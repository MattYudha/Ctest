<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Employee;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TaskController extends Controller
{
    /**
     * Display a listing of the tasks.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $role = session('role');
            $employeeId = session('employee_id');
            $employee = $employeeId ? Employee::find($employeeId) : null;

            $query = Task::with('employee');

            // Access rules:
            // - Master Admin       : can see all tasks
            // - HR Administrator               : see tasks in their department (or filtered department_id)
            // - Manager / Unit Head          : see tasks of their direct subordinates (+ own tasks)
            // - Master Admin/Staff  : see only their own tasks
            if ($role === \App\Constants\Roles::MASTER_ADMIN) {
                // no additional restriction
            } elseif ($role === 'HR Administrator') {
                // HR Administrator: by department. If department_id is provided, use it; otherwise use HR Administrator's own department.
                $departmentId = $request->input('department_id') ?: ($employee?->department_id);

                if ($departmentId) {
                    $query->whereHas('employee', function ($q) use ($departmentId) {
                        $q->where('department_id', $departmentId);
                    });
                } else {
                    // If for some reason employee/department not found, fall back to no tasks
                    $query->whereRaw('1 = 0');
                }
            } elseif ($role === 'Manager / Unit Head' && $employee) {
                // Manager / Unit Head: tasks of direct subordinates + own tasks
                $query->where(function ($q) use ($employeeId) {
                    $q->where('assigned_to', $employeeId)
                      ->orWhereIn('assigned_to', function ($sub) use ($employeeId) {
                          $sub->select('id')
                              ->from('employees')
                              ->where('supervisor_id', $employeeId);
                      });
                });
            } else {
                // Master Admin, Employee, and any other roles:
                // only see their own tasks
                if ($employeeId) {
                    $query->where('assigned_to', $employeeId);
                } else {
                    // No employee context -> show nothing
                    $query->whereRaw('1 = 0');
                }
            }

            if ($request->filled('month')) {
                // $request->month usually comes as "YYYY-MM" from <input type="month">
                $monthStr = $request->month;
                try {
                    $startOfMonth = \Carbon\Carbon::createFromFormat('Y-m', $monthStr)->startOfMonth();
                    $endOfMonth = \Carbon\Carbon::createFromFormat('Y-m', $monthStr)->endOfMonth();
                    
                    $query->whereBetween('due_date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')]);
                } catch (\Exception $e) {
                    // Ignore invalid format
                }
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $btns = '<div class="btn-group btn-group-sm" role="group">';
                    $btns .= '<a href="'.route('tasks.show', $row->id).'" class="btn btn-outline-info"><i class="bi bi-eye"></i></a>';
                    
                    if ($row->status === 'pending') {
                        $btns .= '<a href="'.route('tasks.done', $row->id).'" class="btn btn-outline-success"><i class="bi bi-check-circle"></i></a>';
                    } else {
                        $btns .= '<a href="'.route('tasks.pending', $row->id).'" class="btn btn-outline-warning"><i class="bi bi-arrow-counterclockwise"></i></a>';
                    }
                    
                    $userRole = session('role');
                    if (\App\Constants\Roles::isAdmin($userRole) || $userRole === \App\Constants\Roles::MANAGER_UNIT_HEAD) {
                        $btns .= '<a href="'.route('tasks.edit', $row->id).'" class="btn btn-outline-primary"><i class="bi bi-pencil"></i></a>';
                        $csrf = csrf_token();
                        $btns .= '
                            <form action="'.route('tasks.destroy', $row->id).'" method="POST" class="d-inline">
                                <input type="hidden" name="_token" value="'.$csrf.'">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn btn-outline-danger" onclick="return confirm(\'Delete this task?\')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        ';
                    }
                    
                    $btns .= '</div>';
                    return $btns;
                })
                ->addColumn('status_badge', function($row){
                    $class = match($row->status) {
                        'pending' => 'bg-warning',
                        'on progress' => 'bg-info',
                        'done' => 'bg-success',
                        default => 'bg-secondary'
                    };
                    return '<span class="badge '.$class.'">'.ucfirst($row->status).'</span>';
                })
                ->editColumn('due_date', function($row){
                    return \Carbon\Carbon::parse($row->due_date)->format('d M Y');
                })
                ->rawColumns(['action', 'status_badge'])
                ->make(true);
        }

        return view('tasks.index');
    }

    /**
     * Show the form for creating a new task.
     */
    public function create()
    {
        $employees = $this->getAssignableEmployees();
        return view('tasks.create', compact('employees'));
    }

    /**
     * Store a newly created task in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'required|exists:employees,id',
            'due_date'    => 'required|date',
            'status'      => 'required|in:pending,on progress,done',
        ]);

        // Attach the creator from session
        $validated['created_by'] = session('employee_id');

        Task::create($validated);

        return redirect()->route('tasks.index')->with('success', 'Task created successfully.');
    }

    /**
     * Display the specified task.
     */
    public function show(Task $task)
    {
        $task->load(['employee', 'comments.employee']);
        return view('tasks.show', compact('task'));
    }

    /**
     * Show the form for editing the specified task.
     */
    public function edit(Task $task)
    {
        $employees = $this->getAssignableEmployees();
        return view('tasks.edit', compact('task', 'employees'));
    }

    /**
     * Update the specified task in storage.
     */
    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'required|exists:employees,id',
            'due_date' => 'required|date',
            'status' => 'required|string',
        ]);

        // Auto-set completed_at when status changes to completed/done
        if (in_array($validated['status'], ['completed', 'done']) && !$task->completed_at) {
            $validated['completed_at'] = now();
        }
        // Reset completed_at if status changes away from completed
        elseif (!in_array($validated['status'], ['completed', 'done']) && $task->completed_at) {
            $validated['completed_at'] = null;
        }

        $task->update($validated);

        return redirect()->route('tasks.index')->with('success', 'Task updated successfully.');
    }

    /**
     * Update task status.
     */
    public function done(int $id)
    {
        try {
            $task = Task::findOrFail($id);
            $task->update([
                'status'       => 'done',
                'completed_at' => now()
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, 'Task not found.');
        }

        return redirect()->route('tasks.index')->with('success', 'Task marked as done.');
    }

    /**
     * Update task status.
     */
    public function pending(int $id)
    {
        try {
            $task = Task::findOrFail($id);
            $task->update([
                'status'       => 'pending',
                'completed_at' => null
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, 'Task not found.');
        }

        return redirect()->route('tasks.index')->with('success', 'Task marked as pending.');
    }

    /**
     * Remove the specified task from storage.
     */
    public function destroy(Task $task)
    {
        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully.');
    }

    /**
     * Get employees that the current user can assign tasks to.
     */
    private function getAssignableEmployees()
    {
        $role = session('role');
        $employeeId = session('employee_id');
        $employee = Employee::find($employeeId);

        $query = Employee::query()->where('status', 'active');

        // HR Administrator and Master Admin can assign to everyone
        if (\App\Constants\Roles::isAdmin($role)) {
            return $query->orderBy('fullname')->get();
        }

        // Manager / Unit Heads can assign to everyone in their department
        if ($role === 'Manager / Unit Head' && $employee) {
            return $query->where('department_id', $employee->department_id)
                ->orderBy('fullname')
                ->get();
        }

        // Others (Supervisors) can only assign to their direct reports
        return $query->where('supervisor_id', $employeeId)
            ->orderBy('fullname')
            ->get();
    }
}