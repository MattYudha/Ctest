<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Presence;
use App\Models\Payroll;
use App\Models\Task;
use App\Models\Letter;
use Carbon\Carbon;
use App\Models\EmployeeKPIRecord;
use App\Models\KPI;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Check if current user is global user (HR Administrator, Master Admin)
     */
    private function isGlobalUser()
    {
        $role = session('role');
        return \App\Constants\Roles::isAdmin($role);
    }

    /**
     * Get presence query with last 12 months filter
     */
    private function getPresenceQuery($isGlobal, $employeeId = null)
    {
        // Get last 12 months of data (rolling months)
        $startDate = now()->subMonths(11)->startOfMonth();
        $endDate = now()->endOfMonth();
        
        $query = Presence::whereBetween('date', [$startDate, $endDate])
            ->selectRaw('YEAR(date) as year, MONTH(date) as month, COUNT(*) as total');

        if (!$isGlobal && $employeeId) {
            $query->where('employee_id', $employeeId);
        }

        return $query->groupBy('year', 'month')->orderBy('year')->orderBy('month');
    }

    /**
     * Get payroll query with last 12 months filter
     */
    private function getPayrollQuery($isGlobal, $employeeId = null)
    {
        // Get last 12 months of data (rolling months)
        $startDate = now()->subMonths(11)->startOfMonth();
        $endDate = now()->endOfMonth();
        
        $query = Payroll::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as total');

        if (!$isGlobal && $employeeId) {
            $query->where('employee_id', $employeeId);
        }

        return $query->groupBy('year', 'month')->orderBy('year')->orderBy('month');
    }

    /**
     * Process monthly data for last 12 months and return labels and data arrays
     */
    private function processMonthlyData($rawData)
    {
        $labels = [];
        $data = [];
        
        // Generate last 12 months labels
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $labels[] = $date->format('F');
            
            // Find matching data for this month
            $found = false;
            foreach ($rawData as $row) {
                if ($row->year == $date->year && $row->month == $date->month) {
                    $data[] = $row->total;
                    $found = true;
                    break;
                }
            }
            
            // If no data found for this month, set to 0
            if (!$found) {
                $data[] = 0;
            }
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Get presence data for chart (12 months array)
     */
    private function getPresenceDataArray($isGlobal, $employeeId = null)
    {
        $presenceRaw = $this->getPresenceQuery($isGlobal, $employeeId)->get();
        $presenceData = [];
        
        // Generate last 12 months data
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $found = false;
            
            foreach ($presenceRaw as $row) {
                if ($row->year == $date->year && $row->month == $date->month) {
                    $presenceData[] = $row->total;
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                $presenceData[] = 0;
            }
        }

        return $presenceData;
    }

    public function index()
    {
        /* ================= SCOPE DEFINITION ================= */
        $user = Auth::user();
        $employee = $user->employee;
        $isGlobal = $this->isGlobalUser();

        /* ================= TOTAL CARD ================= */
        $departmentCount = Department::count();
        $employeeCount = Employee::count();

        $empId = $employee ? $employee->id : 0;

        if ($isGlobal) {
            $presenceCount = Presence::count();
            $payrollCount = Payroll::count();
        } else {
            $presenceCount = Presence::where('employee_id', $empId)->count();
            $payrollCount = Payroll::where('employee_id', $empId)->count();
        }

        /* ================= EMPLOYEE STATUS STATS ================= */
        $statusStatsRaw = Employee::selectRaw('employee_status, count(*) as total')
            ->groupBy('employee_status')
            ->get();
        
        $statusLabels = [];
        $statusData = [];
        $availableStatuses = Employee::getAvailableStatuses();
        
        foreach ($statusStatsRaw as $stat) {
            $statusLabels[] = $availableStatuses[$stat->employee_status] ?? ucfirst($stat->employee_status);
            $statusData[] = $stat->total;
        }

        /* ================= PRESENCE PER BULAN ================= */
        $presenceRaw = $this->getPresenceQuery($isGlobal, $empId)->get();
        $presenceProcessed = $this->processMonthlyData($presenceRaw);
        $presenceLabels = $presenceProcessed['labels'];
        $presenceData = $presenceProcessed['data'];

        /* ================= PAYROLL PER BULAN ================= */
        $payrollRaw = $this->getPayrollQuery($isGlobal, $empId)->get();
        $payrollProcessed = $this->processMonthlyData($payrollRaw);
        $payrollLabels = $payrollProcessed['labels'];
        $payrollData = $payrollProcessed['data'];

        /* ================= TASK ================= */
        $tasks = Task::with('employee')->latest()->limit(5)->get();

        /* ================= KPI TREND INTEGRATION ================= */
        $months = 6;
        $trendData = [];
        $categories = [];

        if ($employee) {
            for ($i = $months - 1; $i >= 0; $i--) {
                $period = now()->subMonths($i)->format('Y-m');

                // Get KPI record for this period
                $record = EmployeeKPIRecord::where('employee_id', $employee->id)
                    ->where('period', $period)
                    ->first();

                // Get category breakdown
                $categoryScores = [];
                if ($record) {
                    $allRecords = EmployeeKPIRecord::with('kpi')
                        ->where('employee_id', $employee->id)
                        ->where('period', $period)
                        ->get();

                    foreach ($allRecords as $r) {
                        if ($r->kpi) {
                            $category = $r->kpi->category;
                            if (!isset($categoryScores[$category])) {
                                $categoryScores[$category] = [];
                            }
                            $categoryScores[$category][] = $r->composite_score;
                        }
                    }

                    // Calculate averages
                    foreach ($categoryScores as $cat => $scores) {
                        $categoryScores[$cat] = round(array_sum($scores) / count($scores), 2);
                    }
                }

                $trendData[] = [
                    'period' => $period,
                    'period_label' => now()->subMonths($i)->format('M Y'),
                    'composite_score' => $record?->composite_score ?? 0,
                    'performance_level' => $record?->performance_level ?? 'na',
                    'category_scores' => $categoryScores,
                ];

                // Collect unique categories for legend
                foreach (array_keys($categoryScores) as $cat) {
                    if (!in_array($cat, $categories)) {
                        $categories[] = $cat;
                    }
                }
            }
        }

        /* ================= ROLE-BASED DATA ================= */
        $myTaskCount = 0;
        $myLetterCount = 0;
        $pendingTaskCount = 0;

        if (!$isGlobal && $employee) {
            $myTaskCount = Task::where('assigned_to', $employee->id)->count();
            $pendingTaskCount = Task::where('assigned_to', $employee->id)
                ->where('status', '!=', 'done')->count();
            $myLetterCount = Letter::where('user_id', $user->id)->count();
        }

        /* ================= EMPLOYEE OF THE MONTH (Recalculate Snapshot Cache) ================= */
        $periodNow = now()->format('Y-m');
        $employeeOfTheMonth = null;
        $cachedDashboard = \Illuminate\Support\Facades\Cache::get('company_kpi_dashboard_data');

        if ($cachedDashboard && isset($cachedDashboard['best_employee']) && ($cachedDashboard['period'] ?? '') === $periodNow) {
            $best = $cachedDashboard['best_employee'];
            $empModel = Employee::find($best['employee_id'] ?? 0);
            $employeeOfTheMonth = [
                'employee' => $empModel,
                'fullname' => $best['fullname'],
                'department' => $best['department'],
                'position' => $best['position'],
                'composite_score' => $best['composite_score'],
                'performance_level' => $best['performance_level'] ?? 'excellent',
                'photo' => $best['photo'] ?? null,
                'period_label' => \Carbon\Carbon::createFromFormat('Y-m', $periodNow)->format('F Y'),
            ];
        } else {
            // Fallback calculation across all employees
            $allEmp = Employee::all();
            $topScore = -1;
            foreach ($allEmp as $empItem) {
                $dual = \App\Services\KPICalculationService::calculateDualMetricsForEmployee($empItem, $periodNow);
                if ($dual['score'] > $topScore) {
                    $topScore = $dual['score'];
                    $employeeOfTheMonth = [
                        'employee' => $empItem,
                        'fullname' => $empItem->fullname,
                        'department' => $empItem->department->name ?? 'General',
                        'position' => $empItem->position->name ?? ($empItem->role->title ?? 'Staff'),
                        'composite_score' => $dual['score'],
                        'performance_level' => $dual['level'],
                        'photo' => $empItem->profile_photo ?? null,
                        'period_label' => now()->format('F Y'),
                    ];
                }
            }
        }

        return view('dashboard.index', compact(
            'departmentCount',
            'employeeCount',
            'presenceCount',
            'payrollCount',
            'presenceLabels',
            'presenceData',
            'payrollLabels',
            'payrollData',
            'tasks',
            'trendData',
            'months',
            'categories',
            'isGlobal',
            'myTaskCount',
            'myLetterCount',
            'pendingTaskCount',
            'statusLabels',
            'statusData',
            'employeeOfTheMonth'
        ));
    }

    /**
     * Return presence data as JSON for chart
     * Used by dashboard chart JavaScript
     */
    public function presence()
    {
        try {
            $user = Auth::user();
            $employee = $user->employee;
            $isGlobal = $this->isGlobalUser();
            $empId = $employee ? $employee->id : 0;

            $presenceData = $this->getPresenceDataArray($isGlobal, $empId);

            return response()->json($presenceData);
        } catch (\Exception $e) {
            \Log::error('Error fetching presence data: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'exception' => $e
            ]);

            // Return empty data instead of error to prevent chart breaking
            return response()->json(array_fill(0, 12, 0), 200);
        }
    }
}
