<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\Employee;
use App\Models\Presence;
use App\Models\Setting;
use App\Models\OvertimeSubmission;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use App\Constants\Roles;
use App\Models\FinancialAccount;
use App\Models\FinancialTransaction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Finance\FinancialTransactionController;
use Illuminate\Validation\ValidationException;
use App\Services\HolidayService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PayrollsController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Payroll::with('employee');

            if (!Roles::hasFullFinanceAccess(session('role'))) {
                $query->where('employee_id', auth()->user()->employee_id);
            }

            // period filter
            if ($request->filled('filter_month')) {
                $query->where('period_month', $request->filter_month);
            }
            if ($request->filled('filter_year')) {
                $query->where('period_year', $request->filter_year);
            }
            if ($request->filled('filter_status')) {
                $query->where('status', $request->filter_status);
            }

            return DataTables::of($query)
                ->addIndexColumn()

                ->addColumn('employee_name', function ($row) {
                    $name = $row->employee?->fullname ?? '<em>Unknown</em>';
                    $nik = $row->employee?->nik ?? '-';
                    $npwp = $row->employee?->npwp ?? '-';

                    return '<div class="fw-bold text-nowrap">' .
                        $name .
                        '</div>' .
                        //  '<div class="text-muted small text-nowrap">NIK: ' . $nik . ' | NPWP: ' . $npwp . '</div>';
                        '<div class="text-muted small text-nowrap">NPWP: ' .
                        $npwp .
                        '</div>';
                })
                ->editColumn('net_salary', function ($row) {
                    return 'Rp ' . number_format($row->net_salary, 0, ',', '.');
                })
                ->editColumn('total_earnings', function ($row) {
                    return 'Rp ' . number_format($row->total_earnings, 0, ',', '.');
                })
                ->editColumn('total_deductions', function ($row) {
                    return 'Rp ' . number_format($row->total_deductions, 0, ',', '.');
                })
                ->addColumn('status_badge', function ($row) {
                    return $row->status_badge;
                })
                ->addColumn('action', function ($row) {
                    $btns = '<div class="btn-group btn-group-sm" role="group">';
                    $btns .=
                        '<a href="' .
                        route('payrolls.show', $row->id) .
                        '" class="btn btn-outline-info"><i class="bi bi-eye"></i></a>';

                    if (Roles::hasFullFinanceAccess(session('role'))) {
                        $btns .=
                            '<a href="' .
                            route('payrolls.edit', $row->id) .
                            '" class="btn btn-outline-warning"><i class="bi bi-pencil"></i></a>';

                        // add data-status attribute and btn-delete-payroll class
                        $btns .=
                            '
                            <button type="button" class="btn btn-outline-danger btn-delete-payroll" 
                                data-id="' .
                            $row->id .
                            '" 
                                data-status="' .
                            $row->status .
                            '"
                                title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                            <form id="form-delete-' .
                            $row->id .
                            '" action="' .
                            route('payrolls.destroy', $row->id) .
                            '" method="POST" style="display:none;">
                                ' .
                            csrf_field() .
                            '
                                ' .
                            method_field('DELETE') .
                            '
                            </form>
                        ';
                    }
                    $btns .= '</div>';
                    return $btns;
                })
                ->addColumn('status_actions', function ($row) {
                    if (!Roles::hasFullFinanceAccess(session('role'))) {
                        return '-';
                    }

                    if ($row->status === 'draft') {
                        return '<button class="btn btn-sm btn-primary btn-update-status" data-id="' .
                            $row->id .
                            '" data-status="approved">
                                    <i class="bi bi-check-circle"></i> Approve
                                </button>';
                    }

                    if ($row->status === 'approved') {
                        return '<button class="btn btn-sm btn-success btn-update-status" data-id="' .
                            $row->id .
                            '" data-status="paid">
                                    <i class="bi bi-cash"></i> Mark as Paid
                                </button>';
                    }

                    return '<span class="text-muted small"><i class="bi bi-check-all"></i> Completed</span>';
                })
                ->rawColumns(['action', 'status_badge', 'employee_name', 'status_actions'])
                ->make(true);
        }

        $assetAccounts = \App\Models\FinancialAccount::where('category', 'expense')->get();

        $defaultAccountId = \App\Models\Setting::getValue('default_payroll_account');

        $months = [
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December',
        ];

        return view('payrolls.index', compact('assetAccounts', 'defaultAccountId', 'months'));
    }

    public function create()
    {
        $employees = Employee::orderBy('fullname')->get(['id', 'fullname', 'salary', 'emp_code']);
        $config = config('payroll');
        return view('payrolls.create', compact('employees', 'config'));
    }

    public function store(Request $request)
    {
        // only admin roles can create payroll
        if (!Roles::hasFullFinanceAccess(session('role'))) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'period_month' => 'required|integer|min:1|max:12',
            'period_year' => 'required|integer|min:2020|max:2099',
            'salary' => 'required|numeric|min:0',
            'transport_allowance' => 'nullable|numeric|min:0',
            'meal_allowance' => 'nullable|numeric|min:0',
            'position_allowance' => 'nullable|numeric|min:0',
            'overtime_hours' => 'nullable|numeric|min:0',
            'overtime_amount' => 'nullable|numeric|min:0',
            'performance_bonus' => 'nullable|numeric|min:0',
            'attendance_bonus' => 'nullable|numeric|min:0',
            'other_bonus' => 'nullable|numeric|min:0',
            'reimbursement' => 'nullable|numeric|min:0',
            'bonus_notes' => 'nullable|string',
            'working_days' => 'nullable|integer|min:0',
            'days_present' => 'nullable|integer|min:0',
            'late_count' => 'nullable|integer|min:0',
            'late_deduction' => 'nullable|numeric|min:0',
            'absent_count' => 'nullable|integer|min:0',
            'absent_deduction' => 'nullable|numeric|min:0',
            'penalty_amount' => 'nullable|numeric|min:0',
            'penalty_notes' => 'nullable|string',
            'bpjs_kes' => 'nullable|numeric|min:0',
            'bpjs_tk' => 'nullable|numeric|min:0',
            'pph21' => 'nullable|numeric|min:0',
            'other_deduction' => 'nullable|numeric|min:0',
            'deduction_notes' => 'nullable|string',
        ]);

        // zero out nulls
        $numericFields = [
            'transport_allowance',
            'meal_allowance',
            'position_allowance',
            'overtime_hours',
            'overtime_amount',
            'performance_bonus',
            'attendance_bonus',
            'other_bonus',
            'reimbursement',
            'late_deduction',
            'absent_deduction',
            'penalty_amount',
            'bpjs_kes',
            'bpjs_tk',
            'pph21',
            'other_deduction',
        ];
        foreach ($numericFields as $field) {
            $validated[$field] = $validated[$field] ?? 0;
        }
        $validated['working_days'] = $validated['working_days'] ?? 0;
        $validated['days_present'] = $validated['days_present'] ?? 0;
        $validated['late_count'] = $validated['late_count'] ?? 0;
        $validated['absent_count'] = $validated['absent_count'] ?? 0;

        $payroll = new Payroll($validated);

        $payroll->status = 'draft';
        $payroll->pay_date = null;
        $payroll->notes = null;

        $payroll->calculateNetSalary();
        $payroll->save();

        return redirect()->route('payrolls.index')->with('success', 'Payroll record successfully created.');
    }

    public function show($id)
    {
        $payroll = Payroll::with('employee.department', 'employee.employeePositions.position')->findOrFail($id);

        // access control: non-admin can only see own payroll
        if (!Roles::hasFullFinanceAccess(session('role'))) {
            if ($payroll->employee_id != auth()->user()->employee_id) {
                abort(403);
            }
        }

        return view('payrolls.show', compact('payroll'));
    }

    public function edit($id)
    {
        // only admin roles can edit payroll
        if (!Roles::hasFullFinanceAccess(session('role'))) {
            abort(403, 'Unauthorized action.');
        }

        $payroll = Payroll::findOrFail($id);
        $employees = Employee::orderBy('fullname')->get(['id', 'fullname', 'salary', 'emp_code']);
        $config = config('payroll');
        return view('payrolls.edit', compact('payroll', 'employees', 'config'));
    }

    public function update(Request $request, $id)
    {
        // only admin roles can update payroll
        if (!Roles::hasFullFinanceAccess(session('role'))) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'period_month' => 'required|integer|min:1|max:12',
            'period_year' => 'required|integer|min:2020|max:2099',
            'salary' => 'required|numeric|min:0',
            'transport_allowance' => 'nullable|numeric|min:0',
            'meal_allowance' => 'nullable|numeric|min:0',
            'position_allowance' => 'nullable|numeric|min:0',
            'overtime_hours' => 'nullable|numeric|min:0',
            'overtime_amount' => 'nullable|numeric|min:0',
            'performance_bonus' => 'nullable|numeric|min:0',
            'attendance_bonus' => 'nullable|numeric|min:0',
            'other_bonus' => 'nullable|numeric|min:0',
            'reimbursement' => 'nullable|numeric|min:0',
            'bonus_notes' => 'nullable|string',
            'working_days' => 'nullable|integer|min:0',
            'days_present' => 'nullable|integer|min:0',
            'late_count' => 'nullable|integer|min:0',
            'late_deduction' => 'nullable|numeric|min:0',
            'absent_count' => 'nullable|integer|min:0',
            'absent_deduction' => 'nullable|numeric|min:0',
            'penalty_amount' => 'nullable|numeric|min:0',
            'penalty_notes' => 'nullable|string',
            'bpjs_kes' => 'nullable|numeric|min:0',
            'bpjs_tk' => 'nullable|numeric|min:0',
            'pph21' => 'nullable|numeric|min:0',
            'other_deduction' => 'nullable|numeric|min:0',
            'deduction_notes' => 'nullable|string',
        ]);

        $numericFields = [
            'transport_allowance',
            'meal_allowance',
            'position_allowance',
            'overtime_hours',
            'overtime_amount',
            'performance_bonus',
            'attendance_bonus',
            'other_bonus',
            'reimbursement',
            'late_deduction',
            'absent_deduction',
            'penalty_amount',
            'bpjs_kes',
            'bpjs_tk',
            'pph21',
            'other_deduction',
        ];
        foreach ($numericFields as $field) {
            $validated[$field] = $validated[$field] ?? 0;
        }
        $validated['working_days'] = $validated['working_days'] ?? 0;
        $validated['days_present'] = $validated['days_present'] ?? 0;
        $validated['late_count'] = $validated['late_count'] ?? 0;
        $validated['absent_count'] = $validated['absent_count'] ?? 0;

        $payroll = Payroll::findOrFail($id);
        $payroll->fill($validated);

        $payroll->status = 'draft';

        $payroll->calculateNetSalary();
        $payroll->save();

        return redirect()->route('payrolls.index')->with('success', 'Payroll record successfully updated.');
    }

    public function destroy($id)
    {
        if (!Roles::hasFullFinanceAccess(session('role'))) {
            abort(403, 'Unauthorized action.');
        }

        $payroll = Payroll::with('employee')->findOrFail($id);

        DB::transaction(function () use ($payroll) {
            // delete related financial transaction if available
            if ($payroll->financial_transaction_id) {
                $transaction = FinancialTransaction::find($payroll->financial_transaction_id);

                if ($transaction) {
                    $accountId = $transaction->account_id;
                    $trxDate = $transaction->transaction_date;

                    // delete financial transaction
                    $transaction->delete();

                    // recalculate cash book balance
                    $financeController = new FinancialTransactionController();

                    try {
                        $reflection = new \ReflectionClass($financeController);

                        $method = $reflection->getMethod('recalculateRunningBalance');

                        $method->setAccessible(true);

                        $method->invoke($financeController, $accountId, $trxDate);
                    } catch (\Throwable $th) {
                        \Log::error('Failed to recalculate balance from Payroll: ' . $th->getMessage());
                    }
                }
            }

            // delete payroll data
            $payroll->delete();
        });

        return redirect()->route('payrolls.index')->with('success', 'Payroll data was deleted successfully.');
    }

    /**
     * ajax: get attendance data for an employee in a given period.
     */
    public function getAttendanceData(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2099',
        ]);

        $employeeId = $request->employee_id;
        $month = (int) $request->month;
        $year = (int) $request->year;

        // $employee = Employee::findOrFail($employeeId);

        // // get presences for the period
        // $presences = Presence::where('employee_id', $employeeId)
        //     ->whereMonth('date', $month)
        //     ->whereYear('date', $year)
        //     ->get();

        // $holidayDates = HolidayService::getHolidayDates($year, $month);

        // // count working days (weekdays in the month)
        // $startDate = Carbon::create($year, $month, 1);
        // $endDate = $startDate->copy()->endOfMonth();
        // $workingDays = 0;
        // $current = $startDate->copy();
        // while ($current <= $endDate) {
        //     // skip weekends and dates included in the holiday list
        //     if (!$current->isWeekend() && !in_array($current->format('Y-m-d'), $holidayDates)) {
        //         $workingDays++;
        //     }
        //     $current->addDay();
        // }

        // // count days present (unique dates with check_in)
        // $daysPresent = $presences->whereNotNull('check_in')->pluck('date')->unique()->count();

        // // count late arrivals
        // $workStart = config('presence.work_start_time', '08:00');
        // $lateThreshold = config('presence.late_threshold_minutes', 15);
        // $lateLimit = Carbon::createFromFormat('H:i', $workStart)->addMinutes($lateThreshold);

        // // $lateCount = 0;
        // // foreach ($presences as $p) {
        // //     if ($p->check_in) {
        // //         $checkInTime = Carbon::parse($p->check_in);
        // //         if ($checkInTime->format('H:i:s') > $lateLimit->format('H:i:s')) {
        // //             $lateCount++;
        // //         }
        // //     }
        // // }

        // $lateCount = $presences->where('is_late', true)->count();

        $employee = Employee::findOrFail($employeeId);

        // 1. Ambil data absen mentah dari DB
        $rawPresences = Presence::where('employee_id', $employeeId)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get();

        // 2. Ambil daftar tanggal libur dari service yang udah kita bikin
        $holidayDates = \App\Services\HolidayService::getHolidayDates($year, $month);

        // 3. CLEANING DATA: Buang hari libur, weekend, dan duplicate absen
        $presences = $rawPresences
            ->filter(function ($p) use ($holidayDates) {
                $dateString = Carbon::parse($p->date)->format('Y-m-d');
                $isWeekend = Carbon::parse($p->date)->isWeekend();

                // Hanya lolos kalau BUKAN weekend, BUKAN libur, dan BUKAN bolos murni
                return !$isWeekend && !in_array($dateString, $holidayDates) && !is_null($p->check_in);
            })
            ->unique(function ($item) {
                // Pastikan 1 hari cuma dihitung 1 absen (cegah karyawan tap 2x)
                return Carbon::parse($item->date)->format('Y-m-d');
            });

        // 4. Setup tanggal awal dan akhir bulan
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        // Panggil fungsi ajaib dari HolidayService buat ngitung Total Hari Kerja (tanpa libur)
        $workingDays = \App\Services\HolidayService::getEffectiveWorkingDays(
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d'),
        );

        // 5. Karena $presences udah disaring super bersih, hitungnya gampang!
        $daysPresent = $presences->count();

        // 6. Hitung rincian tipe kerja (Sekarang PASTI SINKRON sama total daysPresent)
        $wfoCount = $presences->filter(fn($p) => strtolower($p->work_type) === 'wfo')->count();
        $wfhCount = $presences->filter(fn($p) => strtolower($p->work_type) === 'wfh')->count();
        $wfaCount = $presences->filter(fn($p) => strtolower($p->work_type) === 'wfa')->count();

        // Hitung keterlambatan dari data absen yang valid aja
        $lateCount = $presences->where('is_late', true)->count();

        // absent days = working days that have passed - days present - approved leaves
        $today = Carbon::today();
        $effectiveEnd = $endDate->greaterThan($today) ? $today : $endDate;
        $passedWorkingDays = \App\Services\HolidayService::getEffectiveWorkingDays(
            $startDate->format('Y-m-d'),
            $effectiveEnd->format('Y-m-d'),
        );

        // count approved leave days in the period
        $leaveCount = \App\Models\LeaveRequest::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q2) use ($startDate, $endDate) {
                        $q2->where('start_date', '<=', $startDate)->where('end_date', '>=', $endDate);
                    });
            })
            ->get()
            ->sum(function ($leave) use ($startDate, $endDate) {
                $start = Carbon::parse($leave->start_date)->greaterThan($startDate)
                    ? Carbon::parse($leave->start_date)
                    : $startDate;
                $end = Carbon::parse($leave->end_date)->lessThan($endDate) ? Carbon::parse($leave->end_date) : $endDate;
                $days = 0;
                $c = $start->copy();
                while ($c <= $end) {
                    if (!$c->isWeekend()) {
                        $days++;
                    }
                    $c->addDay();
                }
                return $days;
            });

        $absentCount = max(0, $passedWorkingDays - $daysPresent - $leaveCount);

        // calculate deductions
        $baseSalary = (float) $employee->salary;
        // $dailySalary = $workingDays > 0 ? $baseSalary / $workingDays : 0;

        // $latePenalty = config('payroll.late_penalty_per_incident', 50000);
        // $absentMultiplier = config('payroll.absent_penalty_multiplier', 1.0);

        // get minimum wfo settings from database
        // use default values if settings are empty
        $minWfoFullTime = (int) (Setting::where('key', 'min_wfo_full_time')->value('value') ?? 12);

        $minWfoPartTime = (int) (Setting::where('key', 'min_wfo_part_time')->value('value') ?? 6);

        // determine required wfo target based on employee working type
        $requiredWfo = strtolower($employee->working_type) === 'part_time' ? $minWfoPartTime : $minWfoFullTime;

        // count actual wfo attendance in the selected month
        $realWfoCount = $presences
            ->filter(fn($p) => strtolower($p->work_type) === 'wfo' && $p->status === 'present')
            ->count();

        // count attendance outside office (wfh / wfa)
        $wfhWfaCount = $daysPresent - $realWfoCount;

        // calculate missing wfo days
        $wfoDeficit = 0;

        if ($realWfoCount < $requiredWfo) {
            $wfoDeficit = $requiredWfo - $realWfoCount;
        }

        // prevent double penalty
        // wfo deficit penalty cannot exceed total wfh/wfa days
        $penalizedWfoDeficit = min($wfoDeficit, $wfhWfaCount);

        // pure absence without attendance check-in
        $absentMurni = $absentCount;

        // final absence = pure absence + valid wfo deficit penalty
        $finalAbsentCount = $absentMurni + $penalizedWfoDeficit;

        $lateDeduction = round($lateCount * ($baseSalary * 0.01));
        $absentDeduction = round($finalAbsentCount * ($baseSalary * 0.01));

        // bpjs calculations
        // bpjs kes rules: 1% covers employee + 1 spouse + 3 children (total 5).
        // each additional head adds 1%.
        $families = $employee->families;
        $spouseCount = $families
            ->filter(fn($f) => in_array(strtolower($f->relation), ['pasangan', 'istri', 'suami', 'spouse']))
            ->count();
        $childCount = $families->filter(fn($f) => in_array(strtolower($f->relation), ['anak', 'child']))->count();

        $extraHeads = max(0, $spouseCount - 1) + max(0, $childCount - 3);
        $bpjsKesRate = config('payroll.bpjs_kes_employee_rate', 0.01) + $extraHeads * 0.01;

        $bpjsKes = round($baseSalary * $bpjsKesRate);
        $bpjsTk = round($baseSalary * config('payroll.bpjs_tk_employee_rate', 0.02));

        // overtime
        $overtimeRate = (int) Setting::getValue('overtime_rate_per_hour', 0);

        $totalApprovedOvertimeMinutes = OvertimeSubmission::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->sum('duration_minutes');

        $totalOvertimeHours = $totalApprovedOvertimeMinutes / 60;
        $overtimePay = round($totalOvertimeHours * $overtimeRate);

        $calculatedBase = $employee->salary;
        $pph21Amount = round($calculatedBase * (($employee->pph21_rate ?? 0) / 100));

        // reimbursement
        $reimbursements = \App\Models\FinancialClaim::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->get(['id', 'title', 'amount', 'created_at']);
        
        $reimbursementAmount = $reimbursements->sum('amount');

        return response()->json([
            'success' => true,
            'data' => [
                'base_salary' => (float) ($employee->basic_salary > 0 ? $employee->basic_salary : $employee->salary),
                'working_days' => $workingDays,
                'days_present' => $daysPresent,
                'late_count' => $lateCount,
                'absent_count' => $finalAbsentCount,
                'absent_murni' => $absentMurni,
                'absent_wfo_deficit' => $penalizedWfoDeficit,
                'employee_working_type' => $employee->working_type,
                'leave_count' => $leaveCount,

                'wfo_count' => $wfoCount,
                'wfh_count' => $wfhCount,
                'wfa_count' => $wfaCount,

                'late_deduction' => $lateDeduction,
                'absent_deduction' => round($absentDeduction),
                'bpjs_kes' => $bpjsKes,
                'bpjs_tk' => $bpjsTk,

                'pph21' => $pph21Amount,
                'pph21_rate' => (float) ($employee->pph21_rate ?? 0.5),
                'transport_allowance' => (float) $employee->transport_allowance,
                'meal_allowance' => (float) $employee->meal_allowance,
                'position_allowance' => (float) $employee->position_allowance,
                'total_salary' => (float) $employee->salary,
                'overtime_hours' => round($totalOvertimeHours, 2),
                'overtime_amount' => $overtimePay,
                'reimbursement' => (float) $reimbursementAmount,
                'reimbursement_details' => $reimbursements,
            ],
        ]);
    }

    /**
     * ajax: get employee salary data.
     */
    public function getEmployeeData(Request $request)
    {
        $request->validate(['employee_id' => 'required|exists:employees,id']);
        $employee = Employee::findOrFail($request->employee_id);

        return response()->json([
            'success' => true,
            'data' => [
                'salary' => (float) $employee->salary,
                'fullname' => $employee->fullname,
                'emp_code' => $employee->emp_code,
            ],
        ]);
    }

    public function print($id)
    {
        $payroll = Payroll::with('employee')->findOrFail($id);

        // check finance access
        if (!Roles::hasFullFinanceAccess(session('role'))) {
            abort(403, 'Access denied!');
        }

        // only paid payroll can be printed
        if ($payroll->status !== 'paid') {
            return redirect()->back()->with('error', 'Payroll slip has not been paid yet and cannot be printed.');
        }

        return view('payrolls.print', compact('payroll'));
    }

    public function showSlip($id)
    {
        $payroll = Payroll::with('employee.department')->findOrFail($id);
        return view('payrolls.slip', compact('payroll'));
    }

    public function updateStatus(Request $request, $id)
    {
        $payroll = Payroll::with('employee')->findOrFail($id);
        $newStatus = $request->status;

        DB::transaction(function () use ($payroll, $newStatus) {
            $payroll->status = $newStatus;

            // if status becomes 'paid', record to finance cash book
            if ($newStatus === 'paid') {
                // 1. backend fetches directly from database (ignoring javascript payload)
                $accountId = Setting::getValue('default_payroll_account');

                // 2. if not set in database (empty), throw validation error
                if (!$accountId) {
                    throw ValidationException::withMessages([
                        'account_id' =>
                            'Fund source account has not been set by Admin. Please set the account first on the main page.',
                    ]);
                }

                $payroll->pay_date = now();

                $monthName = \Carbon\Carbon::create()->month($payroll->period_month)->translatedFormat('F');
                $description =
                    'Employee Salary ' .
                    ($payroll->employee->fullname ?? 'Unknown') .
                    ' for ' .
                    $monthName .
                    ' ' .
                    $payroll->period_year;

                // 3. record to cash book using $accountid from central database
                $transaction = FinancialTransaction::create([
                    'account_id' => $accountId,
                    'amount' => $payroll->net_salary,
                    'transaction_date' => now(),
                    'transaction_type' => 'kredit', // cash out
                    'description' => $description,
                    'created_by' => auth()->id(),
                ]);

                // save relation id
                $payroll->financial_transaction_id = $transaction->id;
            }

            $payroll->save();
        });

        return response()->json([
            'success' => true,
            'message' =>
                $newStatus === 'paid'
                    ? 'Payroll has been paid and the transaction is automatically recorded in the Cash Book.'
                    : 'Payroll status successfully updated.',
        ]);
    }

    public function updateSetting(Request $request)
    {
        $request->validate([
            'account_id' => 'required|exists:financial_accounts,id',
        ]);

        // save or update setting to database
        Setting::updateOrCreate(['key' => 'default_payroll_account'], ['value' => $request->account_id]);

        $account = FinancialAccount::find($request->account_id);

        return response()->json([
            'success' => true,
            'account_name' => $account->code . ' - ' . $account->name,
        ]);
    }

    public function exportCsv(\Illuminate\Http\Request $request)
    {
        // validate, ensure ids are sent
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:payroll,id',
        ]);

        // get payroll data with employee relations
        $payrolls = Payroll::with(['employee.department', 'employee.bankAccounts'])
            ->whereIn('id', $request->ids)
            ->get();

        $templatePath = storage_path('app/templates/kopra_payroll_template.xlsx');
        if (!file_exists($templatePath)) {
            return redirect()->back()->with('error', 'Format template KOPRA not found in storage/app/templates/.');
        }

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();
        
        // Update the Transfer Date on the template to today
        $sheet->setCellValue('B14', date('Ymd'));

        // Clear example rows (19 to 30)
        for ($r = 19; $r <= 30; $r++) {
            for ($c = 'A'; $c <= 'V'; $c++) {
                $sheet->setCellValue($c . $r, '');
            }
        }

        $row = 19;
        foreach ($payrolls as $index => $payroll) {
            $employee = $payroll->employee;
            $bankAccount = $employee ? $employee->bankAccounts->first() : null;
            
            $destinationAccNo = $bankAccount ? $bankAccount->account_number : 'Belum ada di sistem';
            $destinationBankCode = $bankAccount ? ($bankAccount->bank_code ?? 'Belum ada di sistem') : 'Belum ada di sistem';
            $destinationAccName = $bankAccount ? ($bankAccount->account_name ?? ($employee->fullname ?? 'Unknown')) : ($employee->fullname ?? 'Unknown');

            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, 'Belum ada di sistem');
            $sheet->setCellValueExplicit('C' . $row, $destinationAccNo, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('D' . $row, 'IDR');
            $sheet->setCellValue('E' . $row, (float)($payroll->net_salary ?? 0));
            $sheet->setCellValue('F' . $row, $destinationAccName);
            $sheet->setCellValue('G' . $row, $employee->address ?? 'Belum ada di sistem');
            $sheet->setCellValue('H' . $row, $destinationBankCode);
            $sheet->setCellValue('I' . $row, 'Belum ada di sistem');
            $sheet->setCellValue('J' . $row, 'GAJI/' . $payroll->period_month . '/' . $payroll->period_year);
            $sheet->setCellValue('K' . $row, 'Pembayaran Payroll ' . Carbon::create()->month($payroll->period_month)->translatedFormat('F') . ' ' . $payroll->period_year);
            $sheet->setCellValue('L' . $row, $employee->email ?? 'Belum ada di sistem');
            $sheet->setCellValue('M' . $row, 'Belum ada di sistem');
            $sheet->setCellValue('N' . $row, 'Belum ada di sistem');
            $sheet->setCellValue('O' . $row, 'Belum ada di sistem');
            $sheet->setCellValue('P' . $row, 'Belum ada di sistem');
            $sheet->setCellValue('Q' . $row, 'Belum ada di sistem');
            $sheet->setCellValue('R' . $row, 'Belum ada di sistem');
            $sheet->setCellValue('S' . $row, 'Belum ada di sistem');
            $sheet->setCellValue('T' . $row, 'Belum ada di sistem');
            $sheet->setCellValue('U' . $row, 'Belum ada di sistem');
            $sheet->setCellValue('V' . $row, 'Belum ada di sistem');

            $row++;
        }

        $fileName = 'Payment_Export_KOPRA_' . date('Y_m_d_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Cache-Control' => 'max-age=0',
        ];

        $callback = function () use ($writer) {
            $writer->save('php://output');
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportDataCsv(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return redirect()->back()->with('error', 'Please select at least one payroll record.');
        }

        $payrolls = Payroll::whereIn('id', $ids)->where('status', 'paid')->with('employee')->get();

        if ($payrolls->isEmpty()) {
            return redirect()->back()->with('error', 'No paid payroll records were found in the selected data.');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Paid Payrolls Data');

        // Styling for headers
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF0B5ED7'], // Blue header
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ];

        // Column definitions
        $columns = [
            'A' => 'No',
            'B' => 'Nama Karyawan',
            'C' => 'NIK',
            'D' => 'NPWP',
            'E' => 'Periode',
            'F' => 'Gaji Awal',
            'G' => 'Total Penambahan',
            'H' => 'Total Pengurangan',
            'I' => 'Total Setelah Pengurangan & Penambahan (Gaji Akhir)',
            'J' => 'Tarif PPh 21',
            'K' => 'Potongan PPh 21',
            'L' => 'Gaji Bersih (Take Home Pay)'
        ];

        // Set Headers
        foreach ($columns as $col => $title) {
            $sheet->setCellValue($col . '1', $title);
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getStyle('A1:L1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Fill Data
        $row = 2;
        foreach ($payrolls as $index => $payroll) {
            $period = Carbon::create()->month($payroll->period_month)->translatedFormat('F') . ' ' . $payroll->period_year;

            // Calculations based on the requirements
            $gajiAwal = (float)($payroll->salary ?? 0) 
                + (float)($payroll->transport_allowance ?? 0) 
                + (float)($payroll->meal_allowance ?? 0) 
                + (float)($payroll->position_allowance ?? 0);
                
            $totalPenambahan = (float)($payroll->overtime_amount ?? 0) 
                + (float)($payroll->performance_bonus ?? 0) 
                + (float)($payroll->attendance_bonus ?? 0) 
                + (float)($payroll->other_bonus ?? 0) 
                + (float)($payroll->reimbursement ?? 0);
                
            $totalPengurangan = (float) ($payroll->total_deductions ?? 0);
            
            // Total After Deductions = (Gaji Awal + Penambahan) - Pengurangan
            $totalSetelahPengurangan = ($gajiAwal + $totalPenambahan) - $totalPengurangan;
            
            // PPH 21
            $pph21Rate = (float) ($payroll->employee->pph21_rate ?? 0);
            $pph21Amount = (float) ($payroll->pph21 ?? 0);

            $netSalary = (float) ($payroll->net_salary ?? 0);

            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $payroll->employee->fullname ?? 'Unknown');
            $sheet->setCellValue('C' . $row, "'" . ($payroll->employee->nik ?? '-')); // Prevent scientific notation for large NIK
            $sheet->setCellValue('D' . $row, "'" . ($payroll->employee->npwp ?? '-'));
            $sheet->setCellValue('E' . $row, $period);
            $sheet->setCellValue('F' . $row, $gajiAwal);
            $sheet->setCellValue('G' . $row, $totalPenambahan);
            $sheet->setCellValue('H' . $row, $totalPengurangan);
            $sheet->setCellValue('I' . $row, $totalSetelahPengurangan);
            $sheet->setCellValue('J' . $row, $pph21Rate . '%');
            $sheet->setCellValue('K' . $row, $pph21Amount);
            $sheet->setCellValue('L' . $row, $netSalary);

            $row++;
        }

        // Apply formatting for currency columns
        $lastRow = $row - 1;
        if ($lastRow >= 2) {
            $sheet->getStyle('F2:I' . $lastRow)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('K2:L' . $lastRow)->getNumberFormat()->setFormatCode('#,##0');
            
            // Add borders to data
            $sheet->getStyle('A2:L' . $lastRow)->applyFromArray([
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
            
            // Center align No, NIK, NPWP, Periode, PPh 21 Rate
            $sheet->getStyle('A2:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C2:E' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('J2:J' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $fileName = 'payroll_data_paid_' . now()->format('Ymd_His') . '.xlsx';

        // Output to browser
        $writer = new Xlsx($spreadsheet);
        
        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Cache-Control' => 'max-age=0',
        ];

        $callback = function () use ($writer) {
            $writer->save('php://output');
        };

        return response()->stream($callback, 200, $headers);
    }
}
