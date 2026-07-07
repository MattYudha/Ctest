<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\KPI;
use App\Models\EmployeeKPIRecord;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class EmployeeKPIDataSeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::all();
        $kpis = KPI::all();
        
        // Only seed current month to test quickly
        $period = Carbon::now()->format('Y-m');

        foreach ($employees as $employee) {
            $compositeScores = [];
            
            foreach ($kpis as $kpi) {
                $recordData = $this->generateKPIRecord($employee, $kpi, $period);
                $record = EmployeeKPIRecord::create($recordData);
                
                // Collect score for composite calculation
                $compositeScores[$kpi->category] = $record->actual_value;
            }

            // Calculate composite score using weighted formula
            $compositeScore = $this->calculateCompositeScore($compositeScores);
            
            // Determine performance level
            $performanceLevel = $this->getPerformanceLevel($compositeScore);

            // Update the employee's KPI record with composite score
            EmployeeKPIRecord::where('employee_id', $employee->id)
                ->where('period', $period)
                ->update([
                    'composite_score' => $compositeScore,
                    'performance_level' => $performanceLevel,
                ]);
        }

        $this->command->info('✓ KPI employee records seeded for ' . $period);
    }

    private function generateKPIRecord($employee, $kpi, $period)
    {
        $baseTarget = $kpi->target_value;
        $minValue = $kpi->min_value;
        $maxValue = $kpi->max_value;

        // Generate realistic actual values based on category
        $actualValue = match($kpi->category) {
            'Attendance' => rand((int)(($baseTarget * 0.80)), (int)$baseTarget),
            'Productivity' => rand((int)(($baseTarget * 0.70)), (int)($baseTarget + 5)),
            'Leave' => rand((int)$minValue, (int)($baseTarget + 2)),
            'Salary' => $baseTarget,
            'Department' => rand((int)(($baseTarget * 0.75)), (int)$baseTarget),
            'Behavior' => rand((int)(($baseTarget * 0.85)), (int)$baseTarget),
            'Quality' => rand((int)(($baseTarget * 0.75)), (int)$baseTarget),
            default => rand(60, 95),
        };

        // Determine status based on achievement
        $achievement = ($actualValue / $baseTarget) * 100;
        $status = $achievement >= 100 ? 'achieved' : ($achievement >= 80 ? 'warning' : 'critical');

        return [
            'employee_id' => $employee->id,
            'kpi_id' => $kpi->id,
            'period' => $period,
            'target_value' => $baseTarget,
            'actual_value' => $actualValue,
            'status' => $status,
            'performance_level' => 'pending',
            'comments' => 'Auto-calculated for ' . $period,
        ];
    }

    private function calculateCompositeScore($scores)
    {
        $weights = [
            'Attendance' => 0.25,
            'Productivity' => 0.35,
            'Leave' => 0.0,
            'Salary' => 0.0,
            'Department' => 0.15,
            'Behavior' => 0.10,
            'Quality' => 0.15,
        ];

        $weighted = 0;
        foreach ($scores as $category => $value) {
            $weight = $weights[$category] ?? 0;
            if ($weight > 0) {
                $normalized = min(100, max(0, $value));
                $weighted += ($normalized * $weight);
            }
        }

        return min(100, max(0, $weighted));
    }

    private function getPerformanceLevel($score)
    {
        return match(true) {
            $score >= 90 => 'excellent',
            $score >= 75 => 'good',
            $score >= 60 => 'satisfactory',
            $score >= 45 => 'needs_improvement',
            default => 'unsatisfactory',
        };
    }
}
