<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\User;
use App\Models\Presence;
use App\Models\WorkLog;
use App\Models\Department;
use App\Models\Role;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Hash;

class CreatePerfectEmployeeSeeder extends Seeder
{
    /**
     * Seed a 100% score employee for KPI testing
     */
    public function run()
    {
        $dept = Department::first();
        if (!$dept) {
            $dept = Department::create(['name' => 'IT & Systems', 'status' => 'Active']);
        }
        $role = Role::first();

        // 1. Create or update Employee (hire_date = 2025-01-01, far before current month)
        $employee = Employee::updateOrCreate(
            ['email' => 'aditya.pratama@aratech.co.id'],
            [
                'nik'             => 'EMP-CHAMPION-999',
                'fullname'        => 'Aditya Pratama',
                'phone_number'    => '+62 812-9988-7766',
                'npwp'            => '12.345.678.9-012.000',
                'gender'          => 'Male',
                'religion'        => 'Islam',
                'marital_status'  => 'Single',
                'address'         => 'Jl. Sudirman No. 88, Jakarta Pusat',
                'birth_date'      => '1996-08-17 00:00:00',
                'hire_date'       => '2025-01-01 00:00:00', // Joined last year (safe from month calculation boundary bugs)
                'department_id'   => $dept ? $dept->id : null,
                'role_id'         => $role ? $role->id : null,
                'status'          => 'active',
                'employee_status' => 'permanent',
                'working_type'    => 'WFO',
                'salary'          => 12000000,
            ]
        );

        // 2. Create User login credentials
        User::updateOrCreate(
            ['email' => 'aditya.pratama@aratech.co.id'],
            [
                'name'        => 'Aditya Pratama',
                'password'    => Hash::make('Aditya123!'),
                'employee_id' => $employee->id,
            ]
        );

        // 3. Define evaluation period: July 2026 (2026-07)
        $period = '2026-07';
        $startDate = Carbon::createFromFormat('Y-m', $period)->startOfMonth();
        $endDate = Carbon::now()->startOfDay(); // Up to today (2026-07-29)

        $workingDays = CarbonPeriod::create($startDate, $endDate)
            ->filter(fn($date) => $date->isWeekday());

        foreach ($workingDays as $date) {
            $dateStr = $date->format('Y-m-d');

            // 4. Create 100% Checkout Presence
            Presence::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'date'        => $dateStr,
                ],
                [
                    'check_in'   => $dateStr . ' 08:00:00',
                    'check_out'  => $dateStr . ' 17:00:00',
                    'status'     => 'present',
                    'notes'      => 'Presensi Tepat Waktu + Complete Checkout',
                ]
            );

            // 5. Create 100% Work Log
            WorkLog::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'log_date'    => $dateStr,
                ],
                [
                    'description' => 'Menyelesaikan modul pengujian KPI, verifikasipresensi checkout, dan pengisian log harian 100%.',
                ]
            );
        }

        return $employee;
    }
}
