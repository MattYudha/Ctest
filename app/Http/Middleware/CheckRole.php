<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = auth()->user();

        // Validate user exists
        if (!$user) {
            abort(401, 'Unauthenticated');
        }

        // Get employee ID
        $employeeID = $user->employee_id;

        // Check if employee ID exists
        if (!$employeeID) {
            \Log::warning('User without employee_id attempted access', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);
            abort(403, 'No employee profile associated with this user');
        }

        // Find employee
        $employee = Employee::find($employeeID);

        // Validate employee exists
        if (!$employee) {
            \Log::error('Employee not found for user', [
                'user_id' => $user->id,
                'employee_id' => $employeeID
            ]);
            abort(403, 'Employee profile not found');
        }

        // Validate employee has role
        if (!$employee->role) {
            \Log::error('Employee without role attempted access', [
                'employee_id' => $employee->id
            ]);
            abort(403, 'No role assigned to employee');
        }

        // Master Admin & Super Admin always have full access to all protected routes
        if ($user->isMasterAdmin()) {
            return $next($request);
        }

        $checkRoles = array_map('trim', $roles);
        
        // Match by Role Title (String)
        $userRoleTitle = trim($employee->role->title);
        $hasRole = in_array($userRoleTitle, $checkRoles);
        
        // Match by Module Access (JSON array in DB)
        $hasAccess = false;
        if (is_array($employee->role->access)) {
            foreach ($checkRoles as $requiredRole) {
                if (in_array($requiredRole, $employee->role->access)) {
                    $hasAccess = true;
                    break;
                }
            }
        }

        if (!$hasRole && !$hasAccess) {
            \Log::warning('Unauthorized access attempt', [
                'user_id' => $user->id,
                'employee_role' => $employee->role->title,
                'required_roles_or_access' => $roles,
                'url' => $request->url()
            ]);
            abort(403, 'Unauthorized action for your role');
        }

        return $next($request);
    }
}
