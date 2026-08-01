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
            'attendance' => $this->calculateAttendanceMetrics(),
            'productivity' => $this->calculateProductivityMetrics(),
            'leave' => $this->calculateLeaveMetrics(),
            'salary' => $this->calculateSalaryMetrics(),
            'department' => $this->calculateDepartmentMetrics(),
            'behavior' => $this->calculateBehaviorMetrics(),
            'quality' => $this->calculateQualityMetrics(),
        ];
    }

    /**
     * 1. Attendance & Presence Metrics
     */
    public function calculateAttendanceMetrics()
    {
        $startDate = Carbon::createFromFormat('Y-m', $this->period)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Count working days (excluding weekends)
        $workingDays = CarbonPeriod::create($startDate, $endDate)
            ->filter(fn($date) => $date->isWeekday())
            ->count();

        // Get presence records
        $presences = Presence::where('employee_id', $this->employee->id)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get();

        $presentDays = $presences->count();

        // Use config values to ensure consistency with PayrollsController
        $workStart = config('presence.work_start_time', '08:00');
        $lateThresholdMinutes = config('presence.late_threshold_minutes', 15);
        $lateLimit = \Carbon\Carbon::createFromFormat('H:i', $workStart)
            ->addMinutes($lateThresholdMinutes)
            ->format('H:i');
        $workEnd = config('presence.work_end_time', '17:00');

        $lateDays = $presences->filter(function($p) use ($lateLimit) {
            if (!$p->check_in) return false;
            $checkInTime = is_string($p->check_in) ? $p->check_in : $p->check_in->format('H:i');
            return $checkInTime > $lateLimit;
        })->count();
        $earlyCheckouts = $presences->filter(function($p) use ($workEnd) {
            if (!$p->check_out) return false;
            $checkOutTime = is_string($p->check_out) ? $p->check_out : $p->check_out->format('H:i');
            return $checkOutTime < $workEnd;
        })->count();

        $metrics = [];

        // Attendance Rate
        $attendanceRate = $workingDays > 0 ? ($presentDays / $workingDays) * 100 : 0;
        $metrics['attendance_rate'] = round($attendanceRate, 2);

        // Punctuality
        $punctualDays = $presentDays - $lateDays;
        $punctuality = $presentDays > 0 ? ($punctualDays / $presentDays) * 100 : 0;
        $metrics['punctuality'] = round($punctuality, 2);

        // Tardiness Rate
        $tardinessRate = $presentDays > 0 ? ($lateDays / $presentDays) * 100 : 0;
        $metrics['tardiness_rate'] = round($tardinessRate, 2);

        // Absence Rate
        $absenceDays = $workingDays - $presentDays;
        $absenceRate = $workingDays > 0 ? ($absenceDays / $workingDays) * 100 : 0;
        $metrics['absence_rate'] = round($absenceRate, 2);

        // Early Checkout Rate
        $earlyCheckoutRate = $presentDays > 0 ? ($earlyCheckouts / $presentDays) * 100 : 0;
        $metrics['early_checkout_rate'] = round($earlyCheckoutRate, 2);

        // Raw counts for reporting
        $metrics['present_days'] = $presentDays;
        $metrics['absent_days'] = $absenceDays;
        $metrics['late_count'] = $lateDays;
        $metrics['working_days'] = $workingDays;

        return $metrics;
    }

    /**
     * 2. Task Completion & Productivity Metrics
     */
    public function calculateProductivityMetrics()
    {
        $startDate = Carbon::createFromFormat('Y-m', $this->period)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $tasks = Task::where('assigned_to', $this->employee->id)
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->get();

        $completedTasks = $tasks->where('status', 'completed')->count();
        $totalTasks = $tasks->count();
        $onTimeTasks = $tasks->where('status', 'completed')
            ->filter(fn($t) => $t->completed_at && $t->due_date && $t->completed_at->lte($t->due_date))
            ->count();
        $overdueTasks = $tasks->where('status', 'completed')
            ->filter(fn($t) => $t->completed_at && $t->due_date && $t->completed_at->gt($t->due_date))
            ->count();

        $metrics = [];

        // Task Completion Rate
        $completionRate = $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0;
        $metrics['task_completion_rate'] = round($completionRate, 2);

        // On-time Delivery Rate
        $onTimeRate = $completedTasks > 0 ? ($onTimeTasks / $completedTasks) * 100 : 0;
        $metrics['on_time_delivery_rate'] = round($onTimeRate, 2);

        // Task Overdue Rate
        $overdueRate = $totalTasks > 0 ? ($overdueTasks / $totalTasks) * 100 : 0;
        $metrics['overdue_rate'] = round($overdueRate, 2);

        // Raw Counts
        $metrics['completed_tasks_count'] = $completedTasks;
        $metrics['active_tasks'] = $tasks->where('status', 'in-progress')->count();
        $metrics['pending_tasks'] = $tasks->where('status', 'pending')->count();

        return $metrics;
    }

    /**
     * 3. Leave & Time-Off Metrics
     */
    public function calculateLeaveMetrics()
    {
        $startDate = Carbon::createFromFormat('Y-m', $this->period)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $leaveRequests = LeaveRequest::where('employee_id', $this->employee->id)
            ->whereBetween('start_date', [$startDate, $endDate])
            ->where('status', 'approved')
            ->get();

        $totalLeaveDays = $leaveRequests->sum(fn($lr) => $lr->total_days ?? 0);

        $metrics = [];

        // Total Leave Days
        $metrics['total_leave_days'] = $totalLeaveDays;

        // Leave Types breakdown
        $leaveByType = $leaveRequests->groupBy('leave_type')->map->count();
        foreach ($leaveByType as $type => $count) {
            $metrics['leave_' . strtolower(str_replace(' ', '_', $type))] = $count;
        }

        return $metrics;
    }

    /**\n     * 4. Salary & Compensation Metrics\n     */
    public function calculateSalaryMetrics()
    {
        $metrics = [];

        // Base Salary
        $metrics['base_salary'] = $this->employee->salary ?? 0;

        // Salary Grade (based on role)
        $role = $this->employee->role?->title ?? 'N/A';
        $metrics['salary_grade'] = $role;

        return $metrics;
    }

    /**
     * 5. Department & Role-Based Metrics
     */
    public function calculateDepartmentMetrics()
    {
        if (!$this->employee->department_id) {
            return [
                'dept_avg_attendance' => 0,
                'dept_avg_task_completion' => 0,
            ];
        }

        $cacheKey = "dept_metrics_{$this->employee->department_id}_{$this->period}";
        
        return \Cache::remember($cacheKey, now()->addHours(6), function() {
            $metrics = [];

            // Get all employees in same department
            $departmentEmployees = Employee::where('department_id', $this->employee->department_id)->get();

            if ($departmentEmployees->count() === 0) {
                return [
                    'dept_avg_attendance' => 0,
                    'dept_avg_task_completion' => 0,
                ];
            }

            // Department Attendance Average
            $avgAttendance = 0;
            foreach ($departmentEmployees as $emp) {
                $empMetrics = (new static($emp, $this->period))->calculateAttendanceMetrics();
                $avgAttendance += $empMetrics['attendance_rate'] ?? 0;
            }
            $metrics['dept_avg_attendance'] = round($avgAttendance / $departmentEmployees->count(), 2);

            // Department Task Completion Average
            $avgCompletion = 0;
            foreach ($departmentEmployees as $emp) {
                $empMetrics = (new static($emp, $this->period))->calculateProductivityMetrics();
                $avgCompletion += $empMetrics['task_completion_rate'] ?? 0;
            }
            $metrics['dept_avg_task_completion'] = round($avgCompletion / $departmentEmployees->count(), 2);

            return $metrics;
        });
    }

    /**
     * 6. Behavior & Conduct Metrics\n     */
    public function calculateBehaviorMetrics()
    {
        $startDate = Carbon::createFromFormat('Y-m', $this->period)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Count incidents
        $incidents = Incident::where('employee_id', $this->employee->id)
            ->whereBetween('incident_date', [$startDate, $endDate])
            ->get();

        // Count document signatures
        $signatures = Signature::where('user_id', $this->employee->user_id ?? null)
            ->whereBetween('signed_date', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->get();

        $verifiedSignatures = $signatures->where('is_verified', true)->count();

        $metrics = [];

        // Compliance Score (100 - incidents)
        $incidentCount = $incidents->count();
        $metrics['compliance_score'] = max(0, 100 - ($incidentCount * 10)); // 10 points per incident

        // Document Signing Speed
        if ($signatures->count() > 0) {
            $avgSigningHours = $signatures->avg(fn($s) => $s->created_at?->diffInHours($s->signed_date) ?? 0);
            $metrics['document_signing_speed'] = round($avgSigningHours, 2);
        } else {
            $metrics['document_signing_speed'] = 0;
        }

        // Signature Verification Rate
        $metrics['signature_verification_rate'] = $signatures->count() > 0
            ? round(($verifiedSignatures / $signatures->count()) * 100, 2)
            : 0;

        // Incident severity score
        $severityScore = $incidents->sum(fn($i) => match($i->severity) {
            'low' => 1,
            'medium' => 3,
            'high' => 5,
            'critical' => 10,
            default => 0,
        });
        $metrics['conduct_score'] = max(0, 100 - $severityScore);

        return $metrics;
    }

    /**
     * 7. Quality & Efficiency Metrics
     */
    public function calculateQualityMetrics()
    {
        $startDate = Carbon::createFromFormat('Y-m', $this->period)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Get completed tasks with quality ratings
        $tasks = Task::where('assigned_to', $this->employee->id)
            ->whereBetween('completed_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->whereNotNull('quality_rating')
            ->get();

        $metrics = [];

        // Calculate average quality score from actual ratings
        if ($tasks->count() > 0) {
            $avgQuality = $tasks->avg('quality_rating'); // Already 1-5 scale
            $metrics['task_quality_score'] = round($avgQuality, 2);
            $metrics['rated_tasks_count'] = $tasks->count();
        } else {
            // If no ratings yet, return 0 instead of placeholder
            $metrics['task_quality_score'] = 0;
            $metrics['rated_tasks_count'] = 0;
        }

        // Efficiency Index - placeholder for now, requires estimated vs actual hours
        $metrics['efficiency_index'] = 1.0;

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
        $level = self::getPerformanceLevel($finalScore);

        return [
            'score' => round($finalScore, 2),
            'level' => $level
        ];
    }
}
