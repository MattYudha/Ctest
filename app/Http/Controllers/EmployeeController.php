<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use App\Models\Department;
use App\Models\Role;
use App\Models\Presence;
use App\Models\EmployeeMutation;
use App\Models\OfficeLocation;
use Illuminate\Http\Request;
use App\Models\EducationLevel;
use App\Models\IdentityType;
use App\Models\EmployeeFamily;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Position;

class EmployeeController extends Controller
{
    // Display a list of employees
    public function index(Request $request)
    {
        $this->authorize('viewAny', Employee::class);

        if ($request->ajax()) {
            $user = auth()->user();
            \Log::info('datatables.employees', [
                'user_id' => $user?->id,
                'role' => $user?->employee?->role?->title,
                'visible_count' => Employee::visibleTo($user)->count(),
            ]);
            $data = Employee::with(['department', 'role', 'officeLocation'])->visibleTo($user);

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) use ($user) {
                    $viewUrl = route('employees.show', $row->id);
                    $editUrl = route('employees.edit', $row->id);
                    $deleteUrl = route('employees.destroy', $row->id);
                    $csrf = csrf_token();

                    $canUpdate = $user->can('update', $row);
                    $canDelete = $user->can('delete', $row);

                    $btns = '<div class="btn-group btn-group-sm" role="group">';
                    $btns .= '<a href="' . $viewUrl . '" class="btn btn-outline-info"><i class="bi bi-eye"></i></a>';

                    if ($canUpdate) {
                        $btns .=
                            '<a href="' .
                            $editUrl .
                            '" class="btn btn-outline-warning"><i class="bi bi-pencil"></i></a>';
                    }
                    if ($canDelete) {
                        $btns .=
                            '
                            <form action="' .
                            $deleteUrl .
                            '" method="POST" class="d-inline delete-form" data-id="' .
                            $row->id .
                            '">
                                <input type="hidden" name="_token" value="' .
                            $csrf .
                            '">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn btn-outline-danger btn-delete" title="Delete Employee">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        ';
                    }

                    if ($user->isAdmin()) {
                        $resetDeviceUrl = route('employees.reset-device', $row->id);
                        $btns .=
                            '
                            <form action="' .
                            $resetDeviceUrl .
                            '" method="POST" class="d-inline" data-id="' .
                            $row->id .
                            '">
                                <input type="hidden" name="_token" value="' .
                            $csrf .
                            '">
                                <button type="submit" class="btn btn-outline-secondary" title="Reset Device Fingerprint" onclick="return confirm(\'Reset device fingerprint for ' .
                            $row->fullname .
                            '?\')">
                                    <i class="bi bi-phone-vibrate"></i>
                                </button>
                            </form>
                        ';
                    }

                    $btns .= '</div>';
                    return $btns;
                })
                ->addColumn('status_badge', function ($row) {
                    if ($row->status === 'active') {
                        return '<span class="badge bg-success">Active</span>';
                    }
                    return '<span class="badge bg-warning text-dark">Inactive</span>';
                })
                ->addColumn('employee_status_badge', function ($row) {
                    return $row->employee_status_badge;
                })
                ->addColumn('office_location_name', function ($row) {
                    return $row->officeLocation?->name ?? '-';
                })
                ->editColumn('fullname', function ($row) {
                    $avatar = $row->profile_photo 
                        ? '<img src="' . asset('storage/' . $row->profile_photo) . '" class="rounded-circle me-2 shadow-sm" style="width: 34px; height: 34px; object-fit: cover; border: 2px solid #435ebe;">'
                        : '<span class="avatar avatar-sm bg-primary text-white rounded-circle me-2 d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 34px; height: 34px; font-size: 13px; font-weight: bold; border: 2px solid #435ebe;">' . strtoupper(substr($row->fullname, 0, 2)) . '</span>';
                    return '<div class="d-flex align-items-center">' . $avatar . '<span class="fw-semibold">' . e($row->fullname) . '</span></div>';
                })
                ->editColumn('salary', function ($row) use ($user) {
                    if ($user->isAdmin()) {
                        return 'Rp ' . number_format($row->salary, 0, ',', '.');
                    }
                    return '***';
                })
                ->rawColumns(['fullname', 'action', 'status_badge', 'employee_status_badge'])
                ->make(true);
        }
        return view('employees.index');
    }

    // Show the form for creating a new employee
    public function create()
    {
        $this->authorize('create', Employee::class);
        $departments = Department::with('manager')->get();
        $roles = Role::all();
        $officeLocations = OfficeLocation::orderBy('name')->get();
        // Filter supervisors to only those with "Manager / Unit Head" role
        $employees = Employee::whereHas('role', function ($q) {
            $q->where('title', \App\Constants\Roles::MANAGER_UNIT_HEAD);
        })
            ->orderBy('fullname')
            ->get();
        $positions = Position::all();
        return view('employees.create', compact('departments', 'roles', 'officeLocations', 'employees', 'positions'));
    }

    // Store a newly created employee in storage
    public function store(Request $request)
    {
        $this->authorize('create', Employee::class);

        $request->validate([
            'nik' => 'required|string|unique:employees,nik',
            'npwp' => 'required|string|max:50',
            'fullname' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email',
            'phone_number' => 'required|string|max:20',
            'address' => 'required|string',
            'birth_date' => 'required|date',
            'place_of_birth' => 'nullable|string|max:255',
            'gender' => 'nullable|string|max:50',
            'religion' => 'nullable|string|max:100',
            'position_id' => 'required|exists:positions,position_id',
            'marital_status' => 'nullable|string|max:100',
            'hire_date' => 'required|date',
            'resign_date' => 'nullable|date',
            'permanent_date' => 'nullable|date',
            'contract_expiry' => 'nullable|date',
            'department_id' => 'required|exists:departments,id',
            'office_location_id' => 'required|exists:office_locations,id',
            'role_id' => 'required|exists:roles,id',
            'supervisor_id' => 'nullable|exists:employees,id',
            'status' => 'required|string|in:active,inactive',
            'employee_status' => 'required|string|in:permanent,contract,probation,internship',
            'working_type' => 'required|string|in:full_time,part_time',
            'pph21_rate' => 'nullable|numeric|min:0|max:100',
            'password' => 'required|string|min:8',
            'salary' => 'required|numeric',
            'basic_salary' => 'nullable|numeric',
            'meal_allowance' => 'nullable|numeric',
            'transport_allowance' => 'nullable|numeric',
            'position_allowance' => 'nullable|numeric',
        ]);

        $employee = Employee::create([
            'nik' => $request->nik,
            'npwp' => $request->npwp,
            'fullname' => $request->fullname,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'address' => $request->address,
            'birth_date' => $request->birth_date,
            'place_of_birth' => $request->place_of_birth,
            'gender' => $request->gender,
            'religion' => $request->religion,
            'marital_status' => $request->marital_status,
            'hire_date' => $request->hire_date,
            'resign_date' => $request->resign_date,
            'permanent_date' => $request->permanent_date,
            'contract_expiry' => $request->contract_expiry,
            'department_id' => $request->department_id,
            'office_location_id' => $request->office_location_id,
            'role_id' => $request->role_id,
            'supervisor_id' => $request->supervisor_id,
            'status' => $request->status,
            'employee_status' => $request->employee_status,
            'working_type' => $request->working_type,
            'pph21_rate' => $request->pph21_rate ?? 0,
            'salary' => $request->salary,
            'basic_salary' => $request->basic_salary ?: 0,
            'meal_allowance' => $request->meal_allowance ?: 0,
            'transport_allowance' => $request->transport_allowance ?: 0,
            'position_allowance' => $request->position_allowance ?: 0,
        ]);

        // Auto-create a User account for this employee if not exists
        $user = User::where('email', $employee->email)->first();

        if (!$user) {
            User::create([
                'name' => $employee->fullname,
                'email' => $employee->email,
                'password' => Hash::make($request->password),
                'employee_id' => (string) $employee->id,
            ]);
        } else {
            // Ensure employee id is linked
            if (empty($user->employee_id)) {
                $user->employee_id = (string) $employee->id;
                $user->save();
            }
        }

        $message = 'Employee created successfully.';

        return redirect()->route('employees.index')->with('success', $message);
    }

    // Display the specified employee
    public function show($id)
    {
        $employee = Employee::with(['department', 'role', 'officeLocation'])->findOrFail($id);
        $this->authorize('view', $employee);

        $present = Presence::where(['employee_id' => $id, 'status' => 'present'])->count();
        $absent = Presence::where(['employee_id' => $id, 'status' => 'absent'])->count();
        $leave = Presence::where(['employee_id' => $id, 'status' => 'leave'])->count();

        return view('employees.show', compact('employee', 'present', 'absent', 'leave'));
    }

    // Show the form for editing the specified employee
    public function edit($id)
    {
        $employee = Employee::findOrFail($id);
        $user = auth()->user();
        $isAdmin = $user->isAdmin();

        if (!$isAdmin && ($user->employee_id != $id)) {
            return redirect()->route('my-profile')->with('error', 'Anda hanya memiliki akses untuk mengedit profil Anda sendiri.');
        }

        $departments = Department::all();
        $roles = Role::all();
        $officeLocations = OfficeLocation::orderBy('name')->get();
        $educationLevels = EducationLevel::all();
        $identityTypes = IdentityType::all();
        $families = $employee->families()->get();
        $bankAccounts = $employee->bankAccounts()->get();
        $documentIdentities = $employee->documentIdentities()->get();
        $userAccount = User::where('employee_id', $id)->first();
        $positions = Position::all();

        // Potential supervisors (all employees except self)
        $potentialSupervisors = Employee::where('id', '!=', $id)->get();

        return view(
            'employees.edit',
            compact(
                'employee',
                'departments',
                'roles',
                'officeLocations',
                'potentialSupervisors',
                'educationLevels',
                'families',
                'bankAccounts',
                'documentIdentities',
                'identityTypes',
                'userAccount',
                'positions',
            ),
        );
    }

    // Update the specified employee in storage
    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);
        $this->authorize('update', $employee);

        $user = auth()->user();
        $isAdmin = $user->isAdmin();

        // Ensure missing or blank fields are populated with existing employee data so updating avatar/profile never fails validation
        $request->merge([
            'salary' => $request->filled('salary') ? $request->salary : ($employee->salary ?? 0),
            'basic_salary' => $request->filled('basic_salary') ? $request->basic_salary : ($employee->basic_salary ?? 0),
            'meal_allowance' => $request->filled('meal_allowance') ? $request->meal_allowance : ($employee->meal_allowance ?? 0),
            'transport_allowance' => $request->filled('transport_allowance') ? $request->transport_allowance : ($employee->transport_allowance ?? 0),
            'position_allowance' => $request->filled('position_allowance') ? $request->position_allowance : ($employee->position_allowance ?? 0),
            'department_id' => $request->filled('department_id') ? $request->department_id : $employee->department_id,
            'office_location_id' => $request->filled('office_location_id') ? $request->office_location_id : $employee->office_location_id,
            'position_id' => $request->filled('position_id') ? $request->position_id : ($employee->position_id ?? $employee->active_position?->id),
            'role_id' => $request->filled('role_id') ? $request->role_id : $employee->role_id,
            'status' => $request->filled('status') ? $request->status : ($employee->status ?? 'active'),
            'employee_status' => $request->filled('employee_status') ? $request->employee_status : ($employee->employee_status ?? 'permanent'),
            'working_type' => $request->filled('working_type') ? $request->working_type : ($employee->working_type ?? 'full_time'),
            'pph21_rate' => $request->filled('pph21_rate') ? $request->pph21_rate : ($employee->pph21_rate ?? 0),
            'npwp' => $request->filled('npwp') ? $request->npwp : ($employee->npwp ?? '00.000.000.0-000.000'),
            'hire_date' => $request->filled('hire_date') ? $request->hire_date : ($employee->hire_date ? $employee->hire_date->format('Y-m-d') : now()->format('Y-m-d')),
            'phone_number' => $request->filled('phone_number') ? $request->phone_number : ($employee->phone_number ?? '-'),
        ]);

        $request->validate([
            'nik' => 'required|string|unique:employees,nik,' . $id,
            'npwp' => 'nullable|string|max:50',
            'fullname' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email,' . $id,
            'phone_number' => 'nullable|string|max:30', // Max 30 to support formatted phones with dashes/spaces
            'address' => 'nullable|string',
            'place_of_birth' => 'nullable|string|max:150',
            'birth_date' => 'nullable|date',
            'hire_date' => 'nullable|date',
            'department_id' => 'nullable|exists:departments,id',
            'office_location_id' => 'nullable|exists:office_locations,id',
            'role_id' => 'nullable|exists:roles,id',
            'supervisor_id' => 'nullable|exists:employees,id',
            'position_id' => 'nullable|exists:positions,position_id',
            'status' => 'nullable|string|max:50',
            'employee_status' => 'nullable|string|in:permanent,contract,probation,internship',
            'working_type' => 'nullable|string|in:full_time,part_time',
            'pph21_rate' => 'nullable|numeric|min:0|max:100',
            'education_level_id' => 'nullable|exists:education_levels,education_level_id',
            'gender' => 'nullable|string|max:50',
            'religion' => 'nullable|string|max:100',
            'marital_status' => 'nullable|string|max:100',
            'resign_date' => 'nullable|date',
            'permanent_date' => 'nullable|date',
            'contract_expiry' => 'nullable|date',
            'families' => 'array',
            'families.*.id' => 'nullable|integer',
            'families.*.fullname' => 'nullable|string|max:255',
            'families.*.relation' => 'nullable|string|max:100',
            'families.*.nik' => 'nullable|string|max:100',
            'families.*.no_kk' => 'nullable|string|max:100',
            'families.*.place_of_birth' => 'nullable|string|max:150',
            'families.*.date_of_birth' => 'nullable|date',
            'families.*.gender' => 'nullable|string|max:50',
            'mutation_reason' => 'nullable|string|max:500',
            'banks' => 'array',
            'banks.*.id' => 'nullable|integer',
            'banks.*.bank_name' => 'nullable|string|max:255',
            'banks.*.account_no' => 'nullable|string|max:255',
            'banks.*.account_holder' => 'nullable|string|max:255',
            'bank_primary_index' => 'nullable',
            'documents' => 'array',
            'documents.*.id' => 'nullable|integer',
            'documents.*.identity_type_id' => 'nullable|integer|exists:identity_types,identity_type_id',
            'documents.*.identity_number' => 'nullable|string|max:255',
            'documents.*.description' => 'nullable|string|max:500',
            'password' => 'nullable|string|min:8|confirmed',
            'salary' => 'nullable|numeric',
            'basic_salary' => 'nullable|numeric',
            'meal_allowance' => 'nullable|numeric',
            'transport_allowance' => 'nullable|numeric',
            'position_allowance' => 'nullable|numeric',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $oldEmail = $employee->email;
        $oldData = $employee->only(['department_id', 'role_id', 'salary']);
        $data = $request->except(['supervisor_id', '_token', '_method']);

        $canEditEducation = true;

        // Only allow Admin to update supervisor_id
        if ($isAdmin && $request->filled('supervisor_id')) {
            $data['supervisor_id'] = $request->supervisor_id;
        }

        if (!$canEditEducation) {
            unset($data['education_level_id']);
        }

        if ($request->hasFile('profile_photo')) {
            $file = $request->file('profile_photo');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('profile_photos', $filename, 'public');
            $data['profile_photo'] = $path;
        } else {
            unset($data['profile_photo']);
        }

        // Sanitize nullable/foreign key empty strings to null or preserve existing values
        $nullableFks = [
            'position_id', 'office_location_id', 'department_id', 'role_id',
            'education_level_id', 'supervisor_id', 'place_of_birth', 'birth_date',
            'hire_date', 'resign_date', 'permanent_date', 'contract_expiry',
            'gender', 'religion', 'marital_status'
        ];

        foreach ($nullableFks as $field) {
            if (array_key_exists($field, $data)) {
                if ($data[$field] === '' || $data[$field] === null) {
                    $data[$field] = $employee->$field ?? null;
                }
            }
        }

        $approvalNotice = false;

        // Admins apply changes directly
        if ($isAdmin) {
            $employeeFillable = $employee->getFillable();
            $employeeData = collect($data)->only($employeeFillable)->toArray();
            $employee->update($employeeData);

            // Update password if provided by Admin
            if ($request->filled('password') && $employee->user) {
                $employee->user->update([
                    'password' => Hash::make($request->password),
                ]);
            }
        } else {
            // Non-admin updating self profile
            // Save personal data (fullname, photo, phone, address, place_of_birth, birth_date, etc.) directly
            $sensitiveFields = ['nik', 'npwp', 'email'];
            $nonSensitiveData = collect($data)->except(array_merge($sensitiveFields, [
                'supervisor_id', 'salary', 'basic_salary', 'meal_allowance', 'transport_allowance',
                'position_allowance', 'department_id', 'office_location_id', 'position_id', 'role_id',
                'status', 'employee_status', 'working_type', 'pph21_rate', 'hire_date'
            ]))->toArray();

            if (!empty($nonSensitiveData)) {
                $employee->update($nonSensitiveData);

                // If fullname updated, also update linked User name
                if ($request->filled('fullname') && $employee->user) {
                    $employee->user->update([
                        'name' => $request->fullname
                    ]);
                }
            }

            // Check if sensitive identifier fields (NIK, NPWP, Email) were modified
            $sensitiveChanges = [];
            $sensitiveOldData = [];

            foreach ($sensitiveFields as $field) {
                if ($request->has($field)) {
                    $newVal = (string) $request->input($field);
                    $oldVal = (string) $employee->$field;

                    if (filled($newVal) && $newVal !== $oldVal) {
                        $sensitiveChanges[$field] = $newVal;
                        $sensitiveOldData[$field] = $oldVal;
                    }
                }
            }

            if (!empty($sensitiveChanges)) {
                \App\Models\EmployeeUpdateApproval::create([
                    'employee_id' => $employee->id,
                    'requested_by' => auth()->id(),
                    'old_data' => $sensitiveOldData,
                    'new_data' => $sensitiveChanges,
                    'status' => 'pending',
                ]);
                $approvalNotice = true;
            }
        }

        // All users can update their own personal data sections (Family, Bank, Documents)
        // Handle family data
        $familiesInput = $request->input('families', []);
        $existingFamilies = $employee->families()->get()->keyBy('id');

        foreach ($familiesInput as $family) {
            $fields = collect($family)
                ->only(['nik', 'no_kk', 'fullname', 'place_of_birth', 'date_of_birth', 'gender', 'relation'])
                ->toArray();

            $isEmpty = collect($fields)
                ->filter(function ($value) {
                    return filled($value);
                })
                ->isEmpty();

            if ($isEmpty) {
                continue;
            }

            // Update existing family record
            if (!empty($family['id']) && $existingFamilies->has($family['id'])) {
                $existingFamilies[$family['id']]->update($fields);
                continue;
            }

            // Create new family record
            $employee->families()->create($fields);
        }

        $familiesToDelete = $request->input('families_to_delete', []);
        if (!empty($familiesToDelete)) {
            $employee->families()->whereIn('id', $familiesToDelete)->delete();
        }

        // Handle Bank Accounts
        $banksInput = $request->input('banks', []);
        $primaryIndex = $request->input('bank_primary_index');
        $existingBanks = $employee->bankAccounts()->get()->keyBy('id');

        foreach ($banksInput as $index => $bank) {
            $fields = collect($bank)
                ->only(['bank_name', 'account_no', 'account_holder'])
                ->toArray();
            if (empty($fields['bank_name']) && empty($fields['account_no'])) {
                continue;
            }

            $fields['is_primary'] = $index == $primaryIndex;

            if (!empty($bank['id']) && $existingBanks->has($bank['id'])) {
                $existingBanks[$bank['id']]->update($fields);
                continue;
            }
            $employee->bankAccounts()->create($fields);
        }

        $banksToDelete = $request->input('banks_to_delete', []);
        if (!empty($banksToDelete)) {
            $employee->bankAccounts()->whereIn('id', $banksToDelete)->delete();
        }

        // Handle Document Identities
        $docsInput = $request->input('documents', []);
        $existingDocs = $employee->documentIdentities()->get()->keyBy('id');

        foreach ($docsInput as $index => $doc) {
            $fields = collect($doc)
                ->only(['identity_type_id', 'identity_number', 'description'])
                ->toArray();
            if (empty($fields['identity_number'])) {
                continue;
            }

            if (!empty($doc['id']) && $existingDocs->has($doc['id'])) {
                $existingDocs[$doc['id']]->update($fields);
                continue;
            }
            $employee->documentIdentities()->create($fields);
        }

        $docsToDelete = $request->input('documents_to_delete', []);
        if (!empty($docsToDelete)) {
            $employee->documentIdentities()->whereIn('id', $docsToDelete)->delete();
        }

        // Track mutation if key fields changed
        $hasMutation =
            (int) $oldData['department_id'] !== (int) $employee->department_id ||
            (int) $oldData['role_id'] !== (int) $employee->role_id ||
            (float) $oldData['salary'] !== (float) $employee->salary;

        if ($hasMutation) {
            EmployeeMutation::create([
                'employee_id' => $employee->id,
                'old_department_id' => $oldData['department_id'],
                'new_department_id' => $employee->department_id,
                'old_role_id' => $oldData['role_id'],
                'new_role_id' => $employee->role_id,
                'old_salary' => $oldData['salary'],
                'new_salary' => $employee->salary,
                'mutation_date' => now(),
                'type' => $this->determineMutationType($oldData, $employee),
                'reason' => $request->mutation_reason,
                'created_by' => auth()->id(),
            ]);
        }

        // If email changed, update user record if exists
        if ($oldEmail !== $employee->email) {
            $userRecord = User::where('employee_id', $id)->orWhere('email', $oldEmail)->first();
            if ($userRecord) {
                $userRecord->email = $employee->email;
                $userRecord->name = $employee->fullname;
                $userRecord->save();
            }
        }

        // Handle Fingerprint Resets
        if ($isAdmin) {
            $userRecord = User::where('employee_id', $id)->first();
            if ($userRecord) {
                $fingerprintChanged = false;
                if ($request->has('reset_desktop_fingerprint')) {
                    $userRecord->browser_fingerprint_desktop = null;
                    $fingerprintChanged = true;
                }
                if ($request->has('reset_mobile_fingerprint')) {
                    $userRecord->browser_fingerprint_mobile = null;
                    $fingerprintChanged = true;
                }
                if ($fingerprintChanged) {
                    $userRecord->save();
                }
            }
        }

        if ($isAdmin) {
            return redirect()->back()->with('success', 'Data karyawan & Foto Profil berhasil diperbarui.');
        } else {
            if ($approvalNotice) {
                return redirect()->route('my-profile')->with('success', 'Foto profil & data diri berhasil diperbarui. Perubahan data sensitif (NIK/Nama/Email) telah diajukan ke HR Administrator untuk persetujuan.');
            }
            return redirect()->back()->with('success', 'Profil & Foto Profil berhasil diperbarui.');
        }
    }

    // Remove the specified employee from storage
    public function destroy(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);
        $this->authorize('delete', $employee);

        // When an employee is deleted, also delete their user account
        $user = User::where('employee_id', $employee->id)->first();
        if ($user) {
            $user->delete();
        }

        $employee->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Employee deleted successfully.',
            ]);
        }

        return redirect()->route('employees.index')->with('success', 'Employee deleted successfully.');
    }

    /**
     * Determine mutation type based on role/salary changes
     */
    private function determineMutationType($old, $new)
    {
        if ((float) $new->salary > (float) $old['salary']) {
            return 'promotion';
        }
        if ((float) $new->salary < (float) $old['salary']) {
            return 'demotion';
        }
        if ((int) $old['role_id'] !== (int) $new->role_id) {
            return 'mutation';
        }
        return 'adjustment';
    }

    /**
     * Reset the user's browser device fingerprints.
     */
    public function resetDevice(Request $request, Employee $employee)
    {
        // Must be an admin to perform this action
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $user = $employee->user;

        if (!$user) {
            return redirect()->back()->with('error', 'Karyawan ini belum memiliki akun login.');
        }

        $user->update([
            'browser_fingerprint_desktop' => null,
            'browser_fingerprint_mobile' => null,
        ]);

        return redirect()
            ->back()
            ->with(
                'success',
                'Perangkat berhasil di-reset. Karyawan dapat meregistrasikan ulang browser mereka saat absen berikutnya.',
            );
    }
}
