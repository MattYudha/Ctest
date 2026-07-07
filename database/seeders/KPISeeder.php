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
        // Hapus KPI lama agar dashboard bersih
        \DB::table('role_kpi')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \DB::table('employee_kpi_records')->truncate();
        KPI::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 1. Kepatuhan Checkout (Checkout Compliance)
        $kpiCheckout = KPI::create([
            'code' => 'ATT_CHECKOUT',
            'name' => 'Kepatuhan Checkout',
            'category' => 'Attendance',
            'description' => 'Persentase hari kerja yang memiliki catatan check-out valid (kepulangan).',
            'formula' => '(Jumlah Checkout Valid / Jumlah Kehadiran) × 100%',
            'target_value' => 100,
            'min_value' => 0,
            'max_value' => 100,
            'weight' => 0.50, // 50%
            'unit' => '%',
            'status' => 'active',
            'metric_category' => 'attendance',
            'metric_key' => 'checkout_compliance',
        ]);

        // 2. Persentase Pengisian Log (Log Percentage)
        $kpiLog = KPI::create([
            'code' => 'PROD_LOG',
            'name' => 'Persentase Pengisian Log',
            'category' => 'Productivity',
            'description' => 'Persentase jumlah log kerja yang diisi dibandingkan dengan total hari kerja.',
            'formula' => '(Jumlah WorkLog / Jumlah Hari Kerja) × 100%',
            'target_value' => 100,
            'min_value' => 0,
            'max_value' => 100,
            'weight' => 0.50, // 50%
            'unit' => '%',
            'status' => 'active',
            'metric_category' => 'productivity',
            'metric_key' => 'log_percentage',
        ]);

        // Attach ke semua Role yang ada secara global
        $roles = \App\Models\Role::all();
        foreach ($roles as $role) {
            $role->kpis()->attach([
                $kpiCheckout->id => ['target_value' => 100, 'weight' => 0.50],
                $kpiLog->id => ['target_value' => 100, 'weight' => 0.50],
            ]);
        }

        $this->command->info('✓ KPI Master data seeded successfully (Simplified to 2 indicators)');
    }
}
