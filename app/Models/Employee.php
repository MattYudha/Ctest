<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\EmployeeFamily;

use App\Traits\Auditable;

class Employee extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'emp_code',
        'nik',
        'fullname',
        'email',
        'profile_photo',
        'phone_number',
        'npwp',
        'place_of_birth',
        'birth_date',
        'gender',
        'religion',
        'marital_status',
        'address',
        'hire_date',
        'department_id',
        'office_location_id',
        'role_id',
        'position_id',
        'supervisor_id',
        'status',
        'employee_status',
        'working_type',
        'pph21_rate',
        'salary',
        'foundation_id',
        'education_level_id',
        'resign_date',
        'permanent_date',
        'contract_expiry',
        'salary',
        'basic_salary',
        'meal_allowance',
        'transport_allowance',
        'position_allowance',
        'foundation_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'hire_date' => 'date',
            'resign_date' => 'date',
            'permanent_date' => 'date',
            'contract_expiry' => 'date',
            'deleted_at' => 'datetime',
        ];
    }

    // Define relationships to departments and roles
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function officeLocation()
    {
        return $this->belongsTo(OfficeLocation::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function supervisor()
    {
        return $this->belongsTo(Employee::class, 'supervisor_id');
    }

    public function subordinates()
    {
        return $this->hasMany(Employee::class, 'supervisor_id');
    }

    /**
     * Relationship: this employee has one user account.
     *
     * users.employee_id -> employees.id
     */
    public function user()
    {
        return $this->hasOne(User::class, 'employee_id', 'id');
    }

    public function faceProfile()
    {
        return $this->hasOneThrough(FaceProfile::class, User::class, 'employee_id', 'user_id', 'id', 'id');
    }

    /**
     * Relationship: an employee can have many mutations.
     */
    public function mutations()
    {
        return $this->hasMany(EmployeeMutation::class)->latest();
    }

    public function foundation()
    {
        return $this->belongsTo(Foundation::class, 'foundation_id');
    }

    public function educationLevel()
    {
        return $this->belongsTo(EducationLevel::class, 'education_level_id');
    }

    public function employeePositions()
    {
        return $this->hasMany(EmployeePosition::class);
    }

    public function families()
    {
        return $this->hasMany(EmployeeFamily::class);
    }

    public function bankAccounts()
    {
        return $this->hasMany(BankAccount::class);
    }

    public function documentIdentities()
    {
        return $this->hasMany(DocumentIdentity::class);
    }

    /**
     * Scope a query to only include employees visible to the user.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \App\Models\User  $user
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function leaveBalances()
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function kpiRecords()
    {
        return $this->hasMany(EmployeeKPIRecord::class);
    }

    public function incidents()
    {
        return $this->hasMany(Incident::class);
    }

    /**
     * Get or create current year leave balance for a specific type.
     */
    public function getLeaveBalance($type = 'annual')
    {
        return $this->leaveBalances()->firstOrCreate(
            ['leave_type' => $type, 'year' => date('Y')],
            ['entitlement' => 12, 'taken' => 0, 'balance' => 12],
        );
    }

    public function scopeVisibleTo($query, User $user)
    {
        // ... (existing code omitted for brevity but I will keep it in the replacement)
        if ($user->isAdmin()) {
            return $query;
        }

        $employee = $user->employee;

        if (!$employee) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isManager()) {
            return $query->where(function ($q) use ($employee) {
                $q->where('employees.department_id', $employee->department_id)->orWhere(
                    'employees.supervisor_id',
                    $employee->id,
                );
            });
        }

        // Supervisors can see their subordinates
        if ($user->employee?->role?->title === \App\Constants\Roles::SUPERVISOR) {
            return $query->where(function ($q) use ($employee) {
                $q->where('employees.supervisor_id', $employee->id)->orWhere('employees.id', $employee->id);
            });
        }

        // Default to self for Employee/ESS
        return $query->where('employees.id', $employee->id);
    }

    /**
     * Get the badge for employee status.
     */
    public function getEmployeeStatusBadgeAttribute()
    {
        $status = $this->employee_status ?: 'permanent';
        $labels = [
            'permanent' => '<span class="badge bg-primary">Tetap</span>',
            'contract' => '<span class="badge bg-info">Kontrak</span>',
            'probation' => '<span class="badge bg-warning text-dark">Probasi</span>',
            'internship' => '<span class="badge bg-secondary">Magang</span>',
        ];

        return $labels[$status] ?? '<span class="badge bg-light text-dark">' . ucfirst($status) . '</span>';
    }

    /**
     * Get available employment statuses.
     */
    public static function getAvailableStatuses()
    {
        return [
            'permanent' => 'Tetap',
            'contract' => 'Kontrak',
            'probation' => 'Probasi',
            'internship' => 'Magang',
        ];
    }

    /**
     * Accessor Helper untuk mengambil posisi/jabatan yang sedang aktif
     * Cara pakai di Blade/Controller: $employee->active_position
     */
    public function getActivePositionAttribute()
    {
        return $this->employeePositions()->where('is_active', true)->first()?->position;
    }

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id', 'position_id');
    }
}
