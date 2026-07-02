<?php

namespace App\Constants;

class Roles
{
    // Role Titles
    const SUPER_ADMIN = 'Super Admin';
    const MASTER_ADMIN = 'Master Admin'; // Rebranded from Super Admin
    const HR_ADMINISTRATOR = 'HR Administrator';
    const MANAGER_UNIT_HEAD = 'Manager / Unit Head';
    const SUPERVISOR = 'Supervisor';
    const EMPLOYEE = 'Employee';
    const MARKETING = 'Marketing';
    const FINANCE = 'Finance';
    const SALES = 'Sales';

    // Role Groups
    const ADMIN_ROLES = [self::SUPER_ADMIN, self::MASTER_ADMIN, self::HR_ADMINISTRATOR];

    const FINANCE_FULL_ROLES = [self::SUPER_ADMIN, self::MASTER_ADMIN, self::HR_ADMINISTRATOR, self::FINANCE];

    const FINANCE_OPERATOR_ROLES = [
        self::SUPER_ADMIN,
        self::MASTER_ADMIN,
        self::HR_ADMINISTRATOR,
        self::MANAGER_UNIT_HEAD,
        self::MARKETING,
        self::FINANCE,
    ];

    const FINANCE_VIEW_ROLES = [
        self::SUPER_ADMIN,
        self::MASTER_ADMIN,
        self::HR_ADMINISTRATOR,
        self::MANAGER_UNIT_HEAD,
        self::MARKETING,
        self::SUPERVISOR,
        self::FINANCE,
    ];

    const SUPERVISOR_ROLES = [
        self::SUPER_ADMIN,
        self::MASTER_ADMIN,
        self::HR_ADMINISTRATOR,
        self::MANAGER_UNIT_HEAD,
        self::SUPERVISOR,
    ];

    const ALL_ROLES = [
        self::SUPER_ADMIN,
        self::MASTER_ADMIN,
        self::HR_ADMINISTRATOR,
        self::MANAGER_UNIT_HEAD,
        self::SUPERVISOR,
        self::EMPLOYEE,
        self::MARKETING,
        self::FINANCE,
        self::SALES,
    ];

    /**
     * Check if role is admin role
     */
    public static function isAdmin(string $role): bool
    {
        return in_array($role, self::ADMIN_ROLES);
    }

    /**
     * Check if role has full finance access
     */
    public static function hasFullFinanceAccess(string $role): bool
    {
        return in_array($role, self::FINANCE_FULL_ROLES);
    }

    /**
     * Check if role can operate (input) finance transactions
     */
    public static function canOperateFinance(string $role): bool
    {
        return in_array($role, self::FINANCE_OPERATOR_ROLES);
    }

    /**
     * Check if role is supervisor role
     */
    public static function isSupervisor(string $role): bool
    {
        return in_array($role, self::SUPERVISOR_ROLES);
    }

    /**
     * Get all role names
     */
    public static function all(): array
    {
        return self::ALL_ROLES;
    }
}
