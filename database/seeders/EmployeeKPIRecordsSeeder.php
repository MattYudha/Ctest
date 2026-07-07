<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\KPI;
use App\Models\EmployeeKPIRecord;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class EmployeeKPIRecordsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = Employee::all();
        $kpis = KPI::all();
        
        // Generate KPI records for current month and previous 2 months
        $periods = [
            Carbon::now()->subMonths(2)->format('Y-m'),
            Carbon::now()->subMonth()->format('Y-m'),
            Carbon::now()->format('Y-m'),
        ];

        foreach ($periods as $period) {
            foreach ($employees as $employee) {
                // Clear existing records for this period/employee (skip if table doesn't exist yet)
                try {
                    EmployeeKPIRecord::where('employee_id', $employee->id)
                        ->where('period', $period)
                        ->delete();
                } catch (\Exception $e) {
                    // Table doesn't exist yet, continue
                }

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
        }

        $this->command->info('KPI records seeded successfully!');
    }

    /**
     * Generate a KPI record based on category
     */
    private function generateKPIRecord($employee, $kpi, $period)
    {
        $baseTarget = $kpi->target_value;
        $minValue = $kpi->min_value;
        $maxValue = $kpi->max_value;

        // Generate realistic actual values based on category
        $actualValue = match($kpi->category) {
            'Attendance' => $this->generateAttendanceValue($baseTarget, $minValue, $maxValue),
            'Productivity' => $this->generateProductivityValue($baseTarget, $minValue, $maxValue),
            'Leave' => $this->generateLeaveValue($baseTarget, $minValue, $maxValue),
            'Salary' => $this->generateSalaryValue($baseTarget, $minValue, $maxValue),
            'Department' => $this->generateDepartmentValue($baseTarget, $minValue, $maxValue),
            'Behavior' => $this->generateBehaviorValue($baseTarget, $minValue, $maxValue),
            'Quality' => $this->generateQualityValue($baseTarget, $minValue, $maxValue),
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
            'comments' => $this->generateComment($status, $kpi->category),
        ];
    }

    private function generateAttendanceValue($target, $min, $max)
    {
        // 70% chance of meeting target, 20% warning, 10% critical
        $rand = rand(1, 100);
        if ($rand <= 70) {
            return rand((int)(($target * 0.95)), (int)$target);
        } elseif ($rand <= 90) {
            return rand((int)(($target * 0.80)), (int)(($target * 0.95)));
        } else {
            return rand((int)$min, (int)(($target * 0.80)));
        }
    }

    private function generateProductivityValue($target, $min, $max)
    {
        // 65% chance of meeting target, 25% warning, 10% critical
        $rand = rand(1, 100);
        if ($rand <= 65) {
            return rand((int)(($target * 0.90)), (int)($target + 5));
        } elseif ($rand <= 90) {
            return rand((int)(($target * 0.70)), (int)(($target * 0.90)));
        } else {
            return rand((int)$min, (int)(($target * 0.70)));
        }
    }

    private function generateLeaveValue($target, $min, $max)
    {
        // Lower is better for leave - absences should be minimized
        return rand((int)$min, (int)($target + 2));
    }

    private function generateSalaryValue($target, $min, $max)
    {
        // Salary metrics should be consistent
        return $target;
    }

    private function generateDepartmentValue($target, $min, $max)
    {
        // 60% chance of meeting, 30% warning, 10% critical
        $rand = rand(1, 100);
        if ($rand <= 60) {
            return rand((int)(($target * 0.95)), (int)$target);
        } elseif ($rand <= 90) {
            return rand((int)(($target * 0.75)), (int)(($target * 0.95)));
        } else {
            return rand((int)$min, (int)(($target * 0.75)));
        }
    }

    private function generateBehaviorValue($target, $min, $max)
    {
        // 75% chance of meeting, 20% warning, 5% critical
        $rand = rand(1, 100);
        if ($rand <= 75) {
            return rand((int)(($target * 0.95)), (int)$target);
        } elseif ($rand <= 95) {
            return rand((int)(($target * 0.85)), (int)(($target * 0.95)));
        } else {
            return rand((int)$min, (int)(($target * 0.85)));
        }
    }

    private function generateQualityValue($target, $min, $max)
    {
        // 70% chance of meeting, 25% warning, 5% critical
        $rand = rand(1, 100);
        if ($rand <= 70) {
            return rand((int)(($target * 0.93)), (int)$target);
        } elseif ($rand <= 95) {
            return rand((int)(($target * 0.75)), (int)(($target * 0.93)));
        } else {
            return rand((int)$min, (int)(($target * 0.75)));
        }
    }

    private function generateComment($status, $category)
    {
        $comments = [
            'achieved' => [
                'Excellent performance this month',
                'Target exceeded - great work',
                'Consistent performance',
                'Meeting all expectations',
            ],
            'warning' => [
                'Slightly below target - needs improvement',
                'Performance is declining',
                'Monitor closely for next period',
                'Below expected performance',
            ],
            'critical' => [
                'Significantly below target - immediate action needed',
                'Critical performance issue',
                'Urgent improvement required',
                'Severe underperformance',
            ]
        ];

        $commentArray = $comments[$status] ?? ['No comment'];
        return $commentArray[array_rand($commentArray)];
    }

    /**
     * Calculate composite score using weighted formula
     * Formula: (Attendance×0.25 + Tasks×0.35 + Compliance×0.15 + Quality×0.15 + Conduct×0.10) / 100
     */
    private function calculateCompositeScore($scores)
    {
        $weights = [
            'Attendance' => 0.25,
            'Productivity' => 0.35,  // Tasks/Productivity
            'Leave' => 0.0,           // Included in other categories
            'Salary' => 0.0,          // Not weighted
            'Department' => 0.0,      // Compliance
            'Behavior' => 0.10,       // Conduct
            'Quality' => 0.15,
        ];

        $weighted = 0;
        foreach ($scores as $category => $value) {
            $weight = $weights[$category] ?? 0;
            if ($weight > 0) {
                // Normalize to 0-100 scale
                $normalized = min(100, max(0, $value));
                $weighted += ($normalized * $weight);
            }
        }

        // Add compliance (Department) and redistribute
        if (isset($scores['Department'])) {
            $weighted += ($scores['Department'] * 0.15);
        }

        return min(100, max(0, $weighted / 100 * 100));
    }

    /**
     * Determine performance level based on composite score
     */
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
