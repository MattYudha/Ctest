<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>COREVO Aratech</title>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Expires" content="0" />

    <link rel="shortcut icon" href="{{ asset('corevo-logo.png') }}" type="image/png" />

    <link rel="stylesheet" href="{{ asset('mazer/assets/compiled/css/app.css') }}?v={{ time() }}" />
    <link rel="stylesheet" href="{{ asset('mazer/assets/compiled/css/app-dark.css') }}?v={{ time() }}" />
    <link rel="stylesheet" href="{{ asset('css/custom-layout-dashboard.css') }}?v={{ time() }}" />

    <link rel="stylesheet" href="{{ asset('vendor/datatables/dataTables.bootstrap5.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendor/datatables/responsive.bootstrap5.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendor/flatpickr/flatpickr.min.css') }}" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" />

    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.setAttribute('data-bs-theme', 'dark');
        }
    </script>

    <script>
        if (localStorage.getItem('desktopSidebarState') === 'collapsed' && window.innerWidth >= 1200) {
            document.body.classList.add('sidebar-collapsed');
        }
    </script>

    @stack ('styles')
</head>

<body>
    <script src="{{ asset('mazer/assets/static/js/initTheme.js') }}"></script>

    <div id="app">
        <div id="sidebar">
            <div class="sidebar-wrapper active">
                <div class="sidebar-header position-relative px-4 pt-4 pb-2">
                    <div class="row align-items-center mx-0">
                        <div class="col-12 d-flex justify-content-between align-items-center px-0 mb-4">
                            <div class="logo-container w-100 text-xl-center text-start">
                                <a href="{{ url('/dashboard') }}" class="d-inline-block text-center">
                                    <img
                                        src="{{ asset('corevo-logo.png') }}"
                                        class="logo-light custom-logo-size"
                                        alt="Logo"
                                    />
                                    <img
                                        src="{{ asset('corevo-logo-white.png') }}"
                                        class="logo-dark custom-logo-size"
                                        alt="Logo"
                                    />
                                </a>
                            </div>

                            <div class="toggler d-xl-none ms-2">
                                <a
                                    class="mb-3 sidebar-hide d-flex align-items-center justify-content-center text-secondary text-decoration-none"
                                    style="width: 32px; height: 32px"
                                >
                                    <i class="bi bi-x-lg fs-5"></i>
                                </a>
                            </div>
                        </div>

                        <div class="col-12 px-0">
                            <div class="theme-toggle w-100">
                                <label
                                    class="theme-toggle-wrapper w-100 mb-0 d-flex justify-content-between align-items-center"
                                    for="toggle-dark"
                                >
                                    <span class="theme-label-text"></span>

                                    <div class="custom-theme-switch mb-0">
                                        <input type="checkbox" id="toggle-dark" style="display: none" />
                                        <div class="switch-slider">
                                            <div class="switch-circle">
                                                <i class="bi bi-sun-fill text-warning icon-sun"></i>
                                                <i class="bi bi-moon-fill text-warning icon-moon"></i>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="sidebar-menu">
                    <ul class="menu" style="margin-top: 0px !important">
                        @php
                            $user = Auth::user();
                            $role = session('role');
                            $isMasterAdmin = $role === \App\Constants\Roles::MASTER_ADMIN;
                            $isAdmin = in_array($role, \App\Constants\Roles::ADMIN_ROLES);
                            $isManager = $role === \App\Constants\Roles::MANAGER_UNIT_HEAD;
                            $isSupervisor = $role === \App\Constants\Roles::SUPERVISOR;
                            $isEmployee = $role === \App\Constants\Roles::EMPLOYEE;
                            $isMarketing = $role === \App\Constants\Roles::MARKETING;
                            $isFinanceRole = $role === \App\Constants\Roles::FINANCE;
                            $isSales = $role === \App\Constants\Roles::SALES;

                            $isDevOrAdmin = $isAdmin || $isMasterAdmin;
                            $isManagerOrAdmin = $isAdmin || $isMasterAdmin || $isManager;
                            $isMarketingOrAdmin = $isAdmin || $isMasterAdmin || $isMarketing;
                            $isStaff = $isManagerOrAdmin || $isSupervisor || $isEmployee || $isMarketing;

                            $activeDashboard = request()->is('dashboard');
                            $activePayroll = request()->is('payrolls*');
                            $activeEmployees = request()->is('employees*');
                            $activePositions = request()->is('positions*');
                            $activeEmployeeApprovals = request()->is('employee-approvals*');
                            $activeDepartments = request()->is('departments*');
                            $activeOfficeLocations = request()->is('office-locations*');
                            $activeRoles = request()->is('roles*');
                            $activeTasks = request()->is('tasks*');
                            $activeLeaveRequests = request()->is('leave-requests*');
                            $activeIncidents = request()->is('incidents*');
                            $activeHolidaysManagement = request()->is('holidays*');

                            $activeCrmDashboard = request()->is('crm/dashboard*');
                            $activeCrmBoard = request()->is('crm/board*');
                            $activeCrmContacts = request()->is('crm/contacts*');
                            $activeCrmEmailBlasts = request()->is('crm/email-blasts*');
                            $activeCrmWaBlasts = request()->is('crm/wa-blasts*');
                            $activeCrmTemplates = request()->is('crm/templates*');

                            $activeKpiDashboard = request()->is('kpi/dashboard*') || request()->is('kpi-dashboard*');
                            $activeKpiTeam = request()->is('kpi/team*');
                            $activeKpiDepartment = request()->is('kpi/department*');
                            $activeKpiPending = request()->is('kpi/pending*') || request()->is('kpi/pending-approvals*');

                            $activeFinanceTransactions = request()->is('finance/transactions*');
                            $activeFinanceEntities = request()->is('finance/entities*');
                            $activeFinanceAccounts = request()->is('finance/accounts*');
                            $activeFinanceReports = request()->is('finance/reports*');
                            $activeFinanceCharts = request()->is('finance/charts*');
                            $activeFinanceClaims = request()->is('finance/claims*');
                            $activeMyFinance = request()->is('finance/my-finance*');

                            $activeInventoryCategories = request()->is('inventory-categories*');
                            $activeInventories = request()->is('inventories*');
                            $activeInventoryUsage = request()->is('inventory-usage-logs*');
                            $activeInventoryRequests = request()->is('inventory-requests*');
                            $activeVendors = request()->is('vendors*');
                            $activeProcurements = request()->is('procurements*');
                            $activeInventoryDispatches = request()->is('inventory-dispatches*');
                            $activeLogisticsShipments = request()->is('logistics-shipments*');

                            $activeLetters = request()->is('letters*');
                            $activeLetterTemplates = request()->is('letter-templates*');
                            $activeLetterConfigs = request()->is('letter-configurations*');
                            $activeLetterTags = request()->is('letter-tags*');
                            $activeLetterArchives = request()->is('letter-archives*');
                            $activeSignatureLogs = request()->is('signature-logs*');

                            $activeReportsExec = request()->is('reports/executive*');
                            $activeReportsMonthly = request()->is('reports/monthly-recap*');

                            $activeWorkLogs = request()->is('work-logs*');
                            $overtimes = request()->is('overtimes*');

                            $hasInventoryAccess = $user->hasAnyAccess(['inventory', 'inventory_logs', 'inventory_usage', 'inventory_requests']);
                            $hasKpiAccess = $user->hasAccess('hr_reports');
                            $hasAttendanceAccess = $user->hasAccess('attendance');

                            $isMasterAdmin = $user->isMasterAdmin();
                            $showPayrollGroup = $isMasterAdmin || $isAdmin || $isFinanceRole || $isManager || $hasKpiAccess;
                            $showInventoryLogs = $isMasterAdmin || $isAdmin || $isManager || $user->hasAccess('inventory_logs');
                            $showInventoryAdmin = $isMasterAdmin || $isAdmin || $isFinanceRole || $user->hasAccess('inventory');

                            $systemMenuActive = $activeRoles || request()->is('audit-trail*') || request()->is('system*');
                            $hrMenuActive =
                                $activeEmployees ||
                                $activeEmployeeApprovals ||
                                $activeDepartments ||
                                $activeOfficeLocations ||
                                $activeTasks ||
                                $activeLeaveRequests ||
                                $activeIncidents ||
                                $activePositions ||
                                $activeHolidaysManagement;
                            $crmMenuActive = $activeCrmDashboard || $activeCrmBoard || $activeCrmContacts || $activeCrmEmailBlasts || $activeCrmWaBlasts || $activeCrmTemplates;
                            $kpiMenuActive = $activeKpiDashboard || $activeKpiTeam || $activeKpiDepartment || $activeKpiPending;
                            $financeMenuActive =
                                $activeFinanceTransactions ||
                                $activeFinanceEntities ||
                                $activeFinanceAccounts ||
                                $activeFinanceReports ||
                                $activeFinanceCharts ||
                                $activeFinanceClaims;
                            $inventoryMenuActive =
                                $activeInventoryCategories ||
                                $activeInventories ||
                                $activeInventoryUsage ||
                                $activeInventoryRequests ||
                                $activeVendors ||
                                $activeProcurements ||
                                $activeInventoryDispatches ||
                                $activeLogisticsShipments;
                            $lettersMenuActive =
                                $activeLetters ||
                                $activeLetterTemplates ||
                                $activeLetterConfigs ||
                                $activeLetterArchives ||
                                $activeSignatureLogs ||
                                $activeLetterTags;
                            $reportsMenuActive = $activeReportsExec || $activeReportsMonthly;
                            $personalMenuActive =
                                request()->is('my-profile') ||
                                request()->is('presences') ||
                                request()->is('knowledge-base*') ||
                                $activeMyFinance ||
                                $activeFinanceClaims ||
                                $activeWorkLogs ||
                                $overtimes;
                        @endphp

                        <li class="sidebar-item {{ $activeDashboard ? 'active' : '' }}">
                            <a href="{{ url('/dashboard') }}" class="sidebar-link">
                                <i class="bi bi-grid-fill"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>

                        @if ($isMasterAdmin)
                            <li class="sidebar-item has-sub {{ $systemMenuActive ? 'active' : '' }}">
                                <a href="#" class="sidebar-link">
                                    <i class="bi bi-gear-fill"></i>
                                    <span>System Settings</span>
                                </a>
                                <ul class="submenu {{ $systemMenuActive ? 'active' : '' }}">
                                    <li class="submenu-item {{ $activeRoles ? 'active' : '' }}">
                                        <a href="{{ url('/roles') }}" class="submenu-link">Roles & Permissions</a>
                                    </li>
                                    <li class="submenu-item {{ request()->is('audit-trail*') ? 'active' : '' }}">
                                        <a href="{{ url('/audit-trail') }}" class="submenu-link">Audit Trail</a>
                                    </li>
                                    <li class="submenu-item {{ request()->is('system*') ? 'active' : '' }}">
                                        <a href="{{ url('/system') }}" class="submenu-link">System Management</a>
                                    </li>
                                </ul>
                            </li>
                        @endif

                        <li class="sidebar-item has-sub {{ $hrMenuActive ? 'active' : '' }}">
                            <a href="#" class="sidebar-link">
                                <i class="bi bi-person-vcard"></i>
                                <span>Human Resources</span>
                            </a>
                            <ul class="submenu {{ $hrMenuActive ? 'active' : '' }}">
                                @if ($isAdmin || $isManager)
                                    <li class="submenu-item {{ $activeEmployees ? 'active' : '' }}">
                                        <a href="{{ url('/employees') }}" class="submenu-link">Employees</a>
                                    </li>
                                    <li class="submenu-item {{ $activePositions ? 'active' : '' }}">
                                        <a href="{{ route('positions.index') }}" class="submenu-link">Positions</a>
                                    </li>
                                @endif
                                @if ($isAdmin || $isMasterAdmin)
                                    <li class="submenu-item {{ $activeEmployeeApprovals ? 'active' : '' }}">
                                        <a href="{{ url('/employee-approvals') }}" class="submenu-link"
                                            >Update Approvals</a
                                        >
                                    </li>
                                    <li class="submenu-item {{ $activeDepartments ? 'active' : '' }}">
                                        <a href="{{ url('/departments') }}" class="submenu-link">Departments</a>
                                    </li>
                                    <li class="submenu-item {{ $activeOfficeLocations ? 'active' : '' }}">
                                        <a href="{{ url('/office-locations') }}" class="submenu-link"
                                            >Office Locations</a
                                        >
                                    </li>
                                    <li class="submenu-item {{ $activeHolidaysManagement ? 'active' : '' }}">
                                        <a href="{{ url('/holidays') }}" class="submenu-link">Holidays Management</a>
                                    </li>
                                @endif
                                <li class="submenu-item {{ $activeTasks ? 'active' : '' }}">
                                    <a href="{{ url('/tasks') }}" class="submenu-link">Tasks</a>
                                </li>
                                <li class="submenu-item {{ $activeLeaveRequests ? 'active' : '' }}">
                                    <a href="{{ url('/leave-requests') }}" class="submenu-link">Leave Requests</a>
                                </li>
                                <li class="submenu-item {{ $activeIncidents ? 'active' : '' }}">
                                    <a href="{{ url('/incidents') }}" class="submenu-link">Incidents & Awards</a>
                                </li>
                            </ul>
                        </li>

                        @if ($isMasterAdmin || $role === \App\Constants\Roles::SUPER_ADMIN || $isSales)
                            <li class="sidebar-item has-sub {{ $crmMenuActive ? 'active' : '' }}">
                                <a href="#" class="sidebar-link">
                                    <i class="bi bi-people-fill"></i>
                                    <span>CRM</span>
                                </a>
                                <ul class="submenu {{ $crmMenuActive ? 'active' : '' }}">
                                    <li class="submenu-item {{ $activeCrmDashboard ? 'active' : '' }}">
                                        <a href="{{ url('crm/dashboard') }}" class="submenu-link">Dashboard</a>
                                    </li>
                                    <li class="submenu-item {{ $activeCrmBoard ? 'active' : '' }}">
                                        <a href="{{ url('crm/board') }}" class="submenu-link">Pipeline / Tasks</a>
                                    </li>
                                    <li class="submenu-item {{ $activeCrmContacts ? 'active' : '' }}">
                                        <a href="{{ url('crm/contacts') }}" class="submenu-link">Contacts</a>
                                    </li>
                                    <li class="submenu-item {{ $activeCrmEmailBlasts ? 'active' : '' }}">
                                        <a href="{{ url('crm/email-blasts') }}" class="submenu-link">Email Blasts</a>
                                    </li>
                                    <li class="submenu-item {{ $activeCrmWaBlasts ? 'active' : '' }}">
                                        <a href="{{ url('crm/wa-blasts') }}" class="submenu-link">WA Blasts</a>
                                    </li>
                                    <li class="submenu-item {{ $activeCrmTemplates ? 'active' : '' }}">
                                        <a href="{{ url('crm/templates') }}" class="submenu-link">Message Templates</a>
                                    </li>
                                </ul>
                            </li>
                        @endif

                        @if ($showPayrollGroup)
                            <li class="sidebar-item has-sub {{ $activePayroll || $kpiMenuActive ? 'active' : '' }}">
                                <a href="#" class="sidebar-link">
                                    <i class="bi bi-cash-stack"></i>
                                    <span>Payroll & KPI</span>
                                </a>
                                <ul class="submenu {{ $activePayroll || $kpiMenuActive ? 'active' : '' }}">
                                    @if ($isAdmin || $isMasterAdmin || $isFinanceRole)
                                        <li class="submenu-item {{ $activePayroll ? 'active' : '' }}">
                                            <a href="{{ url('/payrolls') }}" class="submenu-link">Payrolls</a>
                                        </li>
                                    @endif
                                    @if ($user->hasAccess('hr_reports') || $isAdmin || $isManager)
                                        <li class="submenu-item {{ $activeKpiDashboard ? 'active' : '' }}">
                                            <a href="{{ url('/kpi/dashboard') }}" class="submenu-link">KPI Dashboard</a>
                                        </li>
                                    @endif
                                    @if ($isMasterAdmin || $isManager)
                                        <li class="submenu-item {{ $activeKpiTeam ? 'active' : '' }}">
                                            <a href="{{ url('/kpi/team') }}" class="submenu-link">Team KPI</a>
                                        </li>
                                        <li class="submenu-item {{ $activeKpiDepartment ? 'active' : '' }}">
                                            <a href="{{ url('/kpi/department') }}" class="submenu-link"
                                                >Department KPI</a
                                            >
                                        </li>
                                        <li class="submenu-item {{ $activeKpiPending ? 'active' : '' }}">
                                            <a href="{{ url('/kpi/pending') }}" class="submenu-link"
                                                >Pending Approvals</a
                                            >
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        @endif

                        @if ($isAdmin || $isMasterAdmin || $isManager || $isMarketing || $isSupervisor || $isFinanceRole || $hasInventoryAccess)
                            <li class="sidebar-item has-sub {{ $financeMenuActive ? 'active' : '' }}">
                                <a href="#" class="sidebar-link">
                                    <i class="bi bi-wallet2"></i>
                                    <span>Finance</span>
                                </a>
                                <ul class="submenu {{ $financeMenuActive ? 'active' : '' }}">
                                    @if ($isAdmin || $isMasterAdmin || $isManager || $isMarketing || $isFinanceRole)
                                        <li class="submenu-item {{ $activeFinanceTransactions ? 'active' : '' }}">
                                            <a href="{{ url('/finance/transactions') }}" class="submenu-link"
                                                >Cashbook (Ledger)</a
                                            >
                                        </li>
                                        <li class="submenu-item {{ $activeFinanceEntities ? 'active' : '' }}">
                                            <a href="{{ url('/finance/entities') }}" class="submenu-link"
                                                >Master Entities</a
                                            >
                                        </li>
                                        <li class="submenu-item {{ $activeFinanceAccounts ? 'active' : '' }}">
                                            <a href="{{ url('/finance/accounts') }}" class="submenu-link"
                                                >Account Categories</a
                                            >
                                        </li>
                                    @endif

                                    <li class="submenu-item {{ $activeFinanceReports ? 'active' : '' }}">
                                        <a href="{{ url('/finance/reports') }}" class="submenu-link"
                                            >Financial Reports</a
                                        >
                                    </li>

                                    @if ($isAdmin || $isMasterAdmin || $isManager || $isMarketing || $isFinanceRole)
                                        <li class="submenu-item {{ $activeFinanceCharts ? 'active' : '' }}">
                                            <a href="{{ url('/finance/charts') }}" class="submenu-link"
                                                >Analytical Charts</a
                                            >
                                        </li>
                                    @endif

                                    @if ($isAdmin || $isMasterAdmin)
                                        <li class="submenu-item {{ $activeFinanceClaims ? 'active' : '' }}">
                                            <a href="{{ url('/finance/claims') }}" class="submenu-link"
                                                >Manage Claims</a
                                            >
                                        </li>
                                    @endif
                                </ul>
                            </li>
                            <li class="sidebar-item has-sub {{ $inventoryMenuActive ? 'active' : '' }}">
                                <a href="#" class="sidebar-link">
                                    <i class="bi bi-box"></i>
                                    <span>Inventory</span>
                                </a>
                                <ul class="submenu {{ $inventoryMenuActive ? 'active' : '' }}">
                                    @if ($showInventoryAdmin)
                                        <li class="submenu-item {{ $activeInventoryCategories ? 'active' : '' }}">
                                            <a href="{{ url('/inventory-categories') }}" class="submenu-link"
                                                >Inv. Categories</a
                                            >
                                        </li>
                                        <li class="submenu-item {{ $activeInventories ? 'active' : '' }}">
                                            <a href="{{ url('/inventories') }}" class="submenu-link">Inventories</a>
                                        </li>
                                    @endif

                                    @if ($showInventoryLogs)
                                        <li class="submenu-item {{ $activeInventoryUsage ? 'active' : '' }}">
                                            <a href="{{ url('/inventory-usage-logs') }}" class="submenu-link"
                                                >Usage Logs</a
                                            >
                                        </li>
                                    @endif

                                    <li class="submenu-item {{ $activeInventoryRequests ? 'active' : '' }}">
                                        <a href="{{ url('/inventory-requests') }}" class="submenu-link"
                                            >Inv. Requests</a
                                        >
                                    </li>

                                    @if ($isMasterAdmin || $isFinanceRole || $user->hasAccess('inventory'))
                                        <li class="submenu-item {{ $activeVendors ? 'active' : '' }}">
                                            <a href="{{ url('/vendors') }}" class="submenu-link">Vendors</a>
                                        </li>
                                        <li class="submenu-item {{ $activeProcurements ? 'active' : '' }}">
                                            <a href="{{ url('/procurements') }}" class="submenu-link">Procurements</a>
                                        </li>
                                        <li class="submenu-item {{ $activeInventoryDispatches ? 'active' : '' }}">
                                            <a href="{{ url('/inventory-dispatches') }}" class="submenu-link"
                                                >Releases & Barcode</a
                                            >
                                        </li>
                                        <li class="submenu-item {{ $activeLogisticsShipments ? 'active' : '' }}">
                                            <a href="{{ url('/logistics-shipments') }}" class="submenu-link"
                                                >Logistics Tracking</a
                                            >
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        @endif

                        <li class="sidebar-item has-sub {{ $lettersMenuActive ? 'active' : '' }}">
                            <a href="#" class="sidebar-link">
                                <i class="bi bi-envelope-fill"></i>
                                <span>Letters</span>
                            </a>
                            <ul class="submenu {{ $lettersMenuActive ? 'active' : '' }}">
                                <li class="submenu-item {{ $activeLetters ? 'active' : '' }}">
                                    <a href="{{ url('/letters') }}" class="submenu-link">Letters</a>
                                </li>
                                @if ($isAdmin)
                                    <li class="submenu-item {{ $activeLetterTemplates ? 'active' : '' }}">
                                        <a href="{{ url('/letter-templates') }}" class="submenu-link">Templates</a>
                                    </li>
                                    <li class="submenu-item {{ $activeLetterConfigs ? 'active' : '' }}">
                                        <a href="{{ url('/letter-configurations') }}" class="submenu-link"
                                            >Configurations</a
                                        >
                                    </li>
                                    <li class="submenu-item {{ $activeLetterTags ? 'active' : '' }}">
                                        <a href="{{ url('/letter-tags') }}" class="submenu-link">Tags</a>
                                    </li>
                                @endif
                                @if ($isDevOrAdmin)
                                    <li class="submenu-item {{ $activeLetterArchives ? 'active' : '' }}">
                                        <a href="{{ url('/letter-archives') }}" class="submenu-link">Archives</a>
                                    </li>
                                    <li class="submenu-item {{ $activeSignatureLogs ? 'active' : '' }}">
                                        <a href="{{ url('/signature-logs') }}" class="submenu-link">Signature Logs</a>
                                    </li>
                                @endif
                            </ul>
                        </li>

                        @if ($isManagerOrAdmin)
                            <li class="sidebar-item has-sub {{ $reportsMenuActive ? 'active' : '' }}">
                                <a href="#" class="sidebar-link">
                                    <i class="bi bi-file-earmark-text"></i>
                                    <span>Reports</span>
                                </a>
                                <ul class="submenu {{ $reportsMenuActive ? 'active' : '' }}">
                                    @if ($isAdmin)
                                        <li class="submenu-item {{ $activeReportsExec ? 'active' : '' }}">
                                            <a href="{{ url('/reports/executive') }}" class="submenu-link"
                                                >Executive Report</a
                                            >
                                        </li>
                                    @endif
                                    <li class="submenu-item {{ $activeReportsMonthly ? 'active' : '' }}">
                                        <a href="{{ url('/reports/monthly-recap') }}" class="submenu-link"
                                            >Monthly Report</a
                                        >
                                    </li>
                                </ul>
                            </li>
                        @endif

                        <li class="sidebar-title">Personal</li>

                        <li class="sidebar-item {{ request()->is('my-profile') ? 'active' : '' }}">
                            <a href="{{ url('/my-profile') }}" class="sidebar-link">
                                <i class="bi bi-person-fill"></i>
                                <span>My Profile</span>
                            </a>
                        </li>

                        <li class="sidebar-item {{ request()->is('presences') ? 'active' : '' }}">
                            <a href="{{ url('/presences') }}" class="sidebar-link">
                                <i class="bi bi-table"></i>
                                <span>Presences</span>
                            </a>
                        </li>

                        <li class="sidebar-item {{ request()->is('knowledge-base*') ? 'active' : '' }}">
                            <a href="{{ url('/knowledge-base') }}" class="sidebar-link">
                                <i class="bi bi-book"></i>
                                <span>Knowledge Base</span>
                            </a>
                        </li>

                        <li class="sidebar-item {{ $activeWorkLogs ? 'active' : '' }}">
                            <a href="{{ route('work-logs.index') }}" class="sidebar-link">
                                <i class="bi bi-journal-check"></i>
                                <span>My Activities</span>
                            </a>
                        </li>

                        <li class="sidebar-item {{ $overtimes ? 'active' : '' }}">
                            <a href="{{ route('overtimes.index') }}" class="sidebar-link">
                                <i class="bi bi-clock"></i>
                                <span>My Overtime</span>
                            </a>
                        </li>

                        <li class="sidebar-item {{ $activeMyFinance ? 'active' : '' }}">
                            <a href="{{ url('/finance/my-finance') }}" class="sidebar-link">
                                <i class="bi bi-wallet-fill"></i>
                                <span>My Finance</span>
                            </a>
                        </li>

                        <li class="sidebar-item {{ $activeFinanceClaims ? 'active' : '' }}">
                            <a href="{{ url('/finance/claims') }}" class="sidebar-link">
                                <i class="bi bi-receipt"></i>
                                <span>My Expense Claims</span>
                            </a>
                        </li>

                        <li class="sidebar-item">
                            <a href="{{ url('/logout') }}" class="sidebar-link text-danger">
                                <i class="bi bi-box-arrow-right text-danger"></i>
                                <span>Logout</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div id="main">
            <header
                class="mobile-sticky-topbar d-xl-none py-2 px-3 mb-4 d-flex justify-content-between align-items-center"
            >
                <div class="logo">
                    <a href="{{ url('/dashboard') }}" class="d-flex align-items-center">
                        <img
                            src="{{ asset('corevo-logo.png') }}"
                            class="logo-light"
                            style="height: 38px; width: auto"
                            alt="Logo"
                        />
                        <img
                            src="{{ asset('corevo-logo-white.png') }}"
                            class="logo-dark"
                            style="height: 38px; width: auto"
                            alt="Logo"
                        />
                    </a>
                </div>
                <a class="burger-btn d-block fs-3 text-body">
                    <i class="bi bi-justify"></i>
                </a>
            </header>

            <header class="mb-4 d-none d-xl-flex align-items-center">
                <a
                    href="#"
                    class="desktop-toggle-btn text-body text-decoration-none"
                    style="font-size: 1.5rem; line-height: 1"
                >
                    <i class="bi bi-list"></i>
                </a>
            </header>

            @yield ('content')

            <footer class="footer clearfix mb-0 text-muted mt-4">
                <div class="float-start">
                    <p>2025 &copy; PT Aratech Nusantara Indonesia</p>
                </div>
                <div class="float-end">
                    <p>Crafted by <a href="https://aratechnology.id">Aratech</a></p>
                </div>
            </footer>
        </div>
    </div>

    <script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/responsive.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('mazer/assets/compiled/js/app.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('vendor/flatpickr/flatpickr.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        flatpickr('.date', { dateFormat: 'Y-m-d' });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
        });

        $.extend(true, $.fn.dataTable.defaults, {
            responsive: true,
            language: {
                processing: `
          <div class="custom-loader-box">
            <span class="modern-spinner"></span>
            <span class="loader-text">Loading Data</span>
          </div>
        `,
                lengthMenu: 'Show _MENU_ entries',
                zeroRecords: 'No records found',
                info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                infoEmpty: 'Showing 0 to 0 of 0 entries',
                infoFiltered: '(filtered from _MAX_ total entries)',
                search: 'Search:',
                paginate: {
                    first: 'First',
                    last: 'Last',
                    next: 'Next',
                    previous: 'Previous',
                },
            },
        });

        $(document).ready(function () {
            $('table.table').each(function () {
                if (!$(this).parent().hasClass('table-responsive') && !$(this).parent().hasClass('dt-scroll')) {
                    $(this).wrap('<div class="table-responsive"></div>');
                }
            });
        });

        $(document).ajaxError(function (event, jqXHR, ajaxSettings, thrownError) {
            if (jqXHR.status === 403) {
                Swal.fire('Access Denied', 'You do not have permission to perform this action.', 'error');
            } else if (jqXHR.status === 500) {
                Swal.fire('Server Error', 'An unexpected error occurred. Please try again later.', 'error');
            }
        });

        window.confirmDelete = function (formOrUrl, message = 'Are you sure you want to delete this record?') {
            Swal.fire({
                title: 'Delete Confirmation',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel',
            }).then((result) => {
                if (result.isConfirmed) {
                    if (typeof formOrUrl === 'string') {
                        window.location.href = formOrUrl;
                    } else {
                        formOrUrl.submit();
                    }
                }
            });
            return false;
        };

        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible') {
                fetch(window.location.href, {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                })
                    .then((response) => {
                        if (response.status === 401 || response.status === 419) {
                            console.log('Session expired. Redirecting user to the login page...');
                            window.location.href = '/login';
                        }
                    })
                    .catch((error) => console.log('Session check skipped.'));
            }
        });

        const toggleDark = document.getElementById('toggle-dark');
        const htmlElement = document.documentElement;

        if (localStorage.getItem('theme') === 'dark' && toggleDark) {
            toggleDark.checked = true;
        }

        if (toggleDark) {
            toggleDark.addEventListener('change', function () {
                if (this.checked) {
                    htmlElement.setAttribute('data-bs-theme', 'dark');
                    localStorage.setItem('theme', 'dark');
                } else {
                    htmlElement.setAttribute('data-bs-theme', 'light');
                    localStorage.setItem('theme', 'light');
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            const body = document.body;
            const toggleBtns = document.querySelectorAll('.desktop-toggle-btn');

            toggleBtns.forEach((btn) => {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    body.classList.toggle('sidebar-collapsed');

                    if (body.classList.contains('sidebar-collapsed')) {
                        localStorage.setItem('desktopSidebarState', 'collapsed');
                    } else {
                        localStorage.setItem('desktopSidebarState', 'expanded');
                    }
                });
            });
        });
    </script>

    @stack ('scripts')
</body>
</html>
