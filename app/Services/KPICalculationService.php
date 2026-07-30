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

    /**
     * Calculate 2-metric KPI (Checkout Compliance & Work Log %) for an employee
     */
    public static function calculateDualMetricsForEmployee(Employee $employee, $period = null)
    {
        $service = new self($employee, $period);
        $checkoutMetrics = $service->calculateCheckoutMetrics();
        $logMetrics = $service->calculateLogMetrics();

        $checkoutPct = $checkoutMetrics['checkout_compliance'] ?? 0;
        $logPct = $logMetrics['log_percentage'] ?? 0;

        $compositeScore = round(max(0, min(100, ($checkoutPct + $logPct) / 2)), 2);
        $level = self::getPerformanceLevel($compositeScore);

        return [
            'checkout_pct' => $checkoutPct,
            'log_pct' => $logPct,
            'score' => $compositeScore,
            'level' => $level,
            'working_days' => $logMetrics['expected_working_days'] ?? $checkoutMetrics['expected_working_days'] ?? 20,
            'unique_log_days' => $logMetrics['unique_log_days'] ?? 0,
            'checkout_count' => $checkoutMetrics['checkout_count'] ?? 0,
        ];
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
     * Expected: collection of objects with 'achievement_percentage' and 'weight'
     */
    public static function calculateWeightedScore($kpiRecords)
    {
        if ($kpiRecords->isEmpty()) {
            return ['score' => 0, 'level' => 'na'];
        }

        $totalWeightedScore = 0;
        $totalWeight = 0;

        foreach ($kpiRecords as $record) {
            // Support both Model (EmployeeKPIRecord) and Proxy
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
        
        // Clamping to ensure composite score doesn't exceed 100 or fall below 0
        $finalScore = max(0, min(100, $finalScore));
        
        $level = self::getPerformanceLevel($finalScore);

        return [
            'score' => round($finalScore, 2),
            'level' => $level
        ];
    }
}

