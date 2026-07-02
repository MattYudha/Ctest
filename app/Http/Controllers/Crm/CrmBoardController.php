<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CrmContact;
use App\Models\CrmDeal;
use Illuminate\Support\Facades\DB;

class CrmBoardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isSales = $user->isSales();
        $filterUserId = $request->query('user_id');

        $dealQuery = CrmDeal::with('contact')->orderBy('order')->orderBy('created_at', 'desc');
        $contactQuery = CrmContact::orderBy('company_name');

        if ($isSales) {
            $dealQuery->where('created_by', $user->id);
            $contactQuery->where('created_by', $user->id);
        } elseif ($filterUserId) {
            $dealQuery->where('created_by', $filterUserId);
            $contactQuery->where('created_by', $filterUserId);
        }

        // Get deals grouped by status
        $deals = $dealQuery->get()->groupBy('status');
        $contacts = $contactQuery->get();

        // Get sales users for filter dropdown if not sales
        $salesUsers = [];
        if (!$isSales) {
            $salesUsers = \App\Models\User::whereHas('employee.role', function($q) {
                $q->where('title', \App\Constants\Roles::SALES);
            })->get();
        }

        return view('crm.board.index', compact('deals', 'contacts', 'salesUsers', 'isSales', 'filterUserId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'crm_contact_id' => 'nullable|exists:crm_contacts,id',
            'value' => 'nullable|numeric',
            'status' => 'required|string'
        ]);

        $validated['created_by'] = auth()->id();
        
        // Get max order for this status
        $maxOrder = CrmDeal::where('status', $validated['status'])->max('order');
        $validated['order'] = $maxOrder !== null ? $maxOrder + 1 : 0;

        CrmDeal::create($validated);

        return redirect()->route('crm.board.index')->with('success', 'New deal/task added successfully.');
    }

    public function update(Request $request, $id)
    {
        $deal = CrmDeal::findOrFail($id);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'crm_contact_id' => 'nullable|exists:crm_contacts,id',
            'value' => 'nullable|numeric',
        ]);

        $deal->update($validated);

        return redirect()->route('crm.board.index')->with('success', 'Deal/Task updated successfully.');
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'deal_id' => 'required|exists:crm_deals,id',
            'new_status' => 'required|string',
            'new_order' => 'required|array'
        ]);

        DB::beginTransaction();
        try {
            // Update the dragged deal's status
            $deal = CrmDeal::findOrFail($request->deal_id);
            $deal->status = $request->new_status;
            $deal->save();

            // Update orders for all items in the new status column
            foreach ($request->new_order as $index => $id) {
                CrmDeal::where('id', $id)->update(['order' => $index]);
            }

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    public function destroy($id)
    {
        $deal = CrmDeal::findOrFail($id);
        $deal->delete();
        
        return redirect()->route('crm.board.index')->with('success', 'Deal/Task deleted successfully.');
    }
}
