<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Presence;
use App\Models\WorkLog;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\FinancialTransaction;
use App\Models\FinancialAccount;

class RealTestingDataSeeder extends Seeder
{
    public function run()
    {
        // 1. Ambil employee Budi atau Administrator
        $employee = Employee::where('fullname', 'like', '%budi%')->first();
        if (!$employee) {
            $employee = Employee::first();
        }
        
        $this->command->info("Membuat data testing real untuk: " . $employee->fullname);

        // Kita set testing ke BULAN LALU (Agustus) untuk ngetest logic late-entry
        $now = Carbon::now()->subMonth();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfToday = $now->copy()->endOfMonth(); // Sampai akhir bulan lalu
        
        $period = CarbonPeriod::create($startOfMonth, $endOfToday);
        
        // Hapus data lama di bulan lalu agar bersih
        Presence::where('employee_id', $employee->id)
            ->whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfToday->format('Y-m-d')])
            ->delete();
            
        WorkLog::where('employee_id', $employee->id)
            ->whereBetween('log_date', [$startOfMonth->format('Y-m-d'), $endOfToday->format('Y-m-d')])
            ->delete();

        // Siapkan akun kas jika ada
        $account = FinancialAccount::first();
        if ($account && $employee->user) {
            FinancialTransaction::where('created_by', $employee->user->id)
                ->whereBetween('transaction_date', [$startOfMonth->format('Y-m-d'), $endOfToday->format('Y-m-d')])
                ->delete();
        }

        $workingDaysCount = 0;
        $checkoutCount = 0;
        $logCount = 0;
        $cashbookCount = 0;

        foreach ($period as $date) {
            if ($date->isWeekday()) {
                $workingDaysCount++;
                
                // --- BIKIN PRESENCE ---
                // Skenario: Hadir setiap hari kerja, tapi kadang lupa checkout
                $isForgotCheckout = rand(1, 10) > 8; // 20% kemungkinan lupa checkout
                
                Presence::create([
                    'employee_id' => $employee->id,
                    'date' => $date->format('Y-m-d'),
                    'check_in' => $date->format('Y-m-d') . ' 08:00:00',
                    'check_out' => $isForgotCheckout ? null : $date->format('Y-m-d') . ' 17:00:00',
                    'status' => 'present',
                    'work_type' => 'WFO',
                ]);
                
                if (!$isForgotCheckout) {
                    $checkoutCount++;
                }

                // --- BIKIN WORKLOG ---
                // Skenario: Rajin ngisi log, tapi kadang bolong
                $isMissedLog = rand(1, 10) > 7; // 30% kemungkinan bolong ngisi log
                
                if (!$isMissedLog) {
                    WorkLog::create([
                        'employee_id' => $employee->id,
                        'log_date' => $date->format('Y-m-d'),
                        'description' => 'Mengerjakan fitur ' . rand(1, 100),
                    ]);
                    $logCount++;
                }

                // --- BIKIN CASHBOOK (LATE ENTRY SIMULATION) ---
                // Skenario: Bikin transaksi di bulan lalu, tapi telat nginput (di-input hari ini)
                if ($account && $employee->user) {
                    $isMissedCashbook = rand(1, 10) > 8; 
                    if (!$isMissedCashbook) {
                        FinancialTransaction::create([
                            'account_id' => $account->id,
                            'created_by' => $employee->user->id,
                            'transaction_date' => $date->format('Y-m-d'), // Tanggal asli di bulan Agustus
                            'description' => 'Transaksi dummy ' . $date->format('Y-m-d'),
                            'amount' => 50000,
                            'transaction_type' => 'debit',
                            // Simulasi TELAT nginput (di-input bulan September / hari ini)
                            'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 
                            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
                        ]);
                        $cashbookCount++;
                    }
                }
            }
        }
        
        $checkoutPercentage = $workingDaysCount > 0 ? ($checkoutCount / $workingDaysCount) * 100 : 0;
        $logPercentage = $workingDaysCount > 0 ? ($logCount / $workingDaysCount) * 100 : 0;
        $cashbookPercentage = $workingDaysCount > 0 ? ($cashbookCount / $workingDaysCount) * 100 : 0;
        
        $this->command->info("Data berhasil di-generate untuk bulan lalu (" . $startOfMonth->format('F Y') . ")!");
        $this->command->info("Total Hari Kerja: $workingDaysCount");
        $this->command->info("Kepatuhan Checkout: $checkoutCount / $workingDaysCount (" . round($checkoutPercentage, 2) . "%)");
        $this->command->info("Persentase Pengisian Log: $logCount / $workingDaysCount (" . round($logPercentage, 2) . "%)");
        $this->command->info("Total Transaksi Kas: $cashbookCount (Dibuat seolah telat input di bulan ini)");
        
        $this->command->info("Silakan buka dashboard KPI karyawan ini untuk melihat hasilnya!");
    }
}
