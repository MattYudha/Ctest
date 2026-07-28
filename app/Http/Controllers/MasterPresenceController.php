<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\OfficeLocation;
use App\Models\Presence;

class MasterPresenceController extends Controller
{
    public function index()
    {
        $settings = [
            'work_start_time' => Setting::getValue('work_start_time', '08:00'),
            'enable_late_wfo' => Setting::getValue('enable_late_wfo', '1'),
            'late_threshold_wfo' => Setting::getValue('late_threshold_wfo', '15'),

            'enable_late_wfh' => Setting::getValue('enable_late_wfh', '0'),
            'late_threshold_wfh' => Setting::getValue('late_threshold_wfh', '15'),

            'enable_late_wfa' => Setting::getValue('enable_late_wfa', '0'),
            'late_threshold_wfa' => Setting::getValue('late_threshold_wfa', '15'),

            'enable_max_checkout_wfo' => Setting::getValue('enable_max_checkout_wfo', '0'),
            'max_checkout_time_wfo' => Setting::getValue('max_checkout_time_wfo', '17:30'),
            'enable_max_checkout_wfh' => Setting::getValue('enable_max_checkout_wfh', '0'),
            'max_checkout_time_wfh' => Setting::getValue('max_checkout_time_wfh', '17:30'),
            'enable_max_checkout_wfa' => Setting::getValue('enable_max_checkout_wfa', '0'),
            'max_checkout_time_wfa' => Setting::getValue('max_checkout_time_wfa', '17:30'),

            'overtime_rate_per_hour' => Setting::getValue('overtime_rate_per_hour', '50000'),
        ];

        return view('master-presences.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'min_wfo_full_time' => 'required|integer|min:0',
            'min_wfo_part_time' => 'required|integer|min:0',
            'work_start_time' => 'required|date_format:H:i',
            'enable_late_wfo' => 'required|in:0,1',
            'late_threshold_wfo' => 'nullable|integer|min:0',
            'enable_late_wfh' => 'required|in:0,1',
            'late_threshold_wfh' => 'nullable|integer|min:0',
            'enable_late_wfa' => 'required|in:0,1',
            'late_threshold_wfa' => 'nullable|integer|min:0',
            'enable_max_checkout_wfo' => 'required|in:0,1',
            'max_checkout_time_wfo' => 'nullable|date_format:H:i',
            'enable_max_checkout_wfh' => 'required|in:0,1',
            'max_checkout_time_wfh' => 'nullable|date_format:H:i',
            'enable_max_checkout_wfa' => 'required|in:0,1',
            'max_checkout_time_wfa' => 'nullable|date_format:H:i',
            'overtime_rate_per_hour' => 'nullable|integer|min:0',
        ]);

        Setting::updateOrCreate(['key' => 'min_wfo_full_time'], ['value' => $request->min_wfo_full_time]);
        Setting::updateOrCreate(['key' => 'min_wfo_part_time'], ['value' => $request->min_wfo_part_time]);

        foreach ($request->except(['_token', '_method']) as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return redirect()->back()->with('success', 'Pengaturan Master Presence berhasil diperbarui.');
    }

    public function createPresence()
    {
        $employees = Employee::where('status', 'active')->orderBy('fullname')->get();

        $offices = OfficeLocation::orderBy('name')->get();

        return view('master-presences.create_presence', compact('employees', 'offices'));
    }

    public function storePresence(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'work_type' => 'required|string|in:WFO,WFH,WFA',
            'office_location_id' => 'required_if:work_type,WFO|nullable|exists:office_locations,id',
            'status' => 'required|string|in:present,late,absent,leave',
            'check_in_time' => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i',
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
        ]);

        // get default coordinates from selected office if WFO
        $office = null;
        if ($request->work_type === 'WFO' && $request->office_location_id) {
            $office = OfficeLocation::find($request->office_location_id);
        }

        // Determine times
        $checkIn = null;
        $checkOut = null;
        if ($request->status !== 'absent') {
            $checkInTime = $request->check_in_time ?? '09:00:00';
            $checkOutTime = $request->check_out_time ?? '17:00:00';

            // Ensure H:i:s format if only H:i is provided
            if (strlen($checkInTime) == 5) $checkInTime .= ':00';
            if (strlen($checkOutTime) == 5) $checkOutTime .= ':00';

            $checkIn = $request->date . ' ' . $checkInTime;
            $checkOut = $request->date . ' ' . $checkOutTime;
        }

        // Determine coordinates
        $latitude = $request->latitude ?? ($office->latitude ?? '0.000000');
        $longitude = $request->longitude ?? ($office->longitude ?? '0.000000');

        // create manual attendance record without photo or fingerprint validation
        Presence::updateOrCreate(
            [
                // search parameter - employee & date
                'employee_id' => $request->employee_id,
                'date' => $request->date,
            ],
            [
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'check_out_latitude' => $latitude,
                'check_out_longitude' => $longitude,
                'office_location_id' => $request->work_type === 'WFO' ? $request->office_location_id : null,
                'work_type' => $request->work_type,
                'status' => $request->status,
                'is_late' => 0,
                // use default system image
                'photo_path' => 'assets/images/default/admin-manual-presence.png',
                'notes' => 'Manually created/updated by admin (Correction/Override)',
            ],
        );

        return redirect()
            ->route('master-presences.index')
            ->with('success', 'Manual attendance record created successfully.');
    }
}
