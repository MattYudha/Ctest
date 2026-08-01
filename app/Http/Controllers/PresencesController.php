<?php

namespace App\Http\Controllers;

use App\Models\Presence;
use App\Models\Employee;
use App\Models\OfficeLocation;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use App\Services\HolidayService;

class PresencesController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Presence::with(['employee.officeLocation', 'officeLocation']);

            if (!in_array(session('role'), ['HR Administrator', \App\Constants\Roles::MASTER_ADMIN])) {
                $query->where('employee_id', session('employee_id'));
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btns = '<div class="btn-group btn-group-sm" role="group">';
                    $btns .=
                        '<a href="' .
                        route('presences.show', $row->id) .
                        '" class="btn btn-outline-info" title="View Details"><i class="bi bi-eye"></i></a>';

                    if (in_array(session('role'), ['HR Administrator', \App\Constants\Roles::MASTER_ADMIN])) {
                        $btns .=
                            '<a href="' .
                            route('presences.edit', $row->id) .
                            '" class="btn btn-outline-warning"><i class="bi bi-pencil"></i></a>';
                        $csrf = csrf_token();
                        $btns .=
                            '
                            <form action="' .
                            route('presences.destroy', $row->id) .
                            '" method="POST" class="d-inline">
                                <input type="hidden" name="_token" value="' .
                            $csrf .
                            '">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn btn-outline-danger" onclick="return confirm(\'Delete this record?\')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        ';
                    }

                    $btns .= '</div>';
                    return $btns;
                })
                ->addColumn('status_badge', function ($row) {
                    $class = match ($row->status) {
                        'present' => 'bg-success',
                        'absent' => 'bg-danger',
                        'leave' => 'bg-info',
                        default => 'bg-secondary',
                    };
                    $badge = '<span class="badge ' . $class . '">' . ucfirst($row->status) . '</span>';

                    if ($row->status === 'present' && $row->is_late) {
                        $badge .= ' <span class="badge bg-warning text-dark">Late</span>';
                    }

                    return $badge;
                })
                ->addColumn('work_type_badge', function ($row) {
                    $class = match ($row->work_type) {
                        'WFO' => 'bg-primary',
                        'WFH' => 'bg-secondary',
                        'WFA' => 'bg-dark',
                        default => 'bg-light text-dark',
                    };
                    return '<span class="badge ' . $class . '">' . ($row->work_type ?? 'WFO') . '</span>';
                })
                ->addColumn('office_location_name', function ($row) {
                    if ($row->officeLocation) {
                        return e($row->officeLocation->name);
                    }
                    if (($row->work_type ?? 'WFO') === 'WFO' && $row->employee?->officeLocation) {
                        return e($row->employee->officeLocation->name);
                    }
                    return '-';
                })
                ->editColumn('date', function ($row) {
                    return $row->date ? Carbon::parse($row->date)->format('d M Y') : '-';
                })
                ->editColumn('check_in', function ($row) {
                    return $row->check_in ? Carbon::parse($row->check_in)->format('H:i:s') : '-';
                })
                ->editColumn('check_out', function ($row) {
                    if ($row->check_out) {
                        return Carbon::parse($row->check_out)->format('H:i:s');
                    }
                    if (
                        session('employee_id') == $row->employee_id &&
                        Carbon::parse($row->date)->isToday() &&
                        $row->check_in &&
                        !$row->check_out
                    ) {
                        $workTypeLower = strtolower($row->work_type ?? 'WFO');
                        $enableMaxCheckout = \App\Models\Setting::getValue('enable_max_checkout_' . $workTypeLower, '0') === '1';
                        
                        if ($enableMaxCheckout) {
                            $maxCheckoutTimeStr = \App\Models\Setting::getValue('max_checkout_time_' . $workTypeLower, '17:30');
                            $maxCheckoutTime = Carbon::parse(date('Y-m-d') . ' ' . $maxCheckoutTimeStr);
                            
                            if (Carbon::now()->gt($maxCheckoutTime)) {
                                return '-';
                            }
                        }

                        return '<a href="' .
                            route('presences.checkout') .
                            '" class="btn btn-sm btn-success">Check Out</a>';
                    }
                    return '-';
                })
                ->rawColumns(['action', 'status_badge', 'work_type_badge', 'check_out'])
                ->make(true);
        }

        // --- additional holiday check for alert in index ---
        $todayDate = Carbon::today()->format('Y-m-d');
        $year = Carbon::today()->year;
        $month = Carbon::today()->month;

        $holidays = HolidayService::getHolidays($year, $month);
        $isHolidayToday = false;
        $holidayName = '';

        if (Carbon::today()->isWeekend()) {
            $isHolidayToday = true;
            $holidayName = 'Weekend (Saturday / Sunday)';
        } else {
            foreach ($holidays as $h) {
                if ($h['date'] === $todayDate) {
                    $isHolidayToday = true;
                    $holidayName = $h['name'];
                    break;
                }
            }
        }

        return view('presences.index', compact('isHolidayToday', 'holidayName'));
    }

    // show the form to create a new attendance record
    public function create()
    {
        // double protection: prevent direct url access for everyone during holidays.
        // admins should use master presence for manual attendance, not here.
        $todayDate = Carbon::today()->format('Y-m-d');
        $year = Carbon::today()->year;
        $month = Carbon::today()->month;

        if (Carbon::today()->isWeekend()) {
            return redirect()
                ->route('presences.index')
                ->with(
                    'error',
                    'System Closed: Today is the weekend (Saturday/Sunday). You cannot perform self-attendance.',
                );
        }

        $holidayDates = HolidayService::getHolidayDates($year, $month);

        if (in_array($todayDate, $holidayDates)) {
            return redirect()
                ->route('presences.index')
                ->with(
                    'error',
                    'System Closed: Today is a National Holiday / Collective Leave. You cannot perform self-attendance.',
                );
        }

        $employees = Employee::all();
        $currentEmployee = Auth::user()?->employee;

        if ($currentEmployee) {
            $currentEmployee->loadMissing('officeLocation');
        }

        $wfoOfficeLocations = $this->getSelectableWfoOfficeLocations();
        $selectedWfoOfficeLocation = $this->resolveDefaultWfoOfficeLocation($currentEmployee, $wfoOfficeLocations);

        return view('presences.create', compact('employees', 'wfoOfficeLocations', 'selectedWfoOfficeLocation'));
    }

    // store a newly created attendance record
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        // fix: we check from the request, not the role.
        // so if master admin clicks the wfo button from a mobile phone, they still enter self-attendance.
        if ($request->has('employee_id') && $request->has('check_in')) {
            // ==========================================
            // block 1: manual admin form (bypass holidays)
            // ==========================================
            $validator = Validator::make($request->all(), [
                'employee_id' => 'required|exists:employees,id',
                'check_in' => 'required|date',
                'check_out' => 'nullable|date|after_or_equal:check_in',
                'status' => 'required|in:present,absent,leave',
            ]);

            if ($validator->fails()) {
                return redirect()->route('presences.index')->withErrors($validator)->withInput();
            }

            Presence::create([
                'employee_id' => $request->employee_id,
                'check_in' => Carbon::parse($request->check_in)->format('Y-m-d H:i:s'),
                'check_out' => $request->filled('check_out')
                    ? Carbon::parse($request->check_out)->format('Y-m-d H:i:s')
                    : null,
                'date' => Carbon::parse($request->check_in)->format('Y-m-d'),
                'status' => $request->status,
                'work_type' => 'WFO',
            ]);
        } else {
            // ==========================================
            // block 2: self attendance (wfo / wfh / wfa)
            // ==========================================

            if (Carbon::today()->isWeekend()) {
                return redirect()
                    ->back()
                    ->with(
                        'error',
                        'System Closed: Today is the weekend (Saturday/Sunday). You cannot perform attendance.',
                    );
            }

            // holiday check: reject attendance if today is a public holiday / collective leave
            $todayDate = Carbon::today()->format('Y-m-d');
            $year = Carbon::today()->year;
            $month = Carbon::today()->month;

            $holidayDates = HolidayService::getHolidayDates($year, $month);

            if (in_array($todayDate, $holidayDates)) {
                return redirect()
                    ->back()
                    ->with(
                        'error',
                        'System Closed: Today is a National Holiday / Collective Leave. You cannot perform attendance.',
                    );
            }

            $workType = $request->work_type ?? 'WFO';
            $fingerprint = $request->fingerprint;
            $isMobile = $request->is_mobile == '1' || $request->is_mobile === 1 || $request->is_mobile === true;
            $ssid = $request->ssid ?? '';

            $employeeId = session('employee_id');
            $employee = $employeeId ? Employee::with('officeLocation')->find($employeeId) : null;
            $officeLocationConfig = $this->resolveOfficeLocationForEmployee($employee);
            $selectedWfoOfficeLocationId = null;

            // device fingerprinting logic
            if (empty($fingerprint)) {
                return redirect()
                    ->back()
                    ->with('error', 'Failed to verify your device identity. Please refresh the page and try again.');
            }

            try {
                if ($isMobile) {
                    if (!$user->browser_fingerprint_mobile) {
                        $user->update(['browser_fingerprint_mobile' => $fingerprint]);
                    } elseif ($user->browser_fingerprint_mobile !== $fingerprint) {
                        $this->logSuspicious(
                            $user->id,
                            'wrong_fingerprint',
                            "Mobile fingerprint mismatch. Got: $fingerprint",
                        );
                        return redirect()
                            ->back()
                            ->with('error', 'Mobile device is unregistered. Please use your registered device.');
                    }
                } else {
                    if (!$user->browser_fingerprint_desktop) {
                        $user->update(['browser_fingerprint_desktop' => $fingerprint]);
                    } elseif ($user->browser_fingerprint_desktop !== $fingerprint) {
                        $this->logSuspicious(
                            $user->id,
                            'wrong_fingerprint',
                            "Desktop fingerprint mismatch. Got: $fingerprint",
                        );
                        return redirect()
                            ->back()
                            ->with('error', 'Browser is unregistered. Please use your registered browser.');
                    }
                }
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'An error occurred while verifying the device.');
            }

            // wfo validation
            if ($workType === 'WFO') {
                $validator = Validator::make($request->all(), [
                    'office_location_id' => 'required|integer|exists:office_locations,id',
                    'latitude' => 'required|numeric',
                    'longitude' => 'required|numeric',
                    'accuracy' => 'required|numeric',
                ]);

                if ($validator->fails()) {
                    return redirect()->back()->withErrors($validator)->withInput();
                }

                $selectedWfoOfficeLocationId = (int) $request->office_location_id;
                $officeLocationConfig = $this->resolveOfficeLocationForSelection($selectedWfoOfficeLocationId);

                if (!$officeLocationConfig) {
                    return redirect()->back()->withInput()->with('error', 'WFO location is invalid or inactive.');
                }

                if ($request->accuracy <= 5) {
                    $this->logSuspicious($user->id, 'fake_gps', "Unnatural accuracy detected: {$request->accuracy}m");
                    return redirect()
                        ->back()
                        ->with(
                            'error',
                            'Location detected using a Fake GPS application. Please disable it and try again.',
                        );
                }

                $officeLat = $officeLocationConfig['latitude'];
                $officeLon = $officeLocationConfig['longitude'];
                $radius = $officeLocationConfig['radius'];
                $distance = $this->calculateDistance($request->latitude, $request->longitude, $officeLat, $officeLon);

                if ($distance > $radius) {
                    $this->logSuspicious($user->id, 'out_of_radius', "Attendance attempt from $distance meters away.");
                    return redirect()
                        ->back()
                        ->with(
                            'error',
                            "You are outside the radius of {$officeLocationConfig['name']} (Your distance: " .
                                round($distance) .
                                ' meters).',
                        );
                }

                $allowedIPs = $officeLocationConfig['allowed_ips'] ?? [];
                $clientIp = request()->ip();

                if (!empty($allowedIPs) && !in_array($clientIp, $allowedIPs)) {
                    $this->logSuspicious(
                        $user->id,
                        'invalid_ip',
                        "Attendance attempt from an unknown network: $clientIp",
                    );
                    return redirect()
                        ->back()
                        ->with(
                            'error',
                            "The system rejected your WFO attendance because your device IP ($clientIp) is not connected to the official office WiFi.",
                        );
                }
            }

            if (!$employeeId) {
                return redirect()->back()->with('error', 'Invalid session. Please log in again.');
            }

            $today = Carbon::today(env('APP_TIMEZONE', 'Asia/Jakarta'));
            $existingPresence = Presence::where('employee_id', $employeeId)
                ->whereDate('date', $today)
                ->whereNotNull('check_in')
                ->first();

            if ($existingPresence) {
                if ($existingPresence->check_out) {
                    return redirect()->back()->with('error', 'You have already Checked-In and Checked-Out today.');
                } else {
                    return redirect()->back()->with('error', 'You have already Checked-In today. Please Check-Out.');
                }
            }

            $checkInTime = Carbon::now(env('APP_TIMEZONE', 'Asia/Jakarta'));
            $workStartTimeStr = Setting::getValue('work_start_time', '08:00');
            $workStartTime = Carbon::parse(date('Y-m-d') . ' ' . $workStartTimeStr, env('APP_TIMEZONE', 'Asia/Jakarta'));
            $lateThreshold = (int) Setting::getValue('late_threshold_minutes', 15);

            try {
                $latitude = $request->has('latitude') && !empty($request->latitude) ? $request->latitude : null;
                $longitude = $request->has('longitude') && !empty($request->longitude) ? $request->longitude : null;

                $photoPath = null;
                if ($request->filled('photo_data')) {
                    $image_parts = explode(';base64,', $request->photo_data);
                    if (count($image_parts) == 2) {
                        $image_type_aux = explode('image/', $image_parts[0]);
                        $image_type = $image_type_aux[1];
                        $image_base64 = base64_decode($image_parts[1]);

                        $fileName = 'presence_' . $employeeId . '_' . time() . '.' . $image_type;
                        $folderPath = 'presence_photos/' . date('Y-m');

                        \Illuminate\Support\Facades\Storage::disk('public')->put(
                            $folderPath . '/' . $fileName,
                            $image_base64,
                        );
                        $photoPath = $folderPath . '/' . $fileName;
                    }
                }

                $dummyPresence = new Presence([
                    'status' => 'present',
                    'work_type' => $workType,
                    'check_in' => $checkInTime->format('Y-m-d H:i:s'),
                    'date' => $checkInTime->format('Y-m-d'),
                ]);
                $isLate = $this->isLateCheckIn($dummyPresence);

                $presenceData = [
                    'employee_id' => $employeeId,
                    'office_location_id' =>
                        $workType === 'WFO'
                            ? ($selectedWfoOfficeLocationId ?:
                            $officeLocationConfig['id'] ?? null)
                            : null,
                    'check_in' => $checkInTime->format('Y-m-d H:i:s'),
                    'date' => $checkInTime->format('Y-m-d'),
                    'status' => 'present',
                    'work_type' => $workType,
                    'is_late' => $isLate,
                    'photo_path' => $photoPath,
                ];

                if ($latitude !== null) {
                    $presenceData['latitude'] = $latitude;
                }
                if ($longitude !== null) {
                    $presenceData['longitude'] = $longitude;
                }

                $presence = Presence::create($presenceData);
            } catch (\Exception $e) {
                return redirect()
                    ->back()
                    ->with('error', 'Failed to save attendance data: ' . $e->getMessage());
            }

            if ($isLate) {
                $lateMinutes = $checkInTime->diffInMinutes($workStartTime->copy()->addMinutes($lateThreshold));
                $targetRoute = in_array(session('role'), ['HR Administrator', \App\Constants\Roles::MASTER_ADMIN])
                    ? 'presences.index'
                    : 'dashboard';
                return redirect()
                    ->route($targetRoute)
                    ->with('warning', "Attendance successfully recorded. You are {$lateMinutes} minutes late.");
            }
        }

        $targetRoute = in_array(session('role'), ['HR Administrator', \App\Constants\Roles::MASTER_ADMIN])
            ? 'presences.index'
            : 'dashboard';
        return redirect()->route($targetRoute)->with('success', 'Attendance successfully recorded.');
    }

    private function getSelectableWfoOfficeLocations(): array
    {
        return OfficeLocation::active()
            ->orderBy('name')
            ->get()
            ->map(fn(OfficeLocation $officeLocation) => $this->mapOfficeLocationToConfig($officeLocation))
            ->values()
            ->all();
    }

    private function resolveDefaultWfoOfficeLocation(?Employee $employee, array $officeLocations): array
    {
        $assignedOfficeLocationId = $employee?->office_location_id;

        if ($assignedOfficeLocationId) {
            foreach ($officeLocations as $officeLocation) {
                if ((int) $officeLocation['id'] === (int) $assignedOfficeLocationId) {
                    return $officeLocation;
                }
            }
        }

        return $officeLocations[0] ?? $this->defaultOfficeLocationConfig();
    }

    private function resolveOfficeLocationForSelection(?int $officeLocationId): ?array
    {
        if (!$officeLocationId) {
            return null;
        }

        $officeLocation = OfficeLocation::active()->find($officeLocationId);

        return $officeLocation ? $this->mapOfficeLocationToConfig($officeLocation) : null;
    }

    private function resolveOfficeLocationForPresence(?Presence $presence, ?Employee $employee = null): array
    {
        $officeLocation = $presence?->officeLocation;

        if (!$officeLocation) {
            $officeLocation = $employee?->officeLocation;
        }

        return $officeLocation
            ? $this->mapOfficeLocationToConfig($officeLocation)
            : $this->defaultOfficeLocationConfig();
    }

    private function resolveOfficeLocationForEmployee(?Employee $employee): array
    {
        return $employee?->officeLocation
            ? $this->mapOfficeLocationToConfig($employee->officeLocation)
            : $this->defaultOfficeLocationConfig();
    }

    private function defaultOfficeLocationConfig(): array
    {
        return [
            'id' => null,
            'name' => 'Head Office',
            'latitude' => (float) config('presence.office_latitude', -6.2),
            'longitude' => (float) config('presence.office_longitude', 106.816666),
            'radius' => (int) config('presence.location_radius', 1000),
            'allowed_ssids' => [],
            'allowed_ips' => [],
            'address' => null,
        ];
    }

    private function mapOfficeLocationToConfig(OfficeLocation $officeLocation): array
    {
        $defaultConfig = $this->defaultOfficeLocationConfig();

        return [
            'id' => $officeLocation->id,
            'name' => $officeLocation->name,
            'latitude' =>
                $officeLocation->latitude !== null ? (float) $officeLocation->latitude : $defaultConfig['latitude'],
            'longitude' =>
                $officeLocation->longitude !== null ? (float) $officeLocation->longitude : $defaultConfig['longitude'],
            'radius' => !empty($officeLocation->radius) ? (int) $officeLocation->radius : $defaultConfig['radius'],
            'allowed_ssids' => !empty($officeLocation->allowed_ssids)
                ? array_values(array_filter($officeLocation->allowed_ssids))
                : $defaultConfig['allowed_ssids'],
            'allowed_ips' => !empty($officeLocation->allowed_ips)
                ? array_values(array_filter($officeLocation->allowed_ips))
                : $defaultConfig['allowed_ips'],
            'address' => $officeLocation->address,
        ];
    }

    private function buildInvalidSsidMessage(string $officeName, array $allowedSSIDs): string
    {
        if (empty($allowedSSIDs)) {
            return 'You must be connected to the registered office WiFi for WFO attendance.';
        }

        $ssidList = implode(', ', array_map(fn($ssid) => '"' . $ssid . '"', $allowedSSIDs));

        return "You must be connected to the {$officeName} WiFi ({$ssidList}) for WFO attendance.";
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // in meters

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }

    private function logSuspicious($userId, $type, $details)
    {
        \App\Models\SuspiciousActivity::create([
            'user_id' => $userId,
            'activity_type' => $type,
            'details' => $details,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    // show the form for editing an attendance record
    public function edit(Presence $presence)
    {
        $employees = Employee::all();
        return view('presences.edit', compact('presence', 'employees'));
    }

    // update the specified attendance record
    public function update(Request $request, Presence $presence)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'check_in' => 'required|date',
            'check_out' => 'nullable|date|after_or_equal:check_in',
            'status' => 'required|in:present,absent,leave',
        ]);

        $presence->update($request->all());

        return redirect()->route('presences.index')->with('success', 'Attendance data updated successfully.');
    }

    // delete an attendance record
    public function destroy(Presence $presence)
    {
        if ($presence->photo_path && Storage::disk('public')->exists($presence->photo_path)) {
            Storage::disk('public')->delete($presence->photo_path);
        }

        $presence->delete();

        return redirect()->route('presences.index')->with('success', 'Attendance data deleted successfully.');
    }

    // check-out functionality - show form
    public function checkout()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $employeeId = session('employee_id');
        $employee = $employeeId ? Employee::with('officeLocation')->find($employeeId) : null;

        // find today's presence record with check-in but no check-out
        $today = Carbon::today();
        $presence = Presence::with('officeLocation')
            ->where('employee_id', $employeeId)
            ->whereDate('date', $today)
            ->whereNotNull('check_in')
            ->whereNull('check_out')
            ->first();

        if (!$presence) {
            return redirect()
                ->route('presences.index')
                ->with('error', 'No check-in record found for today. Please check in first.');
        }

        $workType = $presence->work_type ?? 'WFO';
        // only resolve office location config for wfo; wfh/wfa don't need it
        $officeLocationConfig =
            $workType === 'WFO'
                ? $this->resolveOfficeLocationForPresence($presence, $employee)
                : $this->defaultOfficeLocationConfig();
        $checkInTime = Carbon::parse($presence->check_in)->format('H:i:s');

        return view('presences.checkout', compact('presence', 'checkInTime', 'officeLocationConfig'));
    }

    // check-out functionality - process checkout
    public function processCheckout(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $employeeId = session('employee_id');
        $employee = $employeeId ? Employee::with('officeLocation')->find($employeeId) : null;

        // find today's presence record with check-in but no check-out
        $today = Carbon::today(env('APP_TIMEZONE', 'Asia/Jakarta'));
        $presence = Presence::with('officeLocation')
            ->where('employee_id', $employeeId)
            ->whereDate('date', $today)
            ->whereNotNull('check_in')
            ->whereNull('check_out')
            ->first();

        if (!$presence) {
            return redirect()
                ->route('presences.index')
                ->with('error', 'No check-in record found for today. Please check in first.');
        }

        $workType = $presence->work_type ?? 'WFO';
        // only resolve office location config for wfo; wfh/wfa don't need geofencing
        $officeLocationConfig =
            $workType === 'WFO'
                ? $this->resolveOfficeLocationForPresence($presence, $employee)
                : $this->defaultOfficeLocationConfig();

        // validate check-out cannot be before check-in
        $checkInTime = Carbon::parse($presence->check_in, env('APP_TIMEZONE', 'Asia/Jakarta'));
        $checkOutTime = Carbon::now(env('APP_TIMEZONE', 'Asia/Jakarta'));

        if ($checkOutTime->lt($checkInTime)) {
            return redirect()
                ->route('presences.checkout')
                ->with('error', 'Check-out time cannot be before check-in time.');
        }

        // check max checkout time configuration based on work type
        $workTypeLower = strtolower($workType);
        $enableMaxCheckoutKey = 'enable_max_checkout_' . $workTypeLower;
        $maxCheckoutTimeKey = 'max_checkout_time_' . $workTypeLower;

        $enableMaxCheckout = Setting::getValue($enableMaxCheckoutKey, '0') === '1';
        if ($enableMaxCheckout) {
            $maxCheckoutTimeStr = Setting::getValue($maxCheckoutTimeKey, '17:30');
            $maxCheckoutTime = Carbon::parse(date('Y-m-d') . ' ' . $maxCheckoutTimeStr);

            if ($checkOutTime->gt($maxCheckoutTime)) {
                return redirect()
                    ->route('presences.index')
                    ->with('error', 'You cannot check-out because it is past the maximum allowed check-out time (' . $maxCheckoutTimeStr . ') for ' . $workType . '.');
            }
        }

        // for wfo, calculate gps distance but do not block the checkout if out of bounds
        if ($presence->work_type === 'WFO' && $request->has('latitude')) {
            $validator = Validator::make($request->all(), [
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'accuracy' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            // calculate distance
            $officeLat = $officeLocationConfig['latitude'];
            $officeLon = $officeLocationConfig['longitude'];
            $radius = $officeLocationConfig['radius'];
            $distance = $this->calculateDistance($request->latitude, $request->longitude, $officeLat, $officeLon);

            if ($distance > $radius) {
                // log the suspicious activity but allow the checkout to proceed
                $this->logSuspicious(
                    $user->id,
                    'out_of_radius_checkout',
                    "User checked out $distance meters from {$officeLocationConfig['name']} (Limit: {$radius}m).",
                );
            }
        }

        // proceed to update check-out time and explicitly save to the new check_out coordinate columns
        $presence->update([
            'check_out' => $checkOutTime->format('Y-m-d H:i:s'),
            'check_out_latitude' => $request->latitude ?? null,
            'check_out_longitude' => $request->longitude ?? null,
        ]);

        $targetRoute = in_array(session('role'), ['HR Administrator', \App\Constants\Roles::MASTER_ADMIN])
            ? 'presences.index'
            : 'dashboard';
        return redirect()->route($targetRoute)->with('success', 'Check-out recorded successfully.');
    }

    public function show(Presence $presence)
    {
        // ensure the user is authorized to view this record
        if (
            !in_array(session('role'), ['HR Administrator', \App\Constants\Roles::MASTER_ADMIN]) &&
            session('employee_id') != $presence->employee_id
        ) {
            abort(403, 'Unauthorized access to this presence record.');
        }

        $presence->load(['employee.department', 'employee.role', 'officeLocation']);
        $officeConfig = $this->resolveOfficeLocationForPresence($presence, $presence->employee);

        // check late status using the controller's built-in function
        $isLate = $presence->is_late;

        // check-in geofence calculations
        $checkInDistance = 0;
        $isCheckInOutOfRadius = false;
        if ($presence->work_type === 'WFO' && $presence->latitude && $presence->longitude) {
            $checkInDistance = $this->calculateDistance(
                $presence->latitude,
                $presence->longitude,
                $officeConfig['latitude'],
                $officeConfig['longitude'],
            );
            if ($checkInDistance > $officeConfig['radius']) {
                $isCheckInOutOfRadius = true;
            }
        }

        // check-out geofence calculations
        $checkOutDistance = 0;
        $isCheckOutOutOfRadius = false;
        if ($presence->work_type === 'WFO' && $presence->check_out_latitude && $presence->check_out_longitude) {
            $checkOutDistance = $this->calculateDistance(
                $presence->check_out_latitude,
                $presence->check_out_longitude,
                $officeConfig['latitude'],
                $officeConfig['longitude'],
            );
            if ($checkOutDistance > $officeConfig['radius']) {
                $isCheckOutOutOfRadius = true;
            }
        }

        return view(
            'presences.show',
            compact(
                'presence',
                'officeConfig',
                'checkInDistance',
                'isCheckInOutOfRadius',
                'checkOutDistance',
                'isCheckOutOutOfRadius',
                'isLate',
            ),
        );
    }

    // calendar view
    public function calendar(Request $request)
    {
        $userRole = session('role');
        $isAdmin = in_array($userRole, ['HR Administrator', \App\Constants\Roles::MASTER_ADMIN]);
        $currentEmployeeId = session('employee_id');

        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;

        $year = (int) $request->get('year', $currentYear);
        $month = (int) $request->get('month', $currentMonth);

        if ($year < 2000 || $year > 2100) {
            $year = $currentYear;
        }
        if ($month < 1 || $month > 12) {
            $month = $currentMonth;
        }

        $selectedEmployeeId = $request->has('employee_id') ? $request->get('employee_id') : $currentEmployeeId;
        if (!$isAdmin) {
            $selectedEmployeeId = $currentEmployeeId;
        }

        $employees = [];
        if ($isAdmin) {
            $employees = Employee::orderBy('fullname')->get();
        }

        // fetch holiday data
        $holidays = \App\Services\HolidayService::getHolidays($year, $month);
        $holidayDates = array_column($holidays, 'date');
        $events = [];

        // fetch actual presence data
        $query = Presence::with('employee')->whereYear('date', $year)->whereMonth('date', $month);

        if ($selectedEmployeeId) {
            $query->where('employee_id', $selectedEmployeeId);
        }

        $presences = $query->get();

        $summary = [
            'working_days' => 0,
            'present' => 0,
            'late' => 0,
            'leave' => 0,
            'absent' => 0,
            'wfo' => 0,
            'wfh' => 0,
            'wfa' => 0,
        ];

        $presenceDates = [];

        foreach ($presences as $p) {
            $date = Carbon::parse($p->date)->format('Y-m-d');
            $status = $p->status;
            
            $displayName = !$selectedEmployeeId && $isAdmin ? $p->employee->fullname . ': ' : '';

            if ($selectedEmployeeId && $status !== 'absent') {
                $presenceDates[] = $date;
            }

            if ($status === 'present') {
                $summary['present']++;
                if ($p->is_late) {
                    $summary['late']++;
                }

                $wt = strtolower($p->work_type);
                if (isset($summary[$wt])) {
                    $summary[$wt]++;
                }

                $titleStr =
                    ($p->check_in ? Carbon::parse($p->check_in)->format('H:i') : '?') .
                    ' - ' .
                    ($p->check_out ? Carbon::parse($p->check_out)->format('H:i') : '...');
                $color = $p->is_late ? '#ffc107' : '#198754'; // yellow (late) or green (on time)
                $textColor = $p->is_late ? '#000' : '#fff';
                
                $workTypeStr = strtoupper($p->work_type ?? 'WFO');
                $wtColor = match($workTypeStr) {
                    'WFO' => '#0d6efd',
                    'WFH' => '#6c757d',
                    'WFA' => '#212529',
                    default => '#0d6efd',
                };
                
                $events[] = [
                    'title' => $displayName . $workTypeStr,
                    'start' => $date,
                    'color' => $wtColor,
                    'textColor' => '#fff',
                    'allDay' => true,
                    'url' => route('presences.show', $p->id),
                ];
            } elseif ($status === 'leave') {
                $summary['leave']++;
                $titleStr = 'Leave';
                $color = '#0dcaf0';
                $textColor = '#000';
            } else {
                // if explicitly recorded as "absent" in db
                $summary['absent']++;
                $titleStr = 'Absent';
                $color = '#dc3545';
                $textColor = '#fff';
                $presenceDates[] = $date;
            }

            $events[] = [
                'title' => $displayName . $titleStr,
                'start' => $date,
                'color' => $color,
                'textColor' => $textColor,
                'allDay' => true,
                'url' => route('presences.show', $p->id),
            ];
        }

        // check past working days that have not been attended
        if ($selectedEmployeeId) {
            $startDate = Carbon::create($year, $month, 1);
            $endDate = $startDate->copy()->endOfMonth();
            $today = Carbon::today();
            $effectiveEnd = $endDate->greaterThan($today) ? $today : $endDate;

            $summary['working_days'] = HolidayService::getEffectiveWorkingDays(
                $startDate->format('Y-m-d'),
                $endDate->format('Y-m-d'),
            );

            $current = $startDate->copy();
            while ($current <= $effectiveEnd) {
                $dateStr = $current->format('Y-m-d');
                // if not weekend, not holiday, and no attendance/leave record = absent
                if (
                    !$current->isWeekend() &&
                    !in_array($dateStr, $holidayDates) &&
                    !in_array($dateStr, $presenceDates)
                ) {
                    $summary['absent']++;
                    $events[] = [
                        'title' => 'Absent',
                        'start' => $dateStr,
                        'color' => '#dc3545', // dark red
                        'textColor' => '#fff',
                        'allDay' => true,
                    ];
                }
                $current->addDay();
            }
        }

        return view(
            'presences.calendar',
            compact('events', 'holidays', 'year', 'month', 'employees', 'selectedEmployeeId', 'summary', 'isAdmin'),
        );
    }

    // helper method to check if check-in is late
    private function isLateCheckIn($presence)
    {
        try {
            if (!$presence->check_in || $presence->status !== 'present') {
                return false;
            }

            $workType = strtolower($presence->work_type ?? 'wfo');

            // get toggle status according to work type (wfo, wfh, wfa)
            $isLateEnabled = Setting::getValue('enable_late_' . $workType, '0');

            // if disabled from master presence, it is automatically never late
            if ($isLateEnabled == '0') {
                return false;
            }

            $checkInTime = Carbon::parse($presence->check_in, env('APP_TIMEZONE', 'Asia/Jakarta'));
            $dateStr =
                $presence->date instanceof \DateTime ? $presence->date->format('Y-m-d') : (string) $presence->date;

            $workStartTimeStr = Setting::getValue('work_start_time', '08:00');
            $workStartTime = Carbon::parse($dateStr . ' ' . $workStartTimeStr, env('APP_TIMEZONE', 'Asia/Jakarta'));

            // get specific minute tolerance according to the work type
            $lateThreshold = (int) Setting::getValue('late_threshold_' . $workType, 15);

            return $checkInTime->gt($workStartTime->copy()->addMinutes($lateThreshold));
        } catch (\Exception $e) {
            \Log::warning('Error checking late status: ' . $e->getMessage());
            return false;
        }
    }

    public function export(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $query = Presence::with('employee')
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->orderBy('employee_id', 'asc');

        $presences = $query->get();

        $filename = 'Presences_' . $startDate . '_to_' . $endDate . '.csv';
        $handle = fopen('php://memory', 'r+');

        // header
        fputcsv($handle, ['Employee Name', 'Date', 'Check In', 'Check Out', 'Work Type', 'Status', 'Is Late']);

        // data
        foreach ($presences as $presence) {
            fputcsv($handle, [
                $presence->employee->fullname ?? 'Unknown',
                Carbon::parse($presence->date)->format('Y-m-d'),
                $presence->check_in ? Carbon::parse($presence->check_in)->format('H:i:s') : '-',
                $presence->check_out ? Carbon::parse($presence->check_out)->format('H:i:s') : '-',
                $presence->work_type ?? 'WFO',
                ucfirst($presence->status),
                $presence->is_late ? 'Yes' : 'No',
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
