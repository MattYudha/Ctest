<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CrmContact;
use App\Models\CrmDeal;

class CrmDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isSales = $user->isSales();
        $filterUserId = $request->query('user_id');

        $contactQuery = CrmContact::query();
        $dealQuery = CrmDeal::query();

        if ($isSales) {
            $contactQuery->where('created_by', $user->id);
            $dealQuery->where('created_by', $user->id);
        } elseif ($filterUserId) {
            $contactQuery->where('created_by', $filterUserId);
            $dealQuery->where('created_by', $filterUserId);
        }

        $totalContacts = (clone $contactQuery)->count();
        $totalDeals = (clone $dealQuery)->count();
        $totalValue = (clone $dealQuery)->sum('value');
        $wonValue = (clone $dealQuery)->where('status', 'won')->sum('value');

        // Group deals by status
        $dealsByStatus = (clone $dealQuery)->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
            
        $recentDeals = (clone $dealQuery)->with(['contact', 'creator'])->latest()->take(5)->get();

        // Get sales users for filter dropdown if not sales
        $salesUsers = [];
        if (!$isSales) {
            $salesUsers = \App\Models\User::whereHas('employee.role', function($q) {
                $q->where('title', \App\Constants\Roles::SALES);
            })->get();
        }

        return view('crm.dashboard.index', compact('totalContacts', 'totalDeals', 'totalValue', 'wonValue', 'dealsByStatus', 'recentDeals', 'salesUsers', 'isSales', 'filterUserId'));
    }
}
