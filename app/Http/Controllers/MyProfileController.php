<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;

class MyProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        if (!$user || !$user->employee_id) {
            return redirect()->route('dashboard')->with('error', 'Employee record not found.');
        }

        $employee = Employee::with([
            'department', 
            'role', 
            'educationLevel', 
            'families', 
            'bankAccounts',
            'documentIdentities.identityType',
            'employeePositions.position',
            'mutations.oldDepartment',
            'mutations.newDepartment',
            'mutations.oldRole',
            'mutations.newRole'
        ])->findOrFail($user->employee_id);

        return view('profile.my-profile', compact('employee'));
    }
    public function edit()
    {
        $user = Auth::user();
        if (!$user || !$user->employee_id) {
            return redirect()->route('dashboard')->with('error', 'Employee record not found.');
        }

        $employee = Employee::findOrFail($user->employee_id);

        return view('profile.edit-my-profile', compact('employee'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->employee_id) {
            return redirect()->route('dashboard')->with('error', 'Employee record not found.');
        }

        $employee = Employee::findOrFail($user->employee_id);

        $request->validate([
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'place_of_birth' => 'nullable|string|max:100',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'religion' => 'nullable|string|max:50',
            'marital_status' => 'nullable|in:single,married,divorced,widowed',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->only([
            'phone_number', 'address', 'place_of_birth', 'birth_date', 
            'gender', 'religion', 'marital_status'
        ]);

        if ($request->hasFile('profile_photo')) {
            if ($employee->profile_photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($employee->profile_photo)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($employee->profile_photo);
            }
            $data['profile_photo'] = $request->file('profile_photo')->store('profile_photos', 'public');
        }

        $employee->update($data);

        return redirect()->route('my-profile')->with('success', 'Profile updated successfully.');
    }
}
