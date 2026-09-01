<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Presence;
use App\Models\Task;
use App\Models\LeaveRequest;
use App\Models\Incident;
use App\Models\Signature;
use App\Models\EmployeeKPIRecord;
use App\Models\KPI;
use App\Models\Holiday;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class KPICalculationService
{
    private $employee;
    private $period; // Format: 2025-12 (year-month)

    public function __construct(Employee $employee, $period = null)
    {
        $this->employee = $employee;
        $this->period = $period ?? now()->format('Y-m');
    }

    /**
     * Calculate all KPI metrics for an employee in a period
     */
    public function calculateAllKPIs()
    {
        return [
            'attendance' => $this->calculateCheckoutMetrics(),
            'productivity' => $this->calculateLogMetrics(),
        ];
    }

    /**
     * Helper to calculate actual working days taking into account holidays, leaves, and join date
     */
    private function getActualWorkingDays()
    {
        $startDate = Carbon::createFromFormat('Y-m', $this->period)->startOfMonth();
        $endDate = Carbon::createFromFormat('Y-m', $this->period)->endOfMonth();
        $today = Carbon::now()->startOfDay();
        
        $calcEndDate = $endDate->isFuture() ? $today : $endDate;

        // If employee joined after the start of the month, adjust start date
        if ($this->employee->hire_date && $this->employee->hire_date->isAfter($startDate)) {
            $startDate = $this->employee->hire_date->copy()->startOfDay();
        }

        // If they joined after calcEndDate, they have 0 working days
        if ($startDate->isAfter($calcEndDate)) {
            return 0;
        }

        $workingDays = CarbonPeriod::create($startDate, $calcEndDate)
            ->filter(fn($date) => $date->isWeekday())
            ->count();

        // Subtract Holidays
        $holidays = Holiday::whereBetween('date', [$startDate->format('Y-m-d'), $calcEndDate->format('Y-m-d')])
            ->get();

        foreach ($holidays as $holiday) {
            $hDate = Carbon::parse($holiday->date);
            if ($hDate->isWeekday()) {
                $workingDays--;
            }
        }

        // Subtract Approved Leaves
        $leaves = LeaveRequest::where('employee_id', $this->employee->id)
            ->where('status', 'approved')
            ->where(function($query) use ($startDate, $calcEndDate) {
                $query->whereBetween('start_date', [$startDate->format('Y-m-d'), $calcEndDate->format('Y-m-d')])
                      ->orWhereBetween('end_date', [$startDate->format('Y-m-d'), $calcEndDate->format('Y-m-d')]);
            })
            ->get();

        foreach ($leaves as $leave) {
            $lStart = Carbon::parse($leave->start_date);
            $lEnd = Carbon::parse($leave->end_date);
            
            // Adjust bounds to only count days within current month window
            $lStart = $lStart->isBefore($startDate) ? $startDate->copy() : $lStart;
            $lEnd = $lEnd->isAfter($calcEndDate) ? $calcEndDate->copy() : $lEnd;

            $leaveDays = CarbonPeriod::create($lStart, $lEnd)
                ->filter(function($date) use ($holidays) {
                    // Only subtract if it's a weekday and NOT already a holiday
                    $isHoliday = $holidays->contains(function($h) use ($date) {
                        return Carbon::parse($h->date)->isSameDay($date);
                    });
                    return $date->isWeekday() && !$isHoliday;
                })
                ->count();
            
            $workingDays -= $leaveDays;
        }

        return max(0, $workingDays);
    }

    /**
     * 1. Kepatuhan Checkout (Checkout Compliance)
     */
    public function calculateCheckoutMetrics()
    {
        $startDate = Carbon::createFromFormat('Y-m', $this->period)->startOfMonth();
        $endDate = Carbon::createFromFormat('Y-m', $this->period)->endOfMonth();
        $today = Carbon::now()->startOfDay();
        
        $calcEndDate = $endDate->isFuture() ? $today : $endDate;

        // Get actual expected working days
        $expectedWorkingDays = $this->getActualWorkingDays();

        // Get presence records
        $presences = Presence::where('employee_id', $this->employee->id)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $calcEndDate->format('Y-m-d')])
            ->get();

        $presentDays = $presences->count();

        // Hitung berapa hari yang ada check_out-nya
        $checkoutCount = $presences->filter(function($p) {
            return !empty($p->check_out);
        })->count();

        $metrics = [];
        
        // Denominator must be expected working days, not just present days, to prevent 1-day 100% bug
        $checkoutPercentage = $expectedWorkingDays > 0 ? ($checkoutCount / $expectedWorkingDays) * 100 : 0;
        
        // Clamping min 0 max 100 and rounding
        $metrics['checkout_compliance'] = round(max(0, min(100, $checkoutPercentage)), 2);
        
        // Raw data for other dashboard usages
        $metrics['present_days'] = $presentDays;
        $metrics['checkout_count'] = $checkoutCount;
        $metrics['expected_working_days'] = $expectedWorkingDays;
        
        return $metrics;
    }

    /**
     * 2. Persentase Pengisian Log (Log Percentage)
     */
    public function calculateLogMetrics()
    {
        $startDate = Carbon::createFromFormat('Y-m', $this->period)->startOfMonth();
        $endDate = Carbon::createFromFormat('Y-m', $this->period)->endOfMonth();
        $today = Carbon::now()->startOfDay();
        
        $calcEndDate = $endDate->isFuture() ? $today : $endDate;

        // Get actual working days (excluding weekends, holidays, leaves)
        $expectedWorkingDays = $this->getActualWorkingDays();

        // Get unique dates where a log was submitted
        $uniqueLogDaysCount = \App\Models\WorkLog::where('employee_id', $this->employee->id)
            ->whereBetween('log_date', [$startDate->format('Y-m-d'), $calcEndDate->format('Y-m-d')])
            ->get()
            ->map(function ($log) {
                return $log->log_date instanceof \Carbon\Carbon 
                    ? $log->log_date->format('Y-m-d') 
                    : \Carbon\Carbon::parse($log->log_date)->format('Y-m-d');
            })
            ->unique()
            ->count();

        $metrics = [];
        
        // Asumsi 1 hari minimal 1 log (kalau lebih dihitung max 100%)
        // We use uniqueLogDaysCount to prevent users from spamming 20 logs on 1 day to hit 100%
        $logPercentage = $expectedWorkingDays > 0 ? ($uniqueLogDaysCount / $expectedWorkingDays) * 100 : 0;
        
        // Clamping min 0 max 100 and rounding
        $metrics['log_percentage'] = round(max(0, min(100, $logPercentage)), 2);
        
        // Raw data
        $metrics['unique_log_days'] = $uniqueLogDaysCount;
        $metrics['expected_working_days'] = $expectedWorkingDays;

        return $metrics;
    }

    /**
     * Get all calculated metrics as a flat array
     */
    public function getFlatMetrics()
    {
        $allMetrics = $this->calculateAllKPIs();
        $flat = [];
        foreach ($allMetrics as $category => $metrics) {
            foreach ($metrics as $key => $value) {
                $flat["{$category}.{$key}"] = $value;
            }
        }
        return $flat;
    }

    public function calculateTaskMetrics()
    {
        $startDate = Carbon::createFromFormat('Y-m', $this->period)->startOfMonth();
        $endDate = Carbon::createFromFormat('Y-m', $this->period)->endOfMonth();
        
        $tasks = Task::where('assigned_to', $this->employee->id)
            ->whereBetween('due_date', [$startDate->format('Y-m-d 00:00:00'), $endDate->format('Y-m-d 23:59:59')])
            ->get();
            
        $total = $tasks->count();
        $completed = $tasks->where('status', 'completed')->count();
        
        $rate = $total > 0 ? ($completed / $total) * 100 : 0;
        
        return [
            'total' => $total,
            'completed' => $completed,
            'rate' => round(max(0, min(100, $rate)), 2)
        ];
    }

    public function calculateSalesMetrics()
    {
        if (!$this->employee->user) return ['total' => 0, 'won' => 0, 'rate' => 0];

        $startDate = Carbon::createFromFormat('Y-m', $this->period)->startOfMonth();
        $endDate = Carbon::createFromFormat('Y-m', $this->period)->endOfMonth();
        
        $deals = \App\Models\CrmDeal::where('created_by', $this->employee->user->id)
            ->whereBetween('created_at', [$startDate->format('Y-m-d 00:00:00'), $endDate->format('Y-m-d 23:59:59')])
            ->get();
            
        $total = $deals->count();
        $won = $deals->where('status', 'won')->count();
        
        $rate = $total > 0 ? ($won / $total) * 100 : 0;
        
        return [
            'total' => $total,
            'won' => $won,
            'rate' => round(max(0, min(100, $rate)), 2)
        ];
    }

    public function calculateLetterMetrics()
    {
        if (!$this->employee->user) return ['total' => 0, 'approved' => 0, 'rate' => 0];

        $startDate = Carbon::createFromFormat('Y-m', $this->period)->startOfMonth();
        $endDate = Carbon::createFromFormat('Y-m', $this->period)->endOfMonth();
        
        $letters = \App\Models\Letter::where('user_id', $this->employee->user->id)
            ->whereBetween('created_date', [$startDate->format('Y-m-d 00:00:00'), $endDate->format('Y-m-d 23:59:59')])
            ->get();
            
        $total = $letters->count();
        $approved = $letters->where('status', 'approved')->count();
        
        $rate = $total > 0 ? ($approved / $total) * 100 : 0;
        
        return [
            'total' => $total,
            'approved' => $approved,
            'rate' => round(max(0, min(100, $rate)), 2)
        ];
    }

    /**
     * Calculate Cashbook / Financial Claim Metrics (Opsi A SLA Finance & Opsi B Claims)
     */
    public function calculateCashbookMetrics()
    {
        $startDate = Carbon::createFromFormat('Y-m', $this->period)->startOfMonth();
        $endDate = Carbon::createFromFormat('Y-m', $this->period)->endOfMonth();
        $roleName = strtolower($this->employee->role ? $this->employee->role->title : '');

        $isFinance = str_contains($roleName, 'finance') || str_contains($roleName, 'keuangan') || str_contains($roleName, 'accounting');

        if ($isFinance) {
            // Opsi A: SLA Input Transaksi Kas (<= 2 Hari Kerja dari tanggal kejadian)
            if (!$this->employee->user) {
                return ['is_na' => true, 'total' => 0, 'on_time' => 0, 'rate' => 0];
            }

            $transactions = \App\Models\FinancialTransaction::where('created_by', $this->employee->user->id)
                ->whereBetween('created_at', [$startDate->format('Y-m-d 00:00:00'), $endDate->format('Y-m-d 23:59:59')])
                ->get();

            $total = $transactions->count();

            if ($total === 0) {
                // 0 Transaksi di bulan ini -> N/A (Redistribusi proporsional)
                return ['is_na' => true, 'total' => 0, 'on_time' => 0, 'rate' => 0];
            }

            // Using Laravel's diffInDaysFiltered to calculate business days
            // Note: Carbon has diffInDaysFiltered to exclude weekends.
            $onTime = $transactions->filter(function($tx) {
                $tDate = Carbon::parse($tx->transaction_date)->startOfDay();
                $cDate = Carbon::parse($tx->created_at)->startOfDay();
                
                // Cek jika tDate lebih besar (impossible di dunia nyata tapi just in case)
                if ($cDate->isBefore($tDate)) return true;

                // Hitung selisih hari kerja (tanpa Sabtu dan Minggu)
                $businessDays = $tDate->diffInDaysFiltered(function (Carbon $date) {
                    return !$date->isWeekend();
                }, $cDate);

                return $businessDays <= 2;
            })->count();

            $rate = ($onTime / $total) * 100;

            return [
                'is_na' => false,
                'total' => $total,
                'on_time' => $onTime,
                'rate' => round(max(0, min(100, $rate)), 2)
            ];
        } else {
            // Opsi B: General Employee Claims / Reimbursement (Filter Hanging Status)
            $claims = \App\Models\FinancialClaim::where('employee_id', $this->employee->id)
                ->whereIn('status', ['approved', 'rejected']) // Hanya klaim yang siklusnya selesai
                ->whereBetween('created_at', [$startDate->format('Y-m-d 00:00:00'), $endDate->format('Y-m-d 23:59:59')])
                ->get();

            $total = $claims->count();

            if ($total === 0) {
                // 0 Pengajuan klaim (yang selesai) -> N/A (Redistribusi proporsional agar tidak dihukum)
                return ['is_na' => true, 'total' => 0, 'approved' => 0, 'rate' => 0];
            }

            $approved = $claims->where('status', 'approved')->count();
            $rate = ($approved / $total) * 100;

            return [
                'is_na' => false,
                'total' => $total,
                'approved' => $approved,
                'rate' => round(max(0, min(100, $rate)), 2)
            ];
        }
    }

    public static function getMasterKPIConfig()
    {
        $defaultConfig = [
            ['key' => 'presence', 'label' => 'Presensi (Checkout)', 'desc' => 'Kehadiran fisik & kepatuhan jam kerja.', 'weight' => 50, 'applicable_roles' => ['*']],
            ['key' => 'worklog', 'label' => 'Log Kerja Harian', 'desc' => 'Pengisian & verifikasi laporan aktivitas kerja.', 'weight' => 50, 'applicable_roles' => ['*']],
            ['key' => 'task', 'label' => 'Penyelesaian Tugas', 'desc' => 'Persentase penyelesaian tugas pada bulan berjalan.', 'weight' => 0, 'applicable_roles' => ['*']],
            ['key' => 'sales', 'label' => 'Target Sales / Deal', 'desc' => 'Persentase deal yang berhasil dimenangkan.', 'weight' => 0, 'applicable_roles' => ['Sales']],
            ['key' => 'letter', 'label' => 'Pengelolaan Surat', 'desc' => 'Persentase surat yang disetujui.', 'weight' => 0, 'applicable_roles' => ['*']],
            ['key' => 'cashbook', 'label' => 'Buku Kas / Cashbook', 'desc' => 'Disiplin input kas SLA <=24j (Finance) / Status klaim (Staf).', 'weight' => 25, 'applicable_roles' => ['finance']],
            ['key' => 'checkin_wfo', 'label' => 'Check-in (WFO)', 'desc' => 'Tingkat kedatangan tepat waktu khusus jadwal WFO.', 'weight' => 0, 'applicable_roles' => ['*']],
        ];

        $savedConfig = \App\Models\Setting::getValue('kpi_master_config');
        if ($savedConfig) {
            $parsed = json_decode($savedConfig, true);
            if (is_array($parsed)) {
                // Ensure any newly added indicators in code appear in the saved config
                $existingKeys = array_column($parsed, 'key');
                foreach ($defaultConfig as $def) {
                    if (!in_array($def['key'], $existingKeys)) {
                        $parsed[] = $def;
                    }
                }
                return $parsed;
            }
        }
        return $defaultConfig;
    }

    /**
     * Calculate dynamic metrics based on Master KPI configuration (with N/A redistribution)
     */
    public static function calculateDynamicMetricsForEmployee(Employee $employee, $period = null, $snapshotConfig = null)
    {
        $service = new static($employee, $period);
        
        // 1. Get configuration
        $config = $snapshotConfig ?? self::getMasterKPIConfig();
        
        // 1. Ambil semua Role karyawan dengan aman (Null-safe & Multi-role support)
        // Employee di Corevo terhubung langsung ke Role via role_id, bukan pivot di User, 
        // namun kita tetap tangani sebagai array untuk multi-role masa depan dan null-safety.
        $employeeRoles = [];
        if ($employee->role) {
            $employeeRoles[] = $employee->role->title;
        }

        // Jika tidak ada role sama sekali, kita bisa set sebuah role fallback virtual
        if (empty($employeeRoles)) {
            $employeeRoles = ['Unassigned_Staff'];
        }
        
        $activeIndicators = [];
        $totalActiveWeight = 0;
        
        // 2. Filter applicable metrics and sum original weights
        foreach ($config as $indicator) {
            if ($indicator['weight'] <= 0) continue;
            
            // Hard-mapping check for N/A
            $applicableRoles = $indicator['applicable_roles'] ?? [];
            $normalizedAppRoles = array_map('strtolower', $applicableRoles);
            $normalizedEmpRoles = array_map('strtolower', $employeeRoles);
            $isApplicable = in_array('*', $applicableRoles) || !empty(array_intersect($normalizedEmpRoles, $normalizedAppRoles));
            
            if ($isApplicable) {
                // Calculate actual rate
                $rate = 0;
                $raw = [];
                switch ($indicator['key']) {
                    case 'presence':
                        $raw = $service->calculateCheckoutMetrics();
                        $rate = $raw['checkout_compliance'] ?? 0;
                        break;
                    case 'worklog':
                        $raw = $service->calculateLogMetrics();
                        $rate = $raw['log_percentage'] ?? 0;
                        break;
                    case 'task':
                        $raw = $service->calculateTaskMetrics();
                        if ((int)($raw['total'] ?? 0) === 0) {
                            $isApplicable = false;
                        } else {
                            $rate = $raw['rate'] ?? 0;
                        }
                        break;
                    case 'sales':
                        $raw = $service->calculateSalesMetrics();
                        if ((int)($raw['total'] ?? 0) === 0) {
                            $isApplicable = false;
                        } else {
                            $rate = $raw['rate'] ?? 0;
                        }
                        break;
                    case 'letter':
                        $raw = $service->calculateLetterMetrics();
                        if ((int)($raw['total'] ?? 0) === 0) {
                            $isApplicable = false;
                        } else {
                            $rate = $raw['rate'] ?? 0;
                        }
                        break;
                    case 'cashbook':
                        $raw = $service->calculateCashbookMetrics();
                        if (!empty($raw['is_na'])) {
                            $isApplicable = false; // Trigger N/A redistribution
                        } else {
                            $rate = $raw['rate'] ?? 0;
                        }
                        break;
                    case 'checkin_wfo':
                        $raw = $service->calculateCheckinWFOMetrics();
                        if (!empty($raw['is_na'])) {
                            $isApplicable = false; // Trigger N/A redistribution
                        } else {
                            $rate = $raw['rate'] ?? 0;
                        }
                        break;
                }
                
                if ($isApplicable) {
                    $activeIndicators[] = [
                        'key' => $indicator['key'],
                        'label' => $indicator['label'],
                        'desc' => $indicator['desc'] ?? '',
                        'original_weight' => $indicator['weight'],
                        'rate' => $rate,
                        'raw' => $raw
                    ];
                    $totalActiveWeight += $indicator['weight'];
                }
            }
        }
        
        // 3. Division by Zero check (All N/A)
        if ($totalActiveWeight === 0) {
            return [
                'score' => 0,
                'level' => 'na',
                'active_indicators' => [],
                'details' => [],
                'checkout_pct' => 0,
                'log_pct' => 0,
                'working_days' => 20,
                'unique_log_days' => 0,
                'checkout_count' => 0,
            ];
        }
        
        // 4. Proportional Redistribution
        $compositeScore = 0;
        $details = [];
        foreach ($activeIndicators as &$active) {
            $newWeight = ($active['original_weight'] / $totalActiveWeight) * 100;
            $active['actual_weight'] = round($newWeight, 2);
            
            $compositeScore += ($active['rate'] * ($newWeight / 100));
            $details[$active['key']] = $active;
        }
        
        $compositeScore = round(max(0, min(100, $compositeScore)), 2);
        
        return [
            'score' => $compositeScore,
            'level' => self::getPerformanceLevel($compositeScore),
            'active_indicators' => $activeIndicators,
            'details' => $details,
            // Legacy fallbacks
            'checkout_pct' => $details['presence']['rate'] ?? 0,
            'log_pct' => $details['worklog']['rate'] ?? 0,
            'checkout_count' => $details['presence']['raw']['checkout_count'] ?? 0,
            'unique_log_days' => $details['worklog']['raw']['unique_log_days'] ?? 0,
            'working_days' => $details['worklog']['raw']['expected_working_days'] ?? ($details['presence']['raw']['expected_working_days'] ?? 20),
        ];
    }

    /**
     * Legacy wrapper for backward compatibility with existing views.
     */
    public static function calculateDualMetricsForEmployee(Employee $employee, $period = null)
    {
        return self::calculateDynamicMetricsForEmployee($employee, $period);
    }

    /**
     * Get performance level label based on achievement
     */
    public static function getPerformanceLevel($achievement)
    {
        if ($achievement >= 90) return 'excellent';
        if ($achievement >= 75) return 'good';
        if ($achievement >= 60) return 'satisfactory';
        if ($achievement >= 45) return 'needs_improvement';
        return 'unsatisfactory';
    }

    /**
     * Calculate weighted score for a collection of KPI records
     */
    public static function calculateWeightedScore($kpiRecords)
    {
        if ($kpiRecords->isEmpty()) {
            return ['score' => 0, 'level' => 'na'];
        }

        $totalWeightedScore = 0;
        $totalWeight = 0;

        foreach ($kpiRecords as $record) {
            $achievement = method_exists($record, 'getAchievementPercentage') 
                ? $record->getAchievementPercentage() 
                : ($record->composite_score ?? 0);
            
            $weight = $record->weight ?? ($record->kpi->weight ?? 0);
            
            if ($weight > 0) {
                $totalWeightedScore += ($achievement * $weight);
                $totalWeight += $weight;
            }
        }

        $finalScore = $totalWeight > 0 ? $totalWeightedScore / $totalWeight : 0;
        $finalScore = max(0, min(100, $finalScore));
        $level = self::getPerformanceLevel($finalScore);

        return [
            'score' => round($finalScore, 2),
            'level' => $level
        ];
    }

    public function calculateCheckinWFOMetrics()
    {
        $startDate = Carbon::createFromFormat('Y-m', $this->period)->startOfMonth();
        $endDate = Carbon::createFromFormat('Y-m', $this->period)->endOfMonth();

        // 1. Ambil HANYA baris eksplisit yang ditandai sebagai WFO dan berstatus present/absent
        $wfoPresences = \App\Models\Presence::where('employee_id', $this->employee->id)
            ->whereRaw('LOWER(work_type) = ?', ['wfo'])
            ->whereRaw("LOWER(status) IN ('present', 'absent')")
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get();

        $totalWFO = $wfoPresences->count();

        // 2. Jika 0 hari WFO eksplisit (Full WFH, atau WFO tapi bolos tanpa direkam HRD) = Bypass
        if ($totalWFO === 0) {
            return [
                'is_na' => true,
                'total' => 0,
                'on_time' => 0,
                'rate' => 0
            ];
        }

        // 3. Hanya present (bukan absent) dan is_late eksplist 0/false yang dihitung tepat waktu
        $onTime = $wfoPresences->filter(function ($p) {
            return strtolower($p->status) === 'present' && 
                   ($p->is_late === false || $p->is_late === 0 || $p->is_late === '0');
        })->count();

        $rate = ($onTime / $totalWFO) * 100;

        return [
            'is_na' => false,
            'total' => $totalWFO,
            'on_time' => $onTime,
            'rate' => round(max(0, min(100, $rate)), 2)
        ];
    }
}

