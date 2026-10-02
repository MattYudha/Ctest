<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Task;
use App\Models\Employee;
use Carbon\Carbon;

class TaskMockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Ambil beberapa user/karyawan yang sudah ada di database lokal
        $employees = Employee::whereIn('fullname', [
            'Rahmat Yudi Burhannudin', 
            'Muhammad Alfian Nurrizky',
            'Abu Bakar Bill Gates',
            'Anisa khaerani'
        ])->get();

        if ($employees->isEmpty()) {
            // Kalau nama-nama di atas tidak ada, ambil sembarang 3 karyawan pertama
            $employees = Employee::take(3)->get();
        }

        if ($employees->isEmpty()) {
            $this->command->warn('Tidak ada data karyawan ditemukan di database.');
            return;
        }

        $this->command->info("Membuat mock tasks untuk " . $employees->count() . " karyawan...");

        foreach ($employees as $employee) {
            // --- Mock Tasks for September (Bulan Lalu) ---
            Task::create([
                'title'       => 'Task Sept 1 (Done) - ' . $employee->fullname,
                'description' => 'Data testing untuk KPI September.',
                'assigned_to' => $employee->id,
                'due_date'    => Carbon::create(2026, 9, 15)->format('Y-m-d'),
                'status'      => 'done',
                'completed_at'=> Carbon::create(2026, 9, 15)->format('Y-m-d H:i:s'),
            ]);

            Task::create([
                'title'       => 'Task Sept 2 (Pending) - ' . $employee->fullname,
                'description' => 'Data testing pending untuk KPI September.',
                'assigned_to' => $employee->id,
                'due_date'    => Carbon::create(2026, 9, 20)->format('Y-m-d'),
                'status'      => 'pending',
            ]);

            // --- Mock Tasks for October (Bulan Ini) ---
            Task::create([
                'title'       => 'Task Oct 1 (Done) - ' . $employee->fullname,
                'description' => 'Data testing done untuk KPI Oktober.',
                'assigned_to' => $employee->id,
                'due_date'    => Carbon::create(2026, 10, 5)->format('Y-m-d'),
                'status'      => 'done',
                'completed_at'=> Carbon::create(2026, 10, 5)->format('Y-m-d H:i:s'),
            ]);

            Task::create([
                'title'       => 'Task Oct 2 (On Progress) - ' . $employee->fullname,
                'description' => 'Data testing progress untuk KPI Oktober.',
                'assigned_to' => $employee->id,
                'due_date'    => Carbon::create(2026, 10, 25)->format('Y-m-d'),
                'status'      => 'on progress',
            ]);
        }

        $this->command->info("Mock tasks berhasil ditambahkan!");
    }
}
