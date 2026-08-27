<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\Department;
use App\Services\KPICalculationService;
use Illuminate\Support\Facades\Auth;
use App\Constants\Roles;

class KPIMasterController extends Controller
{
    public function index()
    {
        $config = KPICalculationService::getMasterKPIConfig();
        $roles = \App\Models\Role::all();
        
        // Convert applicable_roles to easily checked format for the frontend
        foreach ($config as &$ind) {
            $ind['is_all'] = in_array('*', $ind['applicable_roles'] ?? []);
        }

        return view('kpi.master', compact('config', 'roles'));
    }

    public function publish(Request $request)
    {
        $request->validate([
            'indicators' => 'required|array',
            'indicators.*.key' => 'required|string',
            'indicators.*.weight' => 'required|integer|min:0|max:100',
            'indicators.*.applicable_roles' => 'nullable|array',
        ]);

        $indicators = $request->input('indicators');
        $totalWeight = 0;
        
        $newConfig = [];
        $defaultConfig = KPICalculationService::getMasterKPIConfig();

        foreach ($indicators as $ind) {
            $weight = (int)$ind['weight'];
            $totalWeight += $weight;
            
            $label = '';
            $desc = '';
            foreach ($defaultConfig as $def) {
                if ($def['key'] === $ind['key']) {
                    $label = $def['label'] ?? '';
                    $desc = $def['desc'] ?? '';
                    break;
                }
            }
            
            // If applicable_roles contains '*', it applies to all.
            $appRole = $ind['applicable_roles'] ?? ['*'];
            if (in_array('*', $appRole)) {
                $appRole = ['*'];
            }

            $newConfig[] = [
                'key' => $ind['key'],
                'label' => $label,
                'desc' => $desc,
                'weight' => $weight,
                'applicable_roles' => $appRole,
            ];
        }

        if ($totalWeight !== 100) {
            return redirect()->back()->with('error', 'Total bobot harus tepat 100%. Saat ini: ' . $totalWeight . '%')->withInput();
        }

        Setting::updateOrCreate(
            ['key' => 'kpi_master_config'],
            ['value' => json_encode($newConfig)]
        );

        return redirect()->route('kpi-masters.index')->with('success', 'Konfigurasi Master KPI berhasil diperbarui.');
    }

    public function reset()
    {
        Setting::where('key', 'kpi_master_config')->delete();
        return redirect()->route('kpi-masters.index')->with('success', 'Konfigurasi Master KPI berhasil direset ke default.');
    }
}
