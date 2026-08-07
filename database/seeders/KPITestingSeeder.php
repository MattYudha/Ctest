<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Presence;
use App\Models\WorkLog;
use App\Models\Task;
use App\Models\CrmDeal;
use App\Models\FinancialTransaction;
use App\Models\FinancialClaim;
use App\Models\Letter;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class KPITestingSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Seeding KPI Dummy Data for Current Month...');
        
        $employees = Employee::with(['user', 'role'])->get();
        $startOfMonth = Carbon::now()->startOfMonth();
        $today = Carbon::now();
        
        $workingDays = 0;
        foreach (CarbonPeriod::create($startOfMonth, $today) as $date) {
            if (!$date->isWeekend()) {
                $workingDays++;
            }
        }
        
        if ($workingDays === 0) {
            $workingDays = 5; // Fallback if tested on 1st of month weekend
        }

        foreach ($employees as $employee) {
            $roleTitle = $employee->role ? $employee->role->title : '';
            
            // 1. Presensi (Checkout)
            // Seed 80% attendance rate
            $attendancesToSeed = (int)($workingDays * 0.8);
            for ($i = 0; $i < $attendancesToSeed; $i++) {
                Presence::create([
                    'employee_id' => $employee->id,
                    'check_in' => $startOfMonth->copy()->addDays($i)->format('Y-m-d') . ' 08:00:00',
                    'check_out' => $startOfMonth->copy()->addDays($i)->format('Y-m-d') . ' 17:00:00',
                    'status' => 'present',
                    'is_late' => false,
                    'date' => $startOfMonth->copy()->addDays($i)->format('Y-m-d')
                ]);
            }
            
            // 2. WorkLog
            // Seed 70% log rate
            $logsToSeed = (int)($workingDays * 0.7);
            for ($i = 0; $i < $logsToSeed; $i++) {
                WorkLog::create([
                    'employee_id' => $employee->id,
                    'log_date' => $startOfMonth->copy()->addDays($i)->format('Y-m-d'),
                    'description' => 'Kerja Harian'
                ]);
            }
            
            // 3. Tasks
            // Seed 5 tasks (3 done, 2 pending)
            for ($i = 0; $i < 5; $i++) {
                Task::create([
                    'title' => 'Tugas KPI ' . $i,
                    'description' => 'Test',
                    'assigned_to' => $employee->id,
                    'status' => $i < 3 ? 'Done' : 'Pending',
                    'due_date' => $today->copy()->addDays(2),
                    'created_at' => $today->copy()->subDays(1),
                ]);
            }
            
            // 4. Letters
            // Seed 5 letters (4 approved, 1 rejected)
            if ($employee->user) {
                for ($i = 0; $i < 5; $i++) {
                    Letter::create([
                        'user_id' => $employee->user->id,
                        'subject' => 'Surat KPI ' . $i,
                        'status' => $i < 4 ? 'approved' : 'rejected',
                        'created_date' => $today->copy()->subDays(2)->format('Y-m-d'),
                        'created_at' => $today->copy()->subDays(2),
                        'content' => 'Isi',
                        'letter_number' => 'LTR-' . rand(1000,9999) . '-' . uniqid()
                    ]);
                }
            }

            // 5. CRM Deals (For Sales)
            if (stripos($roleTitle, 'sales') !== false) {
                // 10 Deals (6 won, 4 lost)
                if ($employee->user) {
                    for ($i = 0; $i < 10; $i++) {
                        CrmDeal::create([
                            'title' => 'Deal KPI ' . $i,
                            'status' => $i < 6 ? 'Won' : 'Lost',
                            'value' => 5000000,
                            'created_by' => $employee->user->id,
                            'created_at' => $today->copy()->subDays(5)
                        ]);
                    }
                }
            }
            
            // 6. Cashbook
            if (stripos($roleTitle, 'finance') !== false || stripos($roleTitle, 'keuangan') !== false) {
                // Finance: Input transaction within 2 business days
                if ($employee->user_id) {
                    for ($i = 0; $i < 5; $i++) {
                        FinancialTransaction::create([
                            'created_by' => $employee->user_id,
                            'transaction_date' => $today->copy()->subDays(4)->format('Y-m-d'),
                            // 4 out of 5 on time (within 2 days), 1 late (3 days)
                            'created_at' => $i < 4 ? $today->copy()->subDays(3) : $today->copy()->subDays(1),
                            'amount' => 100000,
                            'type' => 'expense',
                            'description' => 'Test'
                        ]);
                    }
                }
            } else {
                // Non-Finance: Financial Claims
                // 5 Claims (4 approved, 1 pending) => Pending is ignored by denom
                for ($i = 0; $i < 5; $i++) {
                    FinancialClaim::create([
                        'employee_id' => $employee->id,
                        'title' => 'Klaim Test ' . $i,
                        'category' => 'other',
                        'amount' => 50000,
                        'status' => $i < 4 ? 'approved' : 'pending',
                        'created_at' => $today->copy()->subDays(2)
                    ]);
                }
            }
        }
        
        $this->command->info('KPI Dummy Data Seeded Successfully!');
    }
}
