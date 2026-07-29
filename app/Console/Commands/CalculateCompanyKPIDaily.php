<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CalculateCompanyKPIDaily extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kpi:calculate-daily';
    protected $description = 'Calculate and cache company KPIs daily for the dashboard';

    public function handle()
    {
        $this->info('Starting company KPI calculation...');

        $period = \Carbon\Carbon::now()->format('Y-m');
        
        // Cari semua KPI template
        $kpiTemplates = \App\Models\KPI::where('status', 'active')->get();
        
        $employees = \App\Models\Employee::with(['user', 'department', 'position'])->get();
        
        $results = [];
        
        foreach ($employees as $employee) {
            $this->info("Calculating for: {$employee->fullname}");
            
            $calcService = new \App\Services\KPICalculationService($employee, $period);
            $metrics = $calcService->getFlatMetrics(); // [kpi_id => actual_value]
            
            $workingDays = $metrics['productivity.expected_working_days'] ?? 0;
            $logCount = $metrics['productivity.unique_log_days'] ?? 0;
            
            $checkoutPct = $metrics['attendance.checkout_compliance'] ?? 0;
            $logPct = $metrics['productivity.log_percentage'] ?? 0;
            
            if (str_contains(strtolower($employee->fullname), 'budi')) {
                \Log::info("Metrics for Budi: ", $metrics);
            }
            
            // Calculate a composite score (simple average of metrics)
            $compositeScore = ($checkoutPct + $logPct) / 2;
            
            $results[] = [
                'employee_id' => $employee->id,
                'fullname' => $employee->fullname,
                'department' => $employee->department ? $employee->department->name : '-',
                'position' => $employee->position ? $employee->position->name : '-',
                'log_count' => $logCount,
                'working_days' => $workingDays,
                'log_percentage' => $logPct,
                'checkout_percentage' => $checkoutPct,
                'composite_score' => $compositeScore,
                'photo' => $employee->profile_photo ?? null,
            ];
        }
        
        // Cache data with timestamp
        $sortedData = collect($results)
            ->sortByDesc(function ($item) {
                // Primary sort: composite score, Secondary sort (Tie-breaker): log count
                return $item['composite_score'] * 100000 + $item['log_count'];
            })
            ->values()
            ->all();

        $cacheData = [
            'last_updated' => now()->toDateTimeString(),
            'next_update' => now()->addDay()->startOfDay()->toDateTimeString(),
            'data' => $sortedData
        ];
        
        \Illuminate\Support\Facades\Cache::put('company_kpi_dashboard_data', $cacheData, 86400); // cache for 24h
        
        $this->info('Company KPI calculation completed and cached!');
    }
}
