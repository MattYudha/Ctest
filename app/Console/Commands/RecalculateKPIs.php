<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\KPI;
use App\Models\EmployeeKPIRecord;
use App\Services\KPICalculationService;

class RecalculateKPIs extends Command
{
    protected $signature = 'kpi:recalc-all {period?}';
    protected $description = 'Recalculates KPIs for all employees based on current logic';

    public function handle()
    {
        $period = $this->argument('period') ?: now()->format('Y-m');
        $this->info("Recalculating for period: $period");

        EmployeeKPIRecord::where('period', $period)->delete();
        $this->info("Cleared existing records.");

        $employees = Employee::all();
        
        foreach ($employees as $employee) {
            try {
                $service = new KPICalculationService($employee, $period);
                $metrics = $service->calculateAllKPIs();

                if ($employee->role) {
                    $kpis = $employee->role->kpis()
                        ->whereNotNull('metric_category')
                        ->whereNotNull('metric_key')
                        ->get();
                } else {
                    $kpis = collect();
                }

                if ($kpis->isEmpty()) {
                    $kpis = KPI::where('status', 'active')
                        ->whereNotNull('metric_category')
                        ->whereNotNull('metric_key')
                        ->get();
                }

                foreach ($kpis as $kpi) {
                    $actualValue = $metrics[$kpi->metric_category][$kpi->metric_key] ?? 0;
                    $target = $kpi->pivot->target_value ?? ($kpi->target_value > 0 ? $kpi->target_value : 100);
                    
                    $achievement = ($actualValue / max(1, $target)) * 100;
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

                    EmployeeKPIRecord::create([
                        'employee_id' => $employee->id,
                        'kpi_id'      => $kpi->id,
                        'period'      => $period,
                        'target_value'      => $target,
                        'actual_value'      => $actualValue,
                        'composite_score'   => round($achievement, 2),
                        'status'            => $status,
                        'performance_level' => $perf,
                        'submission_status' => 'draft',
                        'reviewed_by'       => null,
                        'reviewed_at'       => null,
                        'notes'             => 'Auto-calculated via script'
                    ]);
                }
                $this->info("Calculated for {$employee->fullname}");
            } catch (\Exception $e) {
                $this->error("Error for {$employee->fullname}: {$e->getMessage()}");
            }
        }
        $this->info("Done!");
    }
}
