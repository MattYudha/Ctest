<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    // Display a list of all roles
    public function index()
    {
        $roles = Role::all();
        return view('roles.index', compact('roles'));
    }

    // Show the form to create a new role
    public function create()
    {
        $this->ensureMasterAdmin();
        $modules = $this->accessModules();
        return view('roles.create', compact('modules'));
    }

    // Store a newly created role
    public function store(Request $request)
    {
        $this->ensureMasterAdmin();

        $request->validate([
            'title' => 'required|unique:roles,title|max:255',
            'description' => 'nullable',
            'access' => 'array',
            'access.*' => 'string',
        ]);

        Role::create([
            'title' => $request->title,
            'description' => $request->description,
            'access' => $request->input('access', []),
        ]);

        return redirect()->route('roles.index')->with('success', 'Role created successfully');
    }

    // Show the form for editing a role
    public function edit(Role $role)
    {
        $this->ensureMasterAdmin();
        $modules = $this->accessModules();
        return view('roles.edit', compact('role', 'modules'));
    }

    // Update the specified role
    public function update(Request $request, Role $role)
    {
        $this->ensureMasterAdmin();

        $request->validate([
            'title' => 'required|max:255|unique:roles,title,' . $role->id,
            'description' => 'nullable',
            'access' => 'array',
            'access.*' => 'string',
        ]);

        $role->update([
            'title' => $request->title,
            'description' => $request->description,
            'access' => $request->input('access', []),
        ]);

        return redirect()->route('roles.index')->with('success', 'Role updated successfully');
    }

    // Delete a role
    public function destroy(Role $role)
    {
        $this->ensureMasterAdmin();

        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Role deleted successfully');
    }

    /**
     * Ensure current user is Master Admin
     * Checks database role, not session (prevents bypass via stale session)
     */
    private function ensureMasterAdmin(): void
    {
        $user = auth()->user();
        $employee = $user?->employee;
        
        // Check if employee exists and has Master Admin role
        if (!$employee || !$employee->role || $employee->role->title !== \App\Constants\Roles::MASTER_ADMIN) {
            \Log::warning('Non-Master Admin attempted role management', [
                'user_id' => $user?->id,
                'role' => $employee?->role?->title ?? 'none'
            ]);
            
            abort(403, 'Unauthorized: Only Master Admins can manage roles.');
        }
    }

    private function accessModules(): array
    {
        return [
            ['key' => 'inventory_logs', 'label' => 'Laporan Inventory Logs'],
            ['key' => 'inventory_usage', 'label' => 'Penggunaan Inventory'],
            ['key' => 'inventory', 'label' => 'Inventaris & Kategori'],
            ['key' => 'inventory_requests', 'label' => 'Permintaan Inventory'],
            ['key' => 'attendance', 'label' => 'Presensi & Kehadiran'],
            ['key' => 'hr_reports', 'label' => 'Laporan HR Administrator / KPI'],
            ['key' => 'knowledge_base', 'label' => 'Knowledge Base & Artikel'],
            ['key' => \App\Constants\Roles::SALES, 'label' => 'CRM & Sales'],
        ];
    }
}
