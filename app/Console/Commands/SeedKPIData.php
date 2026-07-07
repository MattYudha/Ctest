<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\KPI;
use App\Models\EmployeeKPIRecord;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedKPIData extends Command
{
    protected $signature = 'kpi:seed';
    protected $description = 'Seed dummy KPI data for all employees';

    public function handle()
    {
        $employees = Employee::all();
        $kpis = KPI::all();
        $period = now()->format('Y-m');

        if ($employees->isEmpty() || $kpis->isEmpty()) {
            $this->error('No employees or KPIs found in database');
            return 1;
        }

        $this->info("Found " . $employees->count() . " employees and " . $kpis->count() . " KPIs");
        
        $inserted = 0;
        $skipped = 0;

        foreach ($employees as $emp) {
            $categoryScores = [];
            
            foreach ($kpis as $kpi) {
                // Generate realistic value based on category
                $actualValue = $this->generateValue($kpi->category, $kpi->target_value);
                $achievement = ($actualValue / $kpi->target_value) * 100;
                $status = $achievement >= 100 ? 'achieved' : ($achievement >= 80 ? 'warning' : 'critical');
                
                // Determine performance level
                if ($achievement >= 90) {
                    $performanceLevel = 'excellent';
                } elseif ($achievement >= 75) {
                    $performanceLevel = 'good';
                } elseif ($achievement >= 60) {
                    $performanceLevel = 'satisfactory';
                } elseif ($achievement >= 45) {
                    $performanceLevel = 'needs_improvement';
                } else {
                    $performanceLevel = 'unsatisfactory';
                }
                
                // Check if record already exists using raw SQL
                $count = DB::select('select count(*) as cnt from employee_kpi_records where employee_id = ? and kpi_id = ? and period = ?', [$emp->id, $kpi->id, $period]);
                $exists = $count[0]->cnt > 0;
                
                if (!$exists) {
                    try {
                        DB::insert('insert into employee_kpi_records (employee_id, kpi_id, period, target_value, actual_value, status, performance_level, notes, created_at, updated_at) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                            $emp->id,
                            $kpi->id,
                            $period,
                            $kpi->target_value,
                            $actualValue,
                            $status,
                            $performanceLevel,
                            ucfirst($status) . ' - ' . $kpi->name,
                            now(),
                            now(),
                        ]);
                        $inserted++;
                        
                        // Collect for composite score
                        if (!isset($categoryScores[$kpi->category])) {
                            $categoryScores[$kpi->category] = [];
                        }
                        $categoryScores[$kpi->category][] = $actualValue;
                    } catch (\Exception $e) {
                        $this->error('Error inserting KPI: ' . $e->getMessage());
                    }
                } else {
                    $skipped++;
                }
            }
            
            // Calculate composite score
            $weights = [
                'Attendance' => 0.25,
                'Productivity' => 0.35,
                'Department' => 0.15,
                'Behavior' => 0.10,
                'Quality' => 0.15,
                'Leave' => 0.0,
                'Salary' => 0.0,
            ];
            
            $compositeScore = 0;
            foreach ($categoryScores as $category => $scores) {
                $avgScore = array_sum($scores) / count($scores);
                $weight = $weights[$category] ?? 0;
                $compositeScore += $avgScore * $weight;
            }
            
            // Determine performance level
            if ($compositeScore >= 90) {
                $performanceLevel = 'excellent';
            } elseif ($compositeScore >= 75) {
                $performanceLevel = 'good';
            } elseif ($compositeScore >= 60) {
                $performanceLevel = 'satisfactory';
            } elseif ($compositeScore >= 45) {
                $performanceLevel = 'needs_improvement';
            } else {
                $performanceLevel = 'unsatisfactory';
            }
            
            // Update composite scores with raw SQL
            try {
                DB::update('update employee_kpi_records set composite_score = ?, performance_level = ? where employee_id = ? and period = ?', [
                    $compositeScore,
                    $performanceLevel,
                    $emp->id,
                    $period,
                ]);
            } catch (\Exception $e) {
                // Silently continue - updates might fail if inserts didn't happen
            }
        }

        $this->info("\n✓ Inserted: $inserted KPI records");
        $this->info("✓ Skipped: $skipped (already exist)");
        $this->info("✓ Period: $period");
        $this->info("\nDummy KPI data created successfully!\n");
        
        return 0;
    }

    private function generateValue($category, $target)
    {
        $rand = rand(1, 100);
        
        switch ($category) {
            case 'Attendance':
                // 70% hit target, 20% warning, 10% critical
                if ($rand <= 70) {
                    return rand((int)($target * 0.95), (int)$target);
                } elseif ($rand <= 90) {
                    return rand((int)($target * 0.80), (int)($target * 0.95));
                } else {
                    return rand(0, (int)($target * 0.80));
                }
            
            case 'Productivity':
                // 65% hit target, 25% warning, 10% critical
                if ($rand <= 65) {
                    return rand((int)($target * 0.90), (int)($target + 5));
                } elseif ($rand <= 90) {
                    return rand((int)($target * 0.70), (int)($target * 0.90));
                } else {
                    return rand(0, (int)($target * 0.70));
                }
            
            case 'Leave':
                // Lower is better - absences should be minimized
                return rand(0, (int)($target + 2));
            
            case 'Salary':
                // Consistent
                return $target;
            
            case 'Department':
                // 60% hit target
                if ($rand <= 60) {
                    return rand((int)($target * 0.95), (int)$target);
                } elseif ($rand <= 90) {
                    return rand((int)($target * 0.75), (int)($target * 0.95));
                } else {
                    return rand(0, (int)($target * 0.75));
                }
            
            case 'Behavior':
                // 75% hit target
                if ($rand <= 75) {
                    return rand((int)($target * 0.95), (int)$target);
                } elseif ($rand <= 95) {
                    return rand((int)($target * 0.85), (int)($target * 0.95));
                } else {
                    return rand(0, (int)($target * 0.85));
                }
            
            case 'Quality':
                // 70% hit target
                if ($rand <= 70) {
                    return rand((int)($target * 0.93), (int)$target);
                } elseif ($rand <= 95) {
                    return rand((int)($target * 0.75), (int)($target * 0.93));
                } else {
                    return rand(0, (int)($target * 0.75));
                }
            
            default:
                return rand(60, 95);
        }
    }
}
