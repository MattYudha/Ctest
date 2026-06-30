<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payroll extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'payroll';

    protected $fillable = [
        'employee_id',
        'period_month',
        'period_year',
        'salary',
        'transport_allowance',
        'meal_allowance',
        'position_allowance',
        'overtime_hours',
        'overtime_amount',
        'performance_bonus',
        'attendance_bonus',
        'other_bonus',
        'reimbursement',
        'bonus_notes',
        'bonuses',
        'total_earnings',
        'working_days',
        'days_present',
        'late_count',
        'late_deduction',
        'absent_count',
        'absent_deduction',
        'penalty_amount',
        'penalty_notes',
        'bpjs_kes',
        'bpjs_tk',
        'pph21',
        'other_deduction',
        'deduction_notes',
        'deductions',
        'total_deductions',
        'net_salary',
        'status',
        'financial_transaction_id',
        'pay_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'salary' => 'decimal:2',
            'transport_allowance' => 'decimal:2',
            'meal_allowance' => 'decimal:2',
            'position_allowance' => 'decimal:2',
            'overtime_hours' => 'decimal:2',
            'overtime_amount' => 'decimal:2',
            'performance_bonus' => 'decimal:2',
            'attendance_bonus' => 'decimal:2',
            'other_bonus' => 'decimal:2',
            'reimbursement' => 'decimal:2',
            'bonuses' => 'decimal:2',
            'total_earnings' => 'decimal:2',
            'late_deduction' => 'decimal:2',
            'absent_deduction' => 'decimal:2',
            'penalty_amount' => 'decimal:2',
            'bpjs_kes' => 'decimal:2',
            'bpjs_tk' => 'decimal:2',
            'pph21' => 'decimal:2',
            'other_deduction' => 'decimal:2',
            'deductions' => 'decimal:2',
            'total_deductions' => 'decimal:2',
            'net_salary' => 'decimal:2',
            'pay_date' => 'date',
        ];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Calculate total earnings from all income components.
     */
    public function calculateTotalEarnings(): float
    {
        return (float) $this->salary
            + (float) $this->transport_allowance
            + (float) $this->meal_allowance
            + (float) $this->position_allowance
            + (float) $this->overtime_amount
            + (float) $this->performance_bonus
            + (float) $this->attendance_bonus
            + (float) $this->other_bonus
            + (float) $this->reimbursement;
    }

    /**
     * Calculate total deductions from all deduction components.
     */
    public function calculateTotalDeductions(): float
    {
        return (float) $this->late_deduction
            + (float) $this->absent_deduction
            + (float) $this->penalty_amount
            + (float) $this->bpjs_kes
            + (float) $this->bpjs_tk
            + (float) $this->pph21
            + (float) $this->other_deduction;
    }

    /**
     * Calculate and set net salary.
     */
    public function calculateNetSalary(): void
    {
        $this->total_earnings = $this->calculateTotalEarnings();
        $this->bonuses = (float) $this->performance_bonus + (float) $this->attendance_bonus + (float) $this->other_bonus;
        $this->total_deductions = $this->calculateTotalDeductions();
        $this->deductions = $this->total_deductions;
        $this->net_salary = $this->total_earnings - $this->total_deductions;
    }

    /**
     * Scope: filter by period.
     */
    public function scopeForPeriod($query, int $month, int $year)
    {
        return $query->where('period_month', $month)->where('period_year', $year);
    }

    /**
     * Get period label, e.g. "Januari 2026".
     */
    public function getPeriodLabelAttribute(): string
    {
        if (!$this->period_month || !$this->period_year) {
            return '-';
        }
        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
        ];
        return ($months[$this->period_month] ?? '') . ' ' . $this->period_year;
    }

    /**
     * Get status badge HTML.
     */
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'approved' => '<span class="badge bg-primary">Approved</span>',
            'paid' => '<span class="badge bg-success">Paid</span>',
            default => '<span class="badge bg-warning">Draft</span>',
        };
    }
}
