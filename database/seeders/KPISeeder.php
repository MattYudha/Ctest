<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KPI;
use App\Models\EmployeeKPIRecord;
use App\Models\Employee;
use App\Services\KPICalculationService;
use Carbon\Carbon;

class KPISeeder extends Seeder
{
    public function run(): void
    {

        // 1. ATTENDANCE KPIs
        KPI::create([
            'code' => 'ATT_RATE',
            'name' => 'Attendance Rate',
            'category' => 'Attendance',
            'description' => 'Percentage of days employee was present',
            'formula' => '(Working Days Present / Total Working Days) × 100%',
            'target_value' => 95,
            'min_value' => 0,
            'max_value' => 100,
            'weight' => 0.25,
            'unit' => '%',
            'status' => 'active',
        ]);

        KPI::create([
            'code' => 'PUNCTUALITY',
            'name' => 'Punctuality',
            'category' => 'Attendance',
            'description' => 'Percentage of on-time arrivals',
            'formula' => '(On-time Arrivals / Total Working Days) × 100%',
            'target_value' => 90,
            'min_value' => 0,
            'max_value' => 100,
            'weight' => 0.15,
            'unit' => '%',
            'status' => 'active',
        ]);

        KPI::create([
            'code' => 'TARDINESS_RATE',
            'name' => 'Tardiness Rate',
            'category' => 'Attendance',
            'description' => 'Percentage of late arrivals',
            'formula' => '(Late Arrivals / Total Working Days) × 100%',
            'target_value' => 5,
            'min_value' => 0,
            'max_value' => 100,
            'weight' => 0.10,
            'unit' => '%',
            'status' => 'active',
        ]);

        // 2. PRODUCTIVITY KPIs
        KPI::create([
            'code' => 'TASK_COMP_RATE',
            'name' => 'Task Completion Rate',
            'category' => 'Productivity',
            'description' => 'Percentage of assigned tasks completed',
            'formula' => '(Completed Tasks / Total Assigned Tasks) × 100%',
            'target_value' => 85,
            'min_value' => 0,
            'max_value' => 100,
            'weight' => 0.35,
            'unit' => '%',
            'status' => 'active',
        ]);

        KPI::create([
            'code' => 'ON_TIME_DELIVERY',
            'name' => 'On-time Delivery Rate',
            'category' => 'Productivity',
            'description' => 'Percentage of tasks completed by due date',
            'formula' => '(On-time Tasks / Completed Tasks) × 100%',
            'target_value' => 90,
            'min_value' => 0,
            'max_value' => 100,
            'weight' => 0.20,
            'unit' => '%',
            'status' => 'active',
        ]);

        KPI::create([
            'code' => 'TASK_OVERDUE_RATE',
            'name' => 'Task Overdue Rate',
            'category' => 'Productivity',
            'description' => 'Percentage of overdue tasks',
            'formula' => '(Overdue Tasks / Total Tasks) × 100%',
            'target_value' => 5,
            'min_value' => 0,
            'max_value' => 100,
            'weight' => 0.10,
            'unit' => '%',
            'status' => 'active',
        ]);

        // 3. LEAVE KPIs
        KPI::create([
            'code' => 'LEAVE_UTIL_RATE',
            'name' => 'Leave Utilization Rate',
            'category' => 'Leave',
            'description' => 'Percentage of annual leave days used',
            'formula' => '(Days Used / Annual Allocation) × 100%',
            'target_value' => 85,
            'min_value' => 0,
            'max_value' => 100,
            'weight' => 0.05,
            'unit' => '%',
            'status' => 'active',
        ]);

        // 4. BEHAVIOR & COMPLIANCE KPIs
        KPI::create([
            'code' => 'COMPLIANCE_SCORE',
            'name' => 'Compliance Score',
            'category' => 'Behavior',
            'description' => 'Score based on policy adherence and incidents',
            'formula' => '100 - (incidents × 10)',
            'target_value' => 95,
            'min_value' => 0,
            'max_value' => 100,
            'weight' => 0.15,
            'unit' => 'points',
            'status' => 'active',
        ]);

        KPI::create([
            'code' => 'CONDUCT_SCORE',
            'name' => 'Conduct Score',
            'category' => 'Behavior',
            'description' => 'Score based on incident severity',
            'formula' => '100 - (severity_points)',
            'target_value' => 95,
            'min_value' => 0,
            'max_value' => 100,
            'weight' => 0.10,
            'unit' => 'points',
            'status' => 'active',
        ]);

        KPI::create([
            'code' => 'DOC_VERIFY_RATE',
            'name' => 'Document Verification Rate',
            'category' => 'Behavior',
            'description' => 'Percentage of documents verified/signed',
            'formula' => '(Verified Docs / Total Docs) × 100%',
            'target_value' => 95,
            'min_value' => 0,
            'max_value' => 100,
            'weight' => 0.08,
            'unit' => '%',
            'status' => 'active',
        ]);

        $this->command->info('✓ KPI Master data seeded successfully');
        $this->command->info('Run EmployeeKPIRecordsSeeder separately for employee records');
    }
}
