<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Letter extends Model
{
    protected $fillable = [
        'user_id',
        'approver_id',
        'letter_template_id',
        'letter_number',
        'subject',
        'content',
        'start_date',
        'letter_type',
        'status',
        'created_date',
        'approved_date',
        'printed_date',
        'meta_data',
        'notes',
        'purpose',
        'end_date',
        'reason',
        'days',
        'period',
        'recommender_name',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_date' => 'date',
            'approved_date' => 'datetime',
            'printed_date' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($letter) {
            $letter->signatures()->delete();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function template()
    {
        return $this->belongsTo(LetterTemplate::class, 'letter_template_id');
    }

    /**
     * Get all signatures for this letter (polymorphic)
     */
    public function signatures()
    {
        return $this->morphMany(Signature::class, 'signable');
    }

    /**
     * Get the content with placeholders replaced.
     */
    public function getFormattedContentAttribute()
    {
        // Prefer the saved content; but if it's empty/too short, fall back to the selected template's content
        $content = trim((string) $this->content);
        if (strlen($content) < 50 && $this->template) {
            $content = $this->template->content ?? $content;
        }

        $config = LetterConfiguration::first();
        $user = $this->user;
        $employee = $user->employee;

        $replacements = [
            '[NIK]' => $employee->nik ?? '-',
            '[NPWP]' => $employee->npwp ?? '-',
            '[EMPLOYEE_STATUS]' => $employee->employee_status
                ? \App\Models\Employee::getAvailableStatuses()[$employee->employee_status] ?? $employee->employee_status
                : '-',
            '[EMPLOYEE_NAME]' => $employee->fullname ?? $user->name,
            '[COMPANY_NAME]' => $config->company_name ?? 'PT Aratech Indonesia',
            '[POSITION]' => $employee->role->title ?? '-',
            '[DEPARTMENT]' => $employee->department->name ?? '-',
            '[SALARY]' => $employee->salary ? 'Rp ' . number_format($employee->salary, 0, ',', '.') : '-',
            '[ADDRESS]' => $employee->address ?? '-',
            '[PHONE]' => $employee->phone_number ?? '-',
            '[EMAIL]' => $employee->email ?? '-',
            '[DATE]' =>
                $this->created_date instanceof \Carbon\Carbon
                    ? $this->created_date->format('d F Y')
                    : \Carbon\Carbon::parse($this->created_date)->format('d F Y'),
            '[START_DATE]' =>
                $this->start_date ?:
                ($employee->hire_date
                    ? ($employee->hire_date instanceof \Carbon\Carbon
                        ? $employee->hire_date->format('d F Y')
                        : \Carbon\Carbon::parse($employee->hire_date)->format('d F Y'))
                    : '-'),
            '[PURPOSE]' => $this->purpose ?? '-',
            '[END_DATE]' => $this->end_date ?? 'Present / Saat ini',
            '[REASON]' => $this->reason ?? '-',
            '[DAYS]' => $this->days ?? '-',
            '[PERIOD]' => $this->period ?? '-',
            '[RECOMMENDER_NAME]' => $this->recommender_name ?? '-',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }
}
