<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Constants\Roles;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskCommentController;
use App\Http\Controllers\PayrollsController;
use App\Http\Controllers\PresencesController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\InventoryCategoryController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InventoryUsageLogController;
use App\Http\Controllers\InventoryRequestController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\ProcurementController;
use App\Http\Controllers\InventoryDispatchController;
use App\Http\Controllers\LogisticsShipmentController;
use App\Http\Controllers\LetterController;
use App\Http\Controllers\LetterTemplateController;
use App\Http\Controllers\LetterConfigurationController;
use App\Http\Controllers\LetterArchiveController;
use App\Http\Controllers\SignatureController;
use App\Http\Controllers\KPIController;
use App\Http\Controllers\KPIMasterController;
use App\Http\Controllers\ReportingController;
use App\Http\Controllers\MyProfileController;
use App\Http\Controllers\KnowledgeBaseController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\OfficeLocationController;
use App\Http\Controllers\WorkLogController;
use App\Http\Controllers\MasterPresenceController;
use App\Http\Controllers\OvertimeController;
use App\Http\Controllers\LetterTagController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\HolidayController;

use App\Http\Controllers\SystemRecoveryController;

Route::get('/system/sync-master', [SystemRecoveryController::class, 'sync']);

Route::get('/', function () {
    // redirect logged in user to dashboard
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

// redirect guest user to login page
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    // TinyMCE Image Upload
    Route::post('/upload-image', [\App\Http\Controllers\ImageUploadController::class, 'upload'])->name('upload.image');
    // Profile routes
    Route::get('/my-profile', [MyProfileController::class, 'index'])->name('my-profile');
    Route::get('/my-profile/edit', [MyProfileController::class, 'edit'])->name('my-profile.edit');
    Route::put('/my-profile/update', [MyProfileController::class, 'update'])->name('my-profile.update');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Knowledge Base
    Route::get('knowledge-base/create', [KnowledgeBaseController::class, 'create'])
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN])
        ->name('knowledge-base.create');
    Route::post('knowledge-base', [KnowledgeBaseController::class, 'store'])
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN])
        ->name('knowledge-base.store');
    Route::get('knowledge-base/{knowledge_base}/edit', [KnowledgeBaseController::class, 'edit'])
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN])
        ->name('knowledge-base.edit');
    Route::put('knowledge-base/{knowledge_base}', [KnowledgeBaseController::class, 'update'])
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN])
        ->name('knowledge-base.update');
    Route::delete('knowledge-base/{knowledge_base}', [KnowledgeBaseController::class, 'destroy'])
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN])
        ->name('knowledge-base.destroy');
    Route::get('knowledge-base', [KnowledgeBaseController::class, 'index'])->name('knowledge-base.index');
    Route::get('knowledge-base/{knowledge_base}', [KnowledgeBaseController::class, 'show'])->name(
        'knowledge-base.show',
    );

    // Dashboard chart, buatan sendiri
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/presence', [DashboardController::class, 'presence']);



    // Resource routes for departments
    Route::get('departments/org-chart', [DepartmentController::class, 'orgChart'])->name('departments.org-chart');
    Route::resource('departments', DepartmentController::class)->middleware([
        'role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN,
    ]);

    // Resource routes for office locations
    Route::resource('office-locations', OfficeLocationController::class)
        ->except(['show'])
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN]);

    // Resource routes for roles
    Route::resource('roles', RoleController::class)->middleware([
        'role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN,
    ]);

    // Resource routes for employees
    Route::post('employees/{employee}/reset-device', [EmployeeController::class, 'resetDevice'])
        ->name('employees.reset-device')
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN]);

    Route::get('employees', [EmployeeController::class, 'index'])
        ->name('employees.index')
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN]);

    Route::get('employees/create', [EmployeeController::class, 'create'])
        ->name('employees.create')
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN]);

    Route::post('employees', [EmployeeController::class, 'store'])
        ->name('employees.store')
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN]);

    Route::delete('employees/{employee}', [EmployeeController::class, 'destroy'])
        ->name('employees.destroy')
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN]);

    Route::get('employees/{employee}', [EmployeeController::class, 'show'])
        ->name('employees.show');

    Route::get('employees/{employee}/edit', [EmployeeController::class, 'edit'])
        ->name('employees.edit');

    Route::put('employees/{employee}', [EmployeeController::class, 'update'])
        ->name('employees.update');
    Route::post('employees/{employee}/documents', [DocumentController::class, 'store'])
        ->name('employees.documents.store')
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN]);
    Route::delete('documents/{document}', [DocumentController::class, 'destroy'])
        ->name('employees.documents.destroy')
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN]);

    // Employee update approvals
    Route::get('employee-approvals', [App\Http\Controllers\EmployeeUpdateApprovalController::class, 'index'])
        ->name('employee-approvals.index')
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN]);
    Route::get('employee-approvals/{id}', [App\Http\Controllers\EmployeeUpdateApprovalController::class, 'show'])
        ->name('employee-approvals.show')
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN]);
    Route::post('employee-approvals/{id}/approve', [
        App\Http\Controllers\EmployeeUpdateApprovalController::class,
        'approve',
    ])
        ->name('employee-approvals.approve')
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN]);
    Route::post('employee-approvals/{id}/reject', [
        App\Http\Controllers\EmployeeUpdateApprovalController::class,
        'reject',
    ])
        ->name('employee-approvals.reject')
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN]);

    // Resource routes for tasks
    Route::resource('tasks', TaskController::class);
    Route::get('tasks/done/{id}', [TaskController::class, 'done'])->name('tasks.done');
    Route::get('tasks/pending/{id}', [TaskController::class, 'pending'])->name('tasks.pending');
    // Routes for task comments
    Route::post('tasks/{task}/comments', [TaskCommentController::class, 'store'])->name('tasks.comments.store');
    Route::get('tasks/comments/{comment}/evidence', [TaskCommentController::class, 'evidence'])->name(
        'tasks.comments.evidence',
    );
    Route::delete('tasks/comments/{comment}', [TaskCommentController::class, 'destroy'])->name(
        'tasks.comments.destroy',
    );

    // Resource routes for payroll
    Route::get('payrolls', [PayrollsController::class, 'index'])
        ->name('payrolls.index')
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN . ',' . Roles::FINANCE]);
    Route::post('payrolls', [PayrollsController::class, 'store'])
        ->name('payrolls.store')
        ->middleware([
            'role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN . ',' . Roles::FINANCE,
            'throttle:300,1',
        ]);

    Route::get('payrolls/create', [PayrollsController::class, 'create'])
        ->name('payrolls.create')
        ->middleware([
            'role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN . ',' . Roles::FINANCE,
            'throttle:300,1',
        ]);
    Route::get('payrolls/attendance-data', [PayrollsController::class, 'getAttendanceData'])
        ->name('payrolls.attendance-data')
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN . ',' . Roles::FINANCE]);
    Route::get('payrolls/employee-data', [PayrollsController::class, 'getEmployeeData'])
        ->name('payrolls.employee-data')
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN . ',' . Roles::FINANCE]);

    Route::get('payrolls/{payroll}', [PayrollsController::class, 'show'])
        ->name('payrolls.show')
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN . ',' . Roles::FINANCE]);
    Route::get('payrolls/{payroll}/edit', [PayrollsController::class, 'edit'])
        ->name('payrolls.edit')
        ->middleware([
            'role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN . ',' . Roles::FINANCE,
            'throttle:300,1',
        ]);
    Route::put('payrolls/{payroll}', [PayrollsController::class, 'update'])
        ->name('payrolls.update')
        ->middleware([
            'role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN . ',' . Roles::FINANCE,
            'throttle:300,1',
        ]);
    Route::patch('payrolls/{payroll}', [PayrollsController::class, 'update'])->middleware([
        'role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN . ',' . Roles::FINANCE,
        'throttle:300,1',
    ]);
    Route::delete('payrolls/{payroll}', [PayrollsController::class, 'destroy'])
        ->name('payrolls.destroy')
        ->middleware([
            'role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN . ',' . Roles::FINANCE,
            'throttle:300,1',
        ]);

    Route::get('/payrolls/{id}/print', [PayrollsController::class, 'print'])
        ->name('payrolls.print')
        ->middleware([
            'role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN . ',' . Roles::FINANCE,
            'throttle:300,1',
        ]);
    Route::post('payrolls/{id}/update-status', [PayrollsController::class, 'updateStatus'])
        ->name('payrolls.update-status')
        ->middleware([
            'role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN . ',' . Roles::FINANCE,
            'throttle:300,1',
        ]);
    Route::get('payrolls/{id}/slip', [PayrollsController::class, 'showSlip'])
        ->name('payrolls.slip')
        ->middleware([
            'role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN . ',' . Roles::FINANCE,
            'throttle:300,1',
        ]);
    Route::post('payrolls/update-setting', [PayrollsController::class, 'updateSetting'])
        ->name('payrolls.update-setting')
        ->middleware([
            'role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN . ',' . Roles::FINANCE,
            'throttle:300,1',
        ]);
    Route::post('payrolls/export-csv', [PayrollsController::class, 'exportCsv'])
        ->name('payrolls.export-csv')
        ->middleware([
            'role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN . ',' . Roles::FINANCE,
            'throttle:300,1',
        ]);
    Route::post('/payrolls/export-data-csv', [PayrollsController::class, 'exportDataCsv'])
        ->name('payrolls.export-data-csv')
        ->middleware([
            'role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN . ',' . Roles::FINANCE,
            'throttle:300,1',
        ]);

    // Additional presence routes (must be defined before resource route to avoid conflicts)
    Route::get('presences/checkout', [PresencesController::class, 'checkout'])->name('presences.checkout');
    Route::post('presences/checkout', [PresencesController::class, 'processCheckout'])
        ->name('presences.checkout.process')
        ->middleware(['throttle:10,1']);
    Route::get('presences/calendar', [PresencesController::class, 'calendar'])->name('presences.calendar');
    Route::get('presences/export', [PresencesController::class, 'export'])
        ->name('presences.export')
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN]);

    // Presence routes (accessible to all)
    Route::get('presences', [PresencesController::class, 'index'])->name('presences.index');
    Route::get('presences/{presence}/show', [PresencesController::class, 'show'])->name('presences.show');
    Route::get('presences/create', [PresencesController::class, 'create'])->name('presences.create');
    Route::post('presences', [PresencesController::class, 'store'])
        ->name('presences.store')
        ->middleware(['throttle:60,1']);

    // Resource routes for presences management (restricted)
    Route::resource('presences', PresencesController::class)
        ->only(['edit', 'update', 'destroy'])
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN, 'throttle:10,1']);

    // Resource routes for leave requests
    Route::resource('leave-requests', LeaveRequestController::class);

    Route::get('leave-requests/confirm/{id}', [LeaveRequestController::class, 'confirm'])
        ->name('leave-requests.confirm')
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN . ',Manager / Unit Head']);
    Route::get('leave-requests/reject/{id}', [LeaveRequestController::class, 'reject'])
        ->name('leave-requests.reject')
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN . ',Manager / Unit Head']);

    // Resource routes for inventory categories
    Route::resource('inventory-categories', InventoryCategoryController::class)->middleware([
        'role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN . ',inventory,' . Roles::FINANCE,
    ]);

    // Resource routes for inventories
    Route::resource('inventories', InventoryController::class)->middleware([
        'role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN . ',inventory,' . Roles::FINANCE,
    ]);

    // Resource routes for inventory usage logs
    Route::resource('inventory-usage-logs', InventoryUsageLogController::class)->middleware([
        'role:' .
        Roles::HR_ADMINISTRATOR .
        ',' .
        Roles::MASTER_ADMIN .
        ',Manager / Unit Head,inventory_logs,' .
        Roles::FINANCE,
    ]);

    // Resource routes for inventory requests
    Route::resource('inventory-requests', InventoryRequestController::class);
    Route::get('inventory-requests/approve/{id}', [InventoryRequestController::class, 'approve'])
        ->name('inventory-requests.approve')
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN . ',inventory,' . Roles::FINANCE]);
    Route::get('inventory-requests/reject/{id}', [InventoryRequestController::class, 'reject'])
        ->name('inventory-requests.reject')
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN . ',inventory,' . Roles::FINANCE]);

    // Vendor Management
    Route::resource('vendors', VendorController::class)->middleware([
        'role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN,
    ]);

    // Procurement Workflow
    Route::resource('procurements', ProcurementController::class)->middleware([
        'role:' .
        Roles::HR_ADMINISTRATOR .
        ',' .
        Roles::MASTER_ADMIN .
        ',Manager / Unit Head,inventory,' .
        Roles::FINANCE,
    ]);
    Route::get('procurements/order/{id}', [ProcurementController::class, 'markAsOrdered'])
        ->name('procurements.order')
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN . ',inventory,' . Roles::FINANCE]);
    Route::get('procurements/receive/{id}', [ProcurementController::class, 'receive'])
        ->name('procurements.receive')
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN . ',inventory,' . Roles::FINANCE]);

    // Inventory Dispatches (Releases) with Barcode
    Route::resource('inventory-dispatches', InventoryDispatchController::class)->middleware([
        'role:' .
        Roles::HR_ADMINISTRATOR .
        ',' .
        Roles::MASTER_ADMIN .
        ',Manager / Unit Head,inventory,' .
        Roles::FINANCE,
    ]);

    // Logistics Tracking
    Route::resource('logistics-shipments', LogisticsShipmentController::class)
        ->only(['index', 'edit', 'update'])
        ->middleware([
            'role:' .
            Roles::HR_ADMINISTRATOR .
            ',' .
            Roles::MASTER_ADMIN .
            ',Manager / Unit Head,inventory,' .
            Roles::FINANCE,
        ]);

    // Resource routes for letters - all authenticated users can create/submit
    Route::resource('letters', LetterController::class)->middleware(['auth']);
    Route::post('letters/{letter}/submit', [LetterController::class, 'submit'])
        ->name('letters.submit')
        ->middleware(['auth']);

    // Letter approval actions - only HR Administrator and Master Admin
    Route::post('letters/{letter}/approve', [LetterController::class, 'approve'])
        ->name('letters.approve')
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN]);
    Route::post('letters/{letter}/reject', [LetterController::class, 'reject'])
        ->name('letters.reject')
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN]);
    Route::post('letters/{letter}/print', [LetterController::class, 'print'])
        ->name('letters.print')
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN]);
    Route::get('letters/{letter}/export', [LetterController::class, 'export'])->name('letters.export');

    // Resource routes for letter templates
    Route::resource('letter-templates', LetterTemplateController::class)->middleware([
        'role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN,
    ]);

    // Resource routes for letter configuration
    Route::get('letter-configurations', [LetterConfigurationController::class, 'index'])
        ->name('letter-configurations.index')
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN]);
    Route::post('letter-configurations', [LetterConfigurationController::class, 'update'])
        ->name('letter-configurations.update')
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN]);

    // Resource routes for letter archives
    Route::resource('letter-archives', LetterArchiveController::class)
        ->only(['index', 'show'])
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN]);

    // Digital Signature routes
    Route::get('signatures/{signable}/{id}/pad', [SignatureController::class, 'pad'])->name('signatures.pad');
    Route::post('signatures/{signable}/{id}', [SignatureController::class, 'store'])->name('signatures.store');
    Route::post('signatures/{signable}/{id}/internal', [SignatureController::class, 'storeInternal'])->name(
        'signatures.store_internal',
    );
    Route::get('signatures/{signable}/{id}/list', [SignatureController::class, 'list'])->name('signatures.list');
    Route::get('signature-logs', [SignatureController::class, 'logs'])
        ->name('signatures.logs')
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN]);
    Route::post('signatures/{signature}/verify', [SignatureController::class, 'verify'])
        ->name('signatures.verify')
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN]);
    Route::get('signatures/{signature}/download', [SignatureController::class, 'download'])->name(
        'signatures.download',
    );
    Route::get('signatures/{signature}/validate', [SignatureController::class, 'validate'])->name(
        'signatures.validate',
    );
    Route::delete('/signatures/destroy/{id}', [SignatureController::class, 'destroy'])->name('signatures.destroy');
    Route::get('/signatures/my-requests', [SignatureController::class, 'mySignatures'])->name('signatures.my');

    // KPI and Reporting routes
    // Route::get('kpi/team', [KPIController::class, 'team'])
    //     ->name('kpi.team')
    //     ->middleware(['role:Manager / Unit Head,' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN]);
    // Route::get('kpi/department', [KPIController::class, 'department'])
    //     ->name('kpi.department')
    //     ->middleware(['role:Manager / Unit Head,' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN]);
    Route::get('kpi/dashboard', [KPIController::class, 'dashboard'])->name('kpi.dashboard');
    Route::post('kpi/store', [KPIController::class, 'store'])
        ->name('kpi.store')
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN]);
    Route::get('kpi/employee/{id}', [KPIController::class, 'show'])->name('kpi.show');
    Route::post('kpi/employee/{id}/sync', [KPIController::class, 'syncEmployeeMetrics'])->name('kpi.sync-employee');
    Route::get('kpi/trend/{id}', [KPIController::class, 'trend'])->name('kpi.trend');
    Route::get('kpi/team', [KPIController::class, 'team'])
        ->name('kpi.team')
        ->middleware(['role:Manager / Unit Head,' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN]);
    Route::post('kpi/team/assign', [KPIController::class, 'assignTeam'])
        ->name('kpi.team.assign')
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN]);
    Route::post('kpi/team/delete/{supervisorId}', [KPIController::class, 'deleteTeam'])
        ->name('kpi.team.delete')
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN]);
    Route::get('kpi/department', [KPIController::class, 'department'])
        ->name('kpi.department')
        ->middleware(['role:Manager / Unit Head,' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN]);
    Route::post('kpi/department/assign', [KPIController::class, 'assignDepartment'])
        ->name('kpi.department.assign')
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN]);
    Route::post('kpi/department/delete/{departmentId}', [KPIController::class, 'deleteDepartment'])
        ->name('kpi.department.delete')
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN]);
    Route::get('kpi/company', [KPIController::class, 'companyDashboard'])
        ->name('kpi.company');
    Route::post('kpi/recalculate/{id}', [KPIController::class, 'recalculate'])
        ->name('kpi.recalculate')
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN, 'throttle:300,1']);

    // KPI Submission and Approval Workflow
    Route::post('kpi/submit/{id}', [KPIController::class, 'submit'])->name('kpi.submit');
    Route::post('kpi/record/{id}', [KPIController::class, 'updateRecord'])->name('kpi.update-record');
    Route::get('kpi/pending', [KPIController::class, 'pendingApprovals'])
        ->name('kpi.pending')
        ->middleware(['role:Manager / Unit Head,' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN]);
    Route::post('kpi/approve/{id}', [KPIController::class, 'approve'])
        ->name('kpi.approve')
        ->middleware(['role:Manager / Unit Head,' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN]);
    Route::post('kpi/reject/{id}', [KPIController::class, 'reject'])
        ->name('kpi.reject')
        ->middleware(['role:Manager / Unit Head,' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN]);
    Route::delete('kpi/record/{id}', [KPIController::class, 'destroy'])->name('kpi.destroy-record');

    // HR Dispensation & Notes
    Route::post('kpi/records/{record}/hr-note', [KPIController::class, 'storeHRNote'])
        ->name('kpi.hr-note')
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN]);

    // Master KPI Management
    Route::get('kpi/{employee}/records/{record}/evaluate', [KPIController::class, 'evaluateQuality'])
        ->name('kpi.evaluate-quality')
        ->middleware(['role:Manager / Unit Head,' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN]);
    Route::post('kpi/{employee}/records/{record}/evaluate', [KPIController::class, 'storeQualityEvaluation'])
        ->name('kpi.store-evaluate-quality')
        ->middleware(['role:Manager / Unit Head,' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN]);

    Route::post('kpi-masters/reset', [KPIMasterController::class, 'reset'])->name('kpi-masters.reset');
    Route::post('kpi-masters/publish', [KPIMasterController::class, 'publish'])->name('kpi-masters.publish');
    Route::resource('kpi-masters', KPIMasterController::class)
        ->except(['show'])
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN]);

    Route::get('reports/monthly-recap', [ReportingController::class, 'monthlyRecap'])->name('reports.monthly-recap');
    Route::post('reports/monthly-recap/sync', [ReportingController::class, 'syncMetrics'])->name('reports.monthly-recap.sync');
    Route::get('reports/executive', [ReportingController::class, 'executiveDashboard'])
        ->name('reports.executive')
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN]);
    Route::post('reports/generate-kpi', [ReportingController::class, 'generateMonthlyKPI'])
        ->name('reports.generate-kpi')
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN]);
    Route::get('reports/{id}/export-pdf', [ReportingController::class, 'exportPDF'])->name('reports.export-pdf');
    Route::get('reports/{id}/export-trend-pdf', [ReportingController::class, 'exportTrendPDF'])->name('reports.export-trend-pdf');
    Route::get('reports/export-csv', [ReportingController::class, 'exportCSV'])
        ->name('reports.export-csv')
        ->middleware(['role:Manager / Unit Head,' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN]);

    // Audit Trail - Master Admin only
    Route::get('audit-trail', [AuditController::class, 'index'])
        ->name('audit.index')
        ->middleware(['role:' . Roles::MASTER_ADMIN]);
    Route::post('audit-trail/toggle', [AuditController::class, 'toggleEnabled'])
        ->name('audit.toggle')
        ->middleware(['role:' . Roles::MASTER_ADMIN]);
    Route::delete('audit-trail/purge', [AuditController::class, 'purge'])
        ->name('audit.purge')
        ->middleware(['role:' . Roles::MASTER_ADMIN]);

    // System Management - Master Admin only
    Route::get('system', [SystemController::class, 'index'])
        ->name('system.index')
        ->middleware(['role:' . Roles::MASTER_ADMIN]);
    Route::post('system/backup', [SystemController::class, 'backup'])
        ->name('system.backup')
        ->middleware(['role:' . Roles::MASTER_ADMIN]);

    // Resource routes for incidents
    Route::resource('incidents', IncidentController::class)->only([
        'index',
        'show',
        'create',
        'store',
        'edit',
        'update',
    ]);
    Route::resource('incidents', IncidentController::class)
        ->only(['destroy'])
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN]);

    Route::resource('work-logs', WorkLogController::class);

    Route::get('/master-presences', [MasterPresenceController::class, 'index'])
        ->name('master-presences.index')
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN]);

    Route::post('/master-presences', [MasterPresenceController::class, 'update'])
        ->name('master-presences.index.update')
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN]);

    Route::get('/master-presences/create', [MasterPresenceController::class, 'createPresence'])
        ->name('master-presences.create-presence')
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN]);

    Route::post('/master-presences/store', [MasterPresenceController::class, 'storePresence'])
        ->name('master-presences.store-presence')
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN]);

    // Overtimes Management
    Route::post('overtimes/settings', [OvertimeController::class, 'updateSettings'])
        ->name('overtimes.settings')
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN]);

    Route::post('overtimes/approve-batch', [OvertimeController::class, 'approveBatch'])
        ->name('overtimes.approve-batch')
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN]);

    Route::post('overtimes/reject-batch', [OvertimeController::class, 'rejectBatch'])
        ->name('overtimes.reject-batch')
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN]);

    Route::resource('overtimes', OvertimeController::class);

    Route::resource('letter-tags', LetterTagController::class)->except(['show']);

    Route::post('/document/{signable}/{id}/generate-public-link', [SignatureController::class, 'generatePublicLink'])
        ->name('signatures.generate_public_link')
        ->middleware('auth');

    Route::resource('positions', PositionController::class)->middleware([
        'auth',
        'role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN,
    ]);

    Route::resource('holidays', HolidayController::class)
        ->only(['index', 'store', 'destroy'])
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN]);

    Route::post('/holidays/sync', [HolidayController::class, 'sync'])
        ->name('holidays.sync')
        ->middleware(['role:' . Roles::HR_ADMINISTRATOR . ',' . Roles::MASTER_ADMIN]);
});
Route::get('/document/sign/success', [SignatureController::class, 'publicSuccess'])->name('signatures.public.success');
Route::get('/document/sign/{token}', [SignatureController::class, 'publicPad'])->name('signatures.public.pad');
Route::post('/document/sign/{token}', [SignatureController::class, 'publicSubmit'])->name('signatures.public.submit');
Route::post('/signatures/public/{token}/verify-otp', [SignatureController::class, 'verifyPublicOtp'])->name(
    'signatures.public.verify-otp',
);

// Bawaan Breeze.
// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__ . '/auth.php';

Route::get('/seed-kpi', function () {
    \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'MockKPISeeder', '--force' => true]);
    return 'Seeded!';
});
require __DIR__ . '/web_finance.php';
require __DIR__ . '/web_crm.php';
