<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'employee_id',
        'browser_fingerprint_desktop',
        'browser_fingerprint_mobile',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relationship: this user belongs to an employee record.
     *
     * users.employee_id -> employees.id
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
    public function faceProfile()
    {
        return $this->hasOne(FaceProfile::class, 'user_id', 'id');
    }

    public function suspiciousActivities()
    {
        return $this->hasMany(SuspiciousActivity::class);
    }

    /* ==================== ROLE HELPER METHODS ==================== */

    /**
     * Check if user is HR Administrator
     */
    public function isHR(): bool
    {
        return $this->employee?->role?->title === \App\Constants\Roles::HR_ADMINISTRATOR;
    }

    /**
     * Check if user is Master Admin
     */
    public function isMasterAdmin(): bool
    {
        $title = $this->employee?->role?->title;
        return $title === \App\Constants\Roles::MASTER_ADMIN || $title === \App\Constants\Roles::SUPER_ADMIN;
    }

    /**
     * Check if user is Sales
     */
    public function isSales(): bool
    {
        return $this->employee?->role?->title === \App\Constants\Roles::SALES;
    }

    /**
     * Check if user is Manager / Unit Head
     */
    public function isManager(): bool
    {
        return $this->employee?->role?->title === \App\Constants\Roles::MANAGER_UNIT_HEAD;
    }

    /**
     * Check if user is Finance
     */
    public function isFinance(): bool
    {
        return $this->employee?->role?->title === \App\Constants\Roles::FINANCE;
    }

    /**
     * Check if user is an admin (HR Administrator or Master Admin)
     */
    public function isAdmin(): bool
    {
        if (!$this->employee?->role?->title) {
            return false;
        }

        return in_array($this->employee->role->title, \App\Constants\Roles::ADMIN_ROLES);
    }

    /**
     * Check if user is a supervisor (HR Administrator, Master Admin, Manager / Unit Head, or Supervisor)
     */
    public function isSupervisor(): bool
    {
        if (!$this->employee?->role?->title) {
            return false;
        }

        return in_array($this->employee->role->title, \App\Constants\Roles::SUPERVISOR_ROLES);
    }

    /**
     * Check if this user is direct supervisor of another user
     */
    public function isSupervisorOf(?User $targetUser): bool
    {
        if (!$targetUser || !$this->employee || !$targetUser->employee) {
            return false;
        }

        return $targetUser->employee->supervisor_id === $this->employee->id;
    }

    /**
     * Check if user can manage an employee (admin, manager of same dept, or supervisor)
     */
    public function canManage(Employee $employee): bool
    {
        // Admins can manage anyone
        if ($this->isAdmin()) {
            return true;
        }

        if (!$this->employee) {
            return false;
        }

        // Manager / Unit Heads can manage their department
        if ($this->isManager() && $this->employee->department_id === $employee->department_id) {
            return true;
        }

        // Direct supervisors can manage subordinates
        if ($employee->supervisor_id === $this->employee->id) {
            return true;
        }

        // Users can manage themselves
        return $this->employee->id === $employee->id;
    }

    /**
     * Check if user is in same department as employee
     */
    public function isSameDepartment(Employee $employee): bool
    {
        return $this->employee && $this->employee->department_id === $employee->department_id;
    }

    /**
     * Check if user has access to a specific module (dynamic RBAC)
     */
    public function hasAccess(string $module): bool
    {
        // Only Master Admins/Super Admins have full access by default
        $title = $this->employee?->role?->title;
        if ($title === \App\Constants\Roles::MASTER_ADMIN || $title === \App\Constants\Roles::SUPER_ADMIN) {
            return true;
        }

        $access = $this->employee?->role?->access;

        if (is_array($access)) {
            return in_array($module, $access);
        }

        return false;
    }

    /**
     * Check if user has any of the given accesses
     */
    public function hasAnyAccess(array $modules): bool
    {
        foreach ($modules as $module) {
            if ($this->hasAccess($module)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get role title
     */
    public function getRoleTitle(): ?string
    {
        return $this->employee?->role?->title;
    }
}
