<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeKPIRecord;
use App\Models\KPI;
use App\Models\PerformanceReview;
use App\Models\Incident;
use App\Models\KPIRecordProxy;
use App\Services\KPICalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Department;
use App\Models\Payroll;
use App\Models\Presence;
use Carbon\Carbon;
use Illuminate\Support\Str;

class KPIController extends Controller
{
    /**
     * Show KPI dashboard for current user
     */
    public function dashboard(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            abort(403, 'User not linked to employee record.');
        }

        // Support period navigation via ?period=YYYY-MM
        $period = $request->input('period', now()->format('Y-m'));

        // Validate period format, fallback to current month if invalid
        if (!preg_match('/^\d{4}-\d{2}$/', $period)) {
            $period = now()->format('Y-m');
        }

        try {
            // Use Eloquent with eager loading
            $records = EmployeeKPIRecord::with('kpi')
                ->where('employee_id', $employee->id)
                ->where('period', $period)
                ->orderBy('id')
                ->get();
            
            $kpiRecords = $records->map(function($record) {
                return new KPIRecordProxy($record, $record->kpi);
            });
        } catch (\Exception $e) {
            // Table may not exist yet
            $kpiRecords = collect([]);
        }

        $summary = KPICalculationService::calculateWeightedScore($kpiRecords);
        $compositeScore = $summary['score'];
        $performanceLevel = $summary['level'];
        $kpisByCategory = $kpiRecords->groupBy(function($r) { return $r->kpi->category; });
        
        try {
            $incidents = Incident::where('employee_id', $employee->id)
                ->where('status', '!=', 'resolved')
                ->orderByDesc('incident_date')
                ->get();
        } catch (\Exception $e) {
            $incidents = collect([]);
        }

        $calcService = new \App\Services\KPICalculationService($employee, $period);
        $liveMetrics = $calcService->getFlatMetrics();
        $allKpis = \App\Models\KPI::where('status', 'active')->get()->map(function($k) use ($liveMetrics) {
            $cat = strtolower($k->metric_category ?? '');
            if ($cat === 'attendance') {
                $k->calculated_actual = $liveMetrics['attendance.checkout_compliance'] ?? 0;
            } elseif ($cat === 'productivity') {
                $k->calculated_actual = $liveMetrics['productivity.log_percentage'] ?? 0;
            } else {
                $k->calculated_actual = 0;
            }
            return $k;
        });

        // Period navigation helpers
        $periodCarbon   = \Carbon\Carbon::createFromFormat('Y-m', $period)->startOfMonth();
        $prevPeriod     = $periodCarbon->copy()->subMonth()->format('Y-m');
        $nextPeriod     = $periodCarbon->copy()->addMonth()->format('Y-m');
        $isCurrentMonth = $period === now()->format('Y-m');

        return view('kpi.dashboard', compact(
            'employee', 'period', 'kpiRecords', 'compositeScore', 'performanceLevel',
            'kpisByCategory', 'incidents', 'allKpis',
            'prevPeriod', 'nextPeriod', 'isCurrentMonth', 'periodCarbon'
        ));
    }


    /**
     * Show employee KPI report
     */
    public function show($id)
    {
        $user = Auth::user();
        $employee = Employee::findOrFail($id);

        if (($user->employee?->id ?? null) !== $employee->id && !\App\Constants\Roles::isAdmin(session('role')) && ($user->employee?->role?->title ?? '') !== \App\Constants\Roles::MANAGER_UNIT_HEAD) {
            abort(403, 'Unauthorized');
        }

        $period = request('period', now()->format('Y-m'));

        // Single source of truth calculation
        $dual = KPICalculationService::calculateDualMetricsForEmployee($employee, $period);
        $compositeScore = $dual['score'];
        $performanceLevel = $dual['level'];

        // Sync with EmployeeKPIRecord table for database consistency
        $kpi = \App\Models\KPI::firstOrCreate(
            ['code' => 'KPI_DUAL_METRIC'],
            [
                'name' => 'Attendance Checkout & Work Log Metric',
                'category' => 'Productivity & Attendance',
                'target_value' => 100,
                'weight' => 1.0,
                'unit' => '%',
                'status' => 'active',
            ]
        );

        EmployeeKPIRecord::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'kpi_id' => $kpi->id,
                'period' => $period,
            ],
            [
                'actual_value' => $dual['score'],
                'target_value' => 100,
                'composite_score' => $dual['score'],
                'status' => $dual['score'] >= 75 ? 'achieved' : ($dual['score'] >= 60 ? 'warning' : 'critical'),
                'performance_level' => $dual['level'],
                'notes' => "Generated by HR (Checkout: {$dual['checkout_pct']}%, WorkLog: {$dual['log_pct']}%)",
                'updated_at' => now(),
            ]
        );

        try {
            // Use Eloquent with eager loading
            $records = EmployeeKPIRecord::with('kpi')
                ->where('employee_id', $employee->id)
                ->where('period', $period)
                ->orderBy('id')
                ->get();
            
            $kpiRecords = $records->map(function($record) {
                return new KPIRecordProxy($record, $record->kpi);
            });
        } catch (\Exception $e) {
            $kpiRecords = collect([]);
        }

        if ($kpiRecords->count() > 1) {
            $summary = KPICalculationService::calculateWeightedScore($kpiRecords);
            $compositeScore = $summary['score'];
            $performanceLevel = $summary['level'];
        }

        $kpisByCategory = $kpiRecords->groupBy(function($r) { return $r->kpi->category; });

        try {
            $performanceReview = PerformanceReview::where('employee_id', $id)
                ->where('period', $period)
                ->first();
        } catch (\Exception $e) {
            $performanceReview = null;
        }

        $allKpis = \App\Models\KPI::where('status', 'active')->get();

        return view('kpi.show', compact('employee', 'period', 'kpiRecords', 'kpisByCategory', 'performanceReview', 'compositeScore', 'performanceLevel', 'allKpis', 'dual'));
    }

    /**
     * Re-sync live metrics for a specific employee and period
     */
    public function syncEmployeeMetrics(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);
        $period = $request->input('period', now()->format('Y-m'));

        $dual = KPICalculationService::calculateDualMetricsForEmployee($employee, $period);

        $kpi = \App\Models\KPI::firstOrCreate(
            ['code' => 'KPI_DUAL_METRIC'],
            [
                'name' => 'Attendance Checkout & Work Log Metric',
                'category' => 'Productivity & Attendance',
                'target_value' => 100,
                'weight' => 1.0,
                'unit' => '%',
                'status' => 'active',
            ]
        );

        EmployeeKPIRecord::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'kpi_id' => $kpi->id,
                'period' => $period,
            ],
            [
                'actual_value' => $dual['score'],
                'target_value' => 100,
                'composite_score' => $dual['score'],
                'status' => $dual['score'] >= 75 ? 'achieved' : ($dual['score'] >= 60 ? 'warning' : 'critical'),
                'performance_level' => $dual['level'],
                'notes' => "Generated by HR (Checkout: {$dual['checkout_pct']}%, WorkLog: {$dual['log_pct']}%)",
                'updated_at' => now(),
            ]
        );

        \Illuminate\Support\Facades\Cache::forget('company_kpi_dashboard_data');

        return redirect()->route('kpi.show', ['id' => $employee->id, 'period' => $period])
            ->with('success', 'Metrik KPI berhasil disinkronkan secara real-time!');
    }

    /**
     * Show team KPI
     */
    public function team()
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            abort(403, 'User not linked to employee.');
        }

        $period = request('period', now()->format('Y-m'));
        $roleTitle = $user->employee?->role->title ?? null;
        $isGenerated = true;
        $canGenerate = in_array($roleTitle, ['HR Administrator', \App\Constants\Roles::MASTER_ADMIN]);

        $supervisorIds = Employee::whereNotNull('supervisor_id')->pluck('supervisor_id')->unique();
        $supervisors = Employee::whereIn('id', $supervisorIds)->get();

        $selectedSupervisorId = request('supervisor_id', ($canGenerate ? 'all' : $employee->id));

        if ($selectedSupervisorId && $selectedSupervisorId !== 'all') {
            $teamMembers = Employee::where('supervisor_id', $selectedSupervisorId)->get();
            $selectedSupervisor = Employee::find($selectedSupervisorId);
        } else {
            $teamMembers = Employee::all();
            $selectedSupervisor = null;
        }

        $teamKPIs = $teamMembers->map(function($member) use ($period) {
            $dual = KPICalculationService::calculateDualMetricsForEmployee($member, $period);
            return [
                'employee' => $member,
                'working_days' => $dual['working_days'] ?? 20,
                'log_count' => $dual['unique_log_days'] ?? 0,
                'checkout_percentage' => $dual['checkout_pct'],
                'log_percentage' => $dual['log_pct'],
                'composite_score' => $dual['score'],
                'performance_level' => $dual['level'],
            ];
        })->sortByDesc('composite_score')->values();

        // Process Team Summaries for Leaderboard Overview Grid (Sorted by Average Score DESC)
        $teamSummaries = $supervisors->map(function($sup) use ($period) {
            $members = Employee::where('supervisor_id', $sup->id)->get();
            $kpis = $members->map(function($m) use ($period) {
                return KPICalculationService::calculateDualMetricsForEmployee($m, $period)['score'];
            });
            $count = $members->count();
            return [
                'supervisor' => $sup,
                'member_count' => $count,
                'avg_score' => $count > 0 ? round($kpis->avg(), 2) : 0.00,
                'max_score' => $count > 0 ? round($kpis->max(), 2) : 0.00,
                'is_stable' => $count >= 3,
                'badge_label' => $count >= 3 ? '🟢 Tim Standar (≥ 3 Staf)' : '⚠️ Tim Kecil (1-2 Staf)',
            ];
        })->sortByDesc('avg_score')->values();

        $allEmployees = Employee::all();

        return view('kpi.team', compact(
            'teamMembers', 'teamKPIs', 'period', 'isGenerated', 'canGenerate',
            'supervisors', 'selectedSupervisorId', 'selectedSupervisor', 'allEmployees', 'teamSummaries'
        ));
    }

    /**
     * Assign team members under a supervisor
     */
    public function assignTeam(Request $request)
    {
        $user = Auth::user();
        $roleTitle = $user->employee?->role->title ?? null;
        if (!in_array($roleTitle, ['HR Administrator', \App\Constants\Roles::MASTER_ADMIN])) {
            abort(403, 'Hanya HR Administrator atau Master Admin yang dapat mengelola tim.');
        }

        $request->validate([
            'supervisor_id' => 'required|exists:employees,id',
            'member_ids' => 'nullable|array',
            'member_ids.*' => 'exists:employees,id',
        ]);

        $supervisorId = $request->input('supervisor_id');
        $memberIds = $request->input('member_ids', []);

        // Remove supervisor_id for previous members of this supervisor who were unselected
        Employee::where('supervisor_id', $supervisorId)
            ->whereNotIn('id', $memberIds)
            ->update(['supervisor_id' => null]);

        // Assign selected members to this supervisor (excluding supervisor themselves)
        if (!empty($memberIds)) {
            Employee::whereIn('id', array_diff($memberIds, [$supervisorId]))
                ->update(['supervisor_id' => $supervisorId]);
        }

        $period = $request->input('period', now()->format('Y-m'));

        return redirect()->route('kpi.team', ['period' => $period, 'supervisor_id' => $supervisorId])
            ->with('success', 'Formasi tim berhasil diperbarui!');
    }

    /**
     * Delete / dissolve a team by clearing supervisor_id for all subordinates
     */
    public function deleteTeam(Request $request, $supervisorId)
    {
        $user = Auth::user();
        $roleTitle = $user->employee?->role->title ?? null;
        if (!in_array($roleTitle, ['HR Administrator', \App\Constants\Roles::MASTER_ADMIN])) {
            abort(403, 'Hanya HR Administrator atau Master Admin yang dapat membubarkan tim.');
        }

        $supervisor = Employee::findOrFail($supervisorId);

        // Reset supervisor_id to null for all members of this supervisor
        Employee::where('supervisor_id', $supervisorId)->update(['supervisor_id' => null]);

        $period = $request->input('period', now()->format('Y-m'));

        return redirect()->route('kpi.team', ['period' => $period, 'supervisor_id' => 'all'])
            ->with('success', "Tim {$supervisor->fullname} berhasil dibubarkan!");
    }

    /**
     * Show department KPI summary
     */
    public function department()
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            abort(403, 'User not linked.');
        }

        $period = request('period', now()->format('Y-m'));
        $roleTitle = $user->employee?->role->title ?? null;
        $isGenerated = true;
        $canGenerate = in_array($roleTitle, ['HR Administrator', \App\Constants\Roles::MASTER_ADMIN]);

        $departments = \App\Models\Department::all();
        $defaultDeptId = $canGenerate ? 'all' : ($employee->department_id ?? 'all');
        $selectedDeptId = request('department_id', $defaultDeptId);

        if ($selectedDeptId && $selectedDeptId !== 'all') {
            $deptEmployees = Employee::where('department_id', $selectedDeptId)->get();
            $selectedDepartment = \App\Models\Department::find($selectedDeptId);
        } else {
            $deptEmployees = Employee::all();
            $selectedDepartment = null;
        }

        $deptKPIs = $deptEmployees->map(function($emp) use ($period) {
            $dual = KPICalculationService::calculateDualMetricsForEmployee($emp, $period);
            return [
                'employee' => $emp,
                'working_days' => $dual['working_days'] ?? 20,
                'log_count' => $dual['unique_log_days'] ?? 0,
                'checkout_percentage' => $dual['checkout_pct'],
                'log_percentage' => $dual['log_pct'],
                'composite_score' => $dual['score'],
                'performance_level' => $dual['level'],
            ];
        })->sortByDesc('composite_score')->values();

        $avgScore = $deptKPIs->avg('composite_score') ?? 0;

        // Process Department Summaries for Leaderboard Overview Grid (Sorted by Average Score DESC)
        $departmentSummaries = $departments->map(function($dept) use ($period) {
            $members = Employee::where('department_id', $dept->id)->get();
            $kpis = $members->map(function($m) use ($period) {
                return KPICalculationService::calculateDualMetricsForEmployee($m, $period)['score'];
            });
            $count = $members->count();
            return [
                'department' => $dept,
                'member_count' => $count,
                'avg_score' => $count > 0 ? round($kpis->avg(), 2) : 0.00,
                'max_score' => $count > 0 ? round($kpis->max(), 2) : 0.00,
                'is_stable' => $count >= 3,
                'badge_label' => $count >= 3 ? '🟢 Divisi Standar (≥ 3 Staf)' : '⚠️ Divisi Kecil (1-2 Staf)',
            ];
        })->sortByDesc('avg_score')->values();

        $allEmployees = Employee::all();

        return view('kpi.department', compact(
            'deptEmployees', 'deptKPIs', 'avgScore', 'period', 'isGenerated', 'canGenerate',
            'departments', 'selectedDeptId', 'selectedDepartment', 'allEmployees', 'departmentSummaries'
        ));
    }

    /**
     * Assign / create a department and its members
     */
    public function assignDepartment(Request $request)
    {
        $user = Auth::user();
        $roleTitle = $user->employee?->role->title ?? null;
        if (!in_array($roleTitle, ['HR Administrator', \App\Constants\Roles::MASTER_ADMIN])) {
            abort(403, 'Hanya HR Administrator atau Master Admin yang dapat mengelola departemen.');
        }

        $request->validate([
            'department_id' => 'nullable|string',
            'new_department_name' => 'nullable|string|max:100',
            'member_ids' => 'nullable|array',
            'member_ids.*' => 'exists:employees,id',
        ]);

        $deptId = $request->input('department_id');
        $newDeptName = trim($request->input('new_department_name', ''));

        if (!empty($newDeptName)) {
            $dept = \App\Models\Department::firstOrCreate(
                ['name' => $newDeptName],
                [
                    'status' => 'Active',
                    'description' => 'Departemen ' . $newDeptName
                ]
            );
            $deptId = $dept->id;
        }

        if (empty($deptId) || $deptId === 'all') {
            return redirect()->back()->with('error', 'Silakan pilih atau masukkan nama departemen.');
        }

        $memberIds = $request->input('member_ids', []);

        try {
            // Find fallback department for unassigned members if NOT NULL constraint exists
            $fallbackDept = \App\Models\Department::where('id', '!=', $deptId)->first();

            if ($fallbackDept) {
                Employee::where('department_id', $deptId)
                    ->whereNotIn('id', $memberIds)
                    ->update(['department_id' => $fallbackDept->id]);
            }

            // Assign checked members to this department
            if (!empty($memberIds)) {
                Employee::whereIn('id', $memberIds)->update(['department_id' => $deptId]);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui departemen: ' . $e->getMessage());
        }

        $period = $request->input('period', now()->format('Y-m'));

        return redirect()->route('kpi.department', ['period' => $period, 'department_id' => $deptId])
            ->with('success', 'Formasi departemen berhasil diperbarui!');
    }

    /**
     * Delete / dissolve a department
     */
    public function deleteDepartment(Request $request, $departmentId)
    {
        $user = Auth::user();
        $roleTitle = $user->employee?->role->title ?? null;
        if (!in_array($roleTitle, ['HR Administrator', \App\Constants\Roles::MASTER_ADMIN])) {
            abort(403, 'Hanya HR Administrator atau Master Admin yang dapat menghapus departemen.');
        }

        $department = \App\Models\Department::findOrFail($departmentId);
        $departmentName = $department->name;

        try {
            // Find another active department or create an "Unassigned" fallback department
            $fallbackDept = \App\Models\Department::where('id', '!=', $departmentId)->first();
            if (!$fallbackDept) {
                $fallbackDept = \App\Models\Department::firstOrCreate(
                    ['name' => 'Unassigned'],
                    ['status' => 'Active', 'description' => 'Departemen penampung sementara']
                );
            }

            // Reassign members of this department to fallback department
            Employee::where('department_id', $departmentId)->update(['department_id' => $fallbackDept->id]);

            // Delete the department record
            $department->delete();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus departemen: ' . $e->getMessage());
        }

        $period = $request->input('period', now()->format('Y-m'));

        return redirect()->route('kpi.department', ['period' => $period, 'department_id' => 'all'])
            ->with('success', "Departemen {$departmentName} berhasil dihapus! Karyawan telah dipindahkan ke departemen {$fallbackDept->name}.");
    }

    /**
     * Submit KPI for supervisor approval
     */
    public function submit(Request $request, $employeeId)
    {
        $user = Auth::user();
        $employee = Employee::findOrFail($employeeId);

        // Verify user owns this employee record
        if ($user->employee->id !== $employee->id) {
            abort(403, 'You can only submit your own KPI.');
        }

        $period = $request->input('period', now()->format('Y-m'));

        // Update all KPI records for this employee/period to submitted
        \DB::table('employee_kpi_records')
            ->where('employee_id', $employee->id)
            ->where('period', $period)
            ->update([
                'submission_status' => 'submitted',
                'submitted_at' => now(),
                'updated_at' => now(),
            ]);

        return redirect()->route('kpi.dashboard')
            ->with('success', 'KPI berhasil disubmit untuk review oleh atasan.');
    }

    /**
     * Update individual KPI record by employee
     */
    public function updateRecord(Request $request, $recordId)
    {
        $user = Auth::user();
        $record = EmployeeKPIRecord::findOrFail($recordId);

        // Authorization: Employee on their own record
        if ($user->employee->id !== $record->employee_id) {
            abort(403, 'Unauthorized');
        }

        // Only allow updates if in draft or rejected
        if (!in_array($record->submission_status, ['draft', 'rejected'])) {
            return redirect()->back()->with('error', 'KPI sudah disubmit dan tidak bisa diubah.');
        }

        $request->validate([
            'actual_value' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        $data = [
            'notes' => $request->input('notes'),
            'updated_at' => now(),
        ];

        // Allow updating actual_value if provided in request
        if ($request->has('actual_value')) {
            // Check if we should allow manual override (always allow if metric_category is empty)
            // or if it's a draft/rejected record where we allow manual correction
            $data['actual_value'] = $request->input('actual_value');
            
            // Recalculate achievement and performance level
            $target = $record->target_value > 0 ? $record->target_value : 100;
            $achievement = ($data['actual_value'] / $target) * 100;
            $data['composite_score'] = round($achievement, 2);
            $data['performance_level'] = KPICalculationService::getPerformanceLevel($achievement);
            
            // Status mapping
            if ($achievement >= 90) {
                $data['status'] = 'achieved';
            } elseif ($achievement >= 75) {
                $data['status'] = 'achieved';
            } elseif ($achievement >= 60) {
                $data['status'] = 'warning';
            } else {
                $data['status'] = 'critical';
            }
        }

        $record->update($data);

        return redirect()->back()->with('success', 'KPI berhasil diperbarui.');
    }

    /**
     * Show company-wide KPI Dashboard (for HR/Master Admin)
     */
    public function companyDashboard()
    {
        $user = Auth::user();
        $roleTitle = $user->employee?->role->title ?? null;
        $period = request('period', now()->format('Y-m'));

        $isGenerated = EmployeeKPIRecord::where('period', $period)->exists();
        $canGenerate = in_array($roleTitle, ['HR Administrator', \App\Constants\Roles::MASTER_ADMIN]);

        $cachedData = \Illuminate\Support\Facades\Cache::get('company_kpi_dashboard_data');

        if ($cachedData && isset($cachedData['data']) && ($cachedData['period'] ?? '') === $period) {
            $kpiData = $cachedData['data'];
            $bestEmployee = $cachedData['best_employee'];
            $lastUpdated = $cachedData['last_updated'] ?? null;
        } else if ($isGenerated) {
            $employees = Employee::with(['department', 'role'])->get();
            $results = [];
            foreach ($employees as $emp) {
                $dual = KPICalculationService::calculateDualMetricsForEmployee($emp, $period);
                $results[] = [
                    'employee_id' => $emp->id,
                    'fullname' => $emp->fullname,
                    'department' => $emp->department ? $emp->department->name : '-',
                    'position' => $emp->role ? $emp->role->title : '-',
                    'working_days' => $dual['working_days'] ?? 20,
                    'log_count' => $dual['unique_log_days'] ?? 0,
                    'log_percentage' => $dual['log_pct'],
                    'checkout_percentage' => $dual['checkout_pct'],
                    'composite_score' => $dual['score'],
                    'performance_level' => $dual['level'],
                    'photo' => $emp->profile_photo ?? null,
                ];
            }
            $kpiData = collect($results)->sortByDesc('composite_score')->values()->all();
            $bestEmployee = $kpiData[0] ?? null;
            $lastUpdated = now()->toDateTimeString();

            \Illuminate\Support\Facades\Cache::put('company_kpi_dashboard_data', [
                'period' => $period,
                'data' => $kpiData,
                'best_employee' => $bestEmployee,
                'last_updated' => $lastUpdated,
            ], 86400 * 30);
        } else {
            $kpiData = [];
            $bestEmployee = null;
            $lastUpdated = null;
        }

        $periodFormatted = \Carbon\Carbon::createFromFormat('Y-m', $period)->format('F Y');

        return view('kpi.company', compact('kpiData', 'lastUpdated', 'bestEmployee', 'period', 'periodFormatted', 'isGenerated', 'canGenerate'));
    }

    /**
     * Show pending KPI approvals for manager
     */
    public function pendingApprovals()
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            abort(403, 'User not linked to employee.');
        }

        $period = request('period', now()->format('Y-m'));

        // Get subordinates
        $subordinates = Employee::where('supervisor_id', $employee->id)->pluck('id');

        // Get pending KPI records grouped by employee
        $employeesWithPending = Employee::whereIn('id', $subordinates)
            ->whereHas('kpiRecords', function($query) use ($period) {
                $query->where('period', $period)->where('submission_status', 'submitted');
            })
            ->with(['kpiRecords' => function($query) use ($period) {
                $query->where('period', $period)->where('submission_status', 'submitted')->with('kpi');
            }])
            ->get();

        $pendingKPIs = $employeesWithPending->map(function($emp) use ($period) {
            $proxyRecords = $emp->kpiRecords->map(function($r) { return new KPIRecordProxy($r, $r->kpi); });
            $summary = KPICalculationService::calculateWeightedScore($proxyRecords);
            
            // Find the most recent submission time from records
            $submittedAt = $emp->kpiRecords->max('submitted_at');

            return (object) [
                'employee_id' => $emp->id,
                'fullname' => $emp->fullname,
                'period' => $period,
                'submitted_at' => $submittedAt,
                'composite_score' => $summary['score'],
                'performance_level' => $summary['level'],
            ];
        });

        return view('kpi.pending', compact('pendingKPIs', 'period'));
    }

    /**
     * Approve subordinate's KPI
     */
    public function approve(Request $request, $employeeId)
    {
        $user = Auth::user();
        $manager = $user->employee;
        $employee = Employee::findOrFail($employeeId);

        // Verify this employee reports to current user
        if ($employee->supervisor_id !== $manager->id) {
            abort(403, 'Anda bukan atasan langsung karyawan ini.');
        }

        $period = $request->input('period', now()->format('Y-m'));

        // Update all KPI records for this employee/period to approved
        \DB::table('employee_kpi_records')
            ->where('employee_id', $employee->id)
            ->where('period', $period)
            ->where('submission_status', 'submitted')
            ->update([
                'submission_status' => 'approved',
                'reviewed_by' => $manager->id,
                'reviewed_at' => now(),
                'reviewer_notes' => $request->input('notes'),
                'updated_at' => now(),
            ]);

        return redirect()->route('kpi.pending')
            ->with('success', 'KPI ' . $employee->fullname . ' berhasil disetujui.');
    }

    /**
     * Reject subordinate's KPI
     */
    public function reject(Request $request, $employeeId)
    {
        $user = Auth::user();
        $manager = $user->employee;
        $employee = Employee::findOrFail($employeeId);

        // Verify this employee reports to current user
        if ($employee->supervisor_id !== $manager->id) {
            abort(403, 'Anda bukan atasan langsung karyawan ini.');
        }

        $period = $request->input('period', now()->format('Y-m'));
        $notes = $request->input('notes', 'Ditolak tanpa catatan.');

        // Update all KPI records for this employee/period to rejected
        \DB::table('employee_kpi_records')
            ->where('employee_id', $employee->id)
            ->where('period', $period)
            ->where('submission_status', 'submitted')
            ->update([
                'submission_status' => 'rejected',
                'reviewed_by' => $manager->id,
                'reviewed_at' => now(),
                'reviewer_notes' => $notes,
                'updated_at' => now(),
            ]);

        return redirect()->route('kpi.pending')
            ->with('success', 'KPI ' . $employee->fullname . ' ditolak.');
    }

    /**
     * Recalculate KPIs for an employee
     */
    public function recalculate(Request $request, $id)
    {
        $user = Auth::user();
        
        // Authorization check
        if (!\App\Constants\Roles::isAdmin($user->employee?->role?->title ?? '')) {
            abort(403, 'Unauthorized');
        }

        $employee = Employee::findOrFail($id);
        $period = $request->input('period', now()->format('Y-m'));

        try {
            // 1. Calculate Metrics
            $service = new KPICalculationService($employee, $period);
            $metrics = $service->calculateAllKPIs();

            // 2. Dynamic KPI Mapping using Role-based configuration
            if ($employee->role) {
                $kpis = $employee->role->kpis()
                    ->whereNotNull('metric_category')
                    ->whereNotNull('metric_key')
                    ->get();
            } else {
                $kpis = collect();
            }

            // Fallback: If no KPIs mapped to role, use all active KPIs globally
            if ($kpis->isEmpty()) {
                $kpis = \App\Models\KPI::where('status', 'active')
                    ->whereNotNull('metric_category')
                    ->whereNotNull('metric_key')
                    ->get();
            }
            foreach ($kpis as $kpi) {
                // Get actual value from calculated metrics using dynamic mapping
                $actualValue = $metrics[$kpi->metric_category][$kpi->metric_key] ?? 0;
                
                // Use pivot values for target and weight if available, fallback to KPI defaults
                $target = $kpi->pivot->target_value ?? ($kpi->target_value > 0 ? $kpi->target_value : 100);
                $weight = $kpi->pivot->weight ?? ($kpi->weight ?? 0);
                
                // Safe division to prevent division by zero
                $achievement = $target > 0 ? ($actualValue / $target) * 100 : 0;
                
                // Clamping (min 0, max 100)
                $achievement = max(0, min(100, $achievement));
                
                // Decimal precision to 2 digits
                $achievement = round($achievement, 2);
                
                $perf = KPICalculationService::getPerformanceLevel($achievement);
                
                // Status mapping based on achievement
                if ($achievement >= 90) {
                    $status = 'achieved';
                } elseif ($achievement >= 75) {
                    $status = 'achieved';
                } elseif ($achievement >= 60) {
                    $status = 'warning';
                } else {
                    $status = 'critical';
                }

                \DB::table('employee_kpi_records')->updateOrInsert(
                    [
                        'employee_id' => $employee->id,
                        'kpi_id' => $kpi->id,
                        'period' => $period
                    ],
                    [
                        'actual_value' => $actualValue,
                        'target_value' => $target,
                        'composite_score' => round($achievement, 2),
                        'status' => $status,
                        'performance_level' => $perf,
                        'updated_at' => now(),
                    ]
                );
            }

            return redirect()->back()->with('success', 'KPI berhasil dikalkulasi ulang.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengkalkulasi KPI: ' . $e->getMessage());
        }
    }

    /**
     * Show historical performance trend for an employee
     */
    public function trend(Request $request, $id)
    {
        $user = Auth::user();
        $employee = Employee::findOrFail($id);

        // Authorization: User can view their own trend, or managers/HR Administrator can view anyone's
        if (($user->employee?->id ?? null) !== $employee->id && !\App\Constants\Roles::isAdmin(session('role')) && ($user->employee?->role?->title ?? '') !== \App\Constants\Roles::MANAGER_UNIT_HEAD) {
            abort(403, 'Unauthorized');
        }

        $months = (int) $request->input('months', 6); // Default 6 months, min 1, max 12
        $months = max(1, min($months, 12));
        
        $trendData = [];
        $categories = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $period = now()->subMonths($i)->format('Y-m');
            
            // Single source of truth calculation for this month
            $dual = KPICalculationService::calculateDualMetricsForEmployee($employee, $period);
            
            // Check if there is an explicit DB record for this period
            $record = EmployeeKPIRecord::where('employee_id', $employee->id)
                ->where('period', $period)
                ->first();

            $score = $record ? $record->composite_score : $dual['score'];
            $level = $record ? $record->performance_level : $dual['level'];

            // Handle pre-hire date period edge case
            if ($employee->hire_date && \Carbon\Carbon::createFromFormat('Y-m', $period)->endOfMonth()->isBefore($employee->hire_date)) {
                $score = 0;
                $level = 'na';
            }

            $trendData[] = [
                'period' => $period,
                'period_label' => \Carbon\Carbon::createFromFormat('Y-m', $period)->format('M Y'),
                'composite_score' => round($score, 2),
                'performance_level' => $level,
                'checkout_pct' => $dual['checkout_pct'],
                'log_pct' => $dual['log_pct'],
                'working_days' => $dual['working_days'],
                'log_count' => $dual['unique_log_days'],
            ];
        }

        
        /* ================= DASHBOARD STATS INTEGRATION ================= */
        
        // Identify if we are viewing as Manager / Unit Head (Team) or Employee (Individual)
        // If the logged in user is looking at their own record, treat as Individual/Employee view
        // UNLESS they are a manager looking at their own trend? No, request says "if manager show team report, if employee show own data".
        // But here we are on a specific employee's trend page: /kpi/trend/{id}
        // So the context is strictly about THIS employee {id}. 
        // HOWEVER, the user asked: "tambahkan halaman ini pada halaman dashboard... dan apabila dia manager tampilkan report dari team masing masing, atau jika dia karyawan, tampilkan data milik dia sendiri"
        // This implies the widgets added to THIS page should reflect the USER'S scope (or the target employee's scope if we treat the target as the 'manager').
        // Let's assume if the target employee ($employee) is a Supervisor, we show their TEAM'S stats.
        // If the target employee is a regular employee, we show THEIR stats.
        
        $isManager = Employee::where('supervisor_id', $employee->id)->exists();
        $targetEmployeeIds = $isManager 
            ? Employee::where('supervisor_id', $employee->id)->pluck('id')->toArray()
            : [$employee->id];

        // 1. Counts
        $departmentCount = Department::count(); // Global context usually
        $employeeCount   = $isManager ? count($targetEmployeeIds) : 1;
        
        $presenceCount = Presence::whereIn('employee_id', $targetEmployeeIds)->count();
        $payrollCount  = Payroll::whereIn('employee_id', $targetEmployeeIds)->count();

        // 2. Presence Chart (Last 12 months)
        $presenceRaw = Presence::selectRaw('MONTH(date) as month, COUNT(*) as total')
            ->whereIn('employee_id', $targetEmployeeIds)
            ->where('date', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $presenceLabels = [];
        $presenceData   = [];
        foreach ($presenceRaw as $row) {
            // Use current year when creating Carbon instance
            $presenceLabels[] = Carbon::create(now()->year, $row->month, 1)->format('F');
            $presenceData[]   = $row->total;
        }

        // 3. Payroll Chart (Last 12 months)
        $payrollRaw = Payroll::selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->whereIn('employee_id', $targetEmployeeIds)
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $payrollLabels = [];
        $payrollData   = [];
        foreach ($payrollRaw as $row) {
            // Use current year when creating Carbon instance
            $payrollLabels[] = Carbon::create(now()->year, $row->month, 1)->format('F');
            $payrollData[]   = $row->total;
        }

        return view('kpi.trend', compact(
            'employee', 'trendData', 'months', 'categories',
            'departmentCount', 'employeeCount', 'presenceCount', 'payrollCount',
            'presenceLabels', 'presenceData', 'payrollLabels', 'payrollData'
        ));
    }

    /**
     * Delete a specific KPI record
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $record = EmployeeKPIRecord::findOrFail($id);

        // Authorization: Admin, Manager of the employee, or the Employee themselves (if in draft)
        $isAdmin = \App\Constants\Roles::isAdmin(session('role'));
        $isManager = ($user->employee->id ?? null) === $record->employee->supervisor_id;
        $isOwner = ($user->employee->id ?? null) === $record->employee_id;

        if (!$isAdmin && !$isManager && !$isOwner) {
            abort(403, 'Unauthorized');
        }

        // Additional check for owner: only if in draft or rejected
        if ($isOwner && !$isAdmin && !$isManager) {
            if (!in_array($record->submission_status, ['draft', 'rejected'])) {
                return redirect()->back()->with('error', 'KPI sudah disubmit dan tidak bisa dihapus.');
            }
        }

        $record->delete();

        return redirect()->back()->with('success', 'Data KPI berhasil dihapus.');
    }

    /**
     * Store HR Administrative Note / Dispensation for a KPI record
     */
    public function storeHRNote(Request $request, $recordId)
    {
        if (!\App\Constants\Roles::isAdmin(session('role'))) {
            abort(403, 'Unauthorized');
        }

        $record = EmployeeKPIRecord::findOrFail($recordId);

        $validated = $request->validate([
            'notes' => 'required|string|max:1000',
        ]);

        $record->update([
            'notes' => $validated['notes'],
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Catatan dispensasi HR berhasil disimpan!');
    }

    /**
     * Admin/Manager: Store new KPI record manually
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!\App\Constants\Roles::isAdmin(session('role')) && ($user->employee?->role?->title ?? '') !== \App\Constants\Roles::MANAGER_UNIT_HEAD) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'kpi_id'      => 'nullable|required_if:is_new_kpi,0|exists:kpis,id',
            'new_kpi_name'=> 'nullable|required_if:is_new_kpi,1|string|max:255',
            'period'      => 'required|date_format:Y-m',
            'target_value'=> 'required|numeric|min:0',
            'actual_value'=> 'required|numeric|min:0',
            'notes'       => 'nullable|string'
        ]);

        $kpiId = $request->kpi_id;

        // Create new KPI if requested
        if ($request->input('is_new_kpi') == '1') {
            $kpi = \App\Models\KPI::where('name', $request->new_kpi_name)->first();
            
            if (!$kpi) {
                $kpi = \App\Models\KPI::create([
                    'code' => 'KPI-M-' . strtoupper(Str::random(6)),
                    'name' => $request->new_kpi_name,
                    'category' => $request->input('new_kpi_category', 'Quality'),
                    'unit' => $request->input('new_kpi_unit', '%'),
                    'status' => 'active',
                    'target_value' => $request->target_value,
                    'weight' => 0,
                ]);
            }
            $kpiId = $kpi->id;
        }

        $achievement = ($request->actual_value / ($request->target_value > 0 ? $request->target_value : 100)) * 100;
        $perf = KPICalculationService::getPerformanceLevel($achievement);

        if ($achievement >= 90) {
            $status = 'achieved';
        } elseif ($achievement >= 75) {
            $status = 'achieved';
        } elseif ($achievement >= 60) {
            $status = 'warning';
        } else {
            $status = 'critical';
        }

        EmployeeKPIRecord::updateOrCreate(
            [
                'employee_id' => $request->employee_id,
                'kpi_id'      => $kpiId,
                'period'      => $request->period,
            ],
            [
                'target_value'      => $request->target_value,
                'actual_value'      => $request->actual_value,
                'composite_score'   => round($achievement, 2),
                'status'            => $status,
                'performance_level' => $perf,
                'submission_status' => 'approved', // Auto approved if added by admin/manager
                'reviewed_by'       => auth()->user()->employee->id ?? null,
                'reviewed_at'       => now(),
                'notes'             => $request->notes,
            ]
        );

        return redirect()->back()->with('success', 'KPI manual berhasil ditambahkan.');
    }
}
