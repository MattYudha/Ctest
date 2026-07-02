<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\CrmWaBlast;
use App\Models\CrmContact;
use Illuminate\Http\Request;
use App\Models\CrmWaBlastRecipient;
use App\Models\LetterTemplate;

class WaBlastController extends Controller
{
    private function getBlastQuery()
    {
        $query = CrmWaBlast::query();
        if (auth()->user()->isSales()) {
            $query->where('created_by', auth()->id());
        }
        return $query;
    }

    public function index()
    {
        $blasts = $this->getBlastQuery()->latest()->paginate(10);
        return view('crm.wa_blasts.index', compact('blasts'));
    }

    public function create(Request $request)
    {
        // Only select contacts with a valid phone number (digits only logic approximation)
        $contactQuery = CrmContact::whereNotNull('phone')
            ->where('phone', '!=', '')
            ->where('phone', '!=', '-');
            
        if (auth()->user()->isSales()) {
            $contactQuery->where('created_by', auth()->id());
        }

        // calculate total contacts with valid phones
        $contactCount = (clone $contactQuery)->count();

        // retrieve valid contact data to populate the checkbox list
        $allContacts = (clone $contactQuery)->get(['id', 'company_name', 'phone']);

        $templates = LetterTemplate::all();

        return view('crm.wa_blasts.create', compact('contactCount', 'allContacts', 'templates'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'campaign_name' => 'required|string|max:255',
            'body_template' => 'required|string',
            'target_type' => 'required|in:all,selected',
        ]);

        $contactQuery = \App\Models\CrmContact::query();
        if (auth()->user()->isSales()) {
            $contactQuery->where('created_by', auth()->id());
        }

        // retrieve the contact list based on the selection
        if ($request->target_type === 'selected') {
            $contactIds = $request->input('contact_ids', []);
            $contacts = $contactQuery->whereIn('id', $contactIds)->get();
        } else {
            // apply the same filter here
            $contacts = $contactQuery->whereNotNull('phone')
                ->where('phone', '!=', '')
                ->where('phone', '!=', '-')
                ->get();
        }

        if ($contacts->isEmpty()) {
            return back()->with('error', 'No valid/selected contacts found with a phone number.');
        }

        // create master blast record
        $blast = CrmWaBlast::create([
            'campaign_name' => $request->campaign_name,
            'body_template' => $request->body_template,
            'status' => 'active',
            'target_count' => $contacts->count(),
            'created_by' => auth()->id(),
        ]);

        // log all targets to recipients with 'pending' status
        $recipientsData = [];
        $now = now();
        foreach ($contacts as $contact) {
            $recipientsData[] = [
                'wa_blast_id' => $blast->id,
                'contact_id' => $contact->id,
                'phone_number' => $contact->phone,
                'status' => 'pending',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        // bulk insert for performance
        CrmWaBlastRecipient::insert($recipientsData);

        return redirect()
            ->route('crm.wa-blasts.show', $blast->id)
            ->with('success', 'WA Blast campaign created. You can now start sending messages.');
    }

    public function show(Request $request, $id)
    {
        $blast = $this->getBlastQuery()->with(['recipients.contact', 'creator'])->findOrFail($id);

        return view('crm.wa_blasts.show', compact('blast'));
    }

    public function markAsSent(Request $request, $id)
    {
        $request->validate([
            'recipient_id' => 'required|exists:crm_wa_blast_recipients,id'
        ]);

        $blast = $this->getBlastQuery()->findOrFail($id);
        
        $recipient = CrmWaBlastRecipient::where('wa_blast_id', $blast->id)
            ->findOrFail($request->recipient_id);

        $newStatus = $request->input('status', 'sent');
        $newStatus = in_array($newStatus, ['sent', 'failed']) ? $newStatus : 'sent';

        if ($recipient->status === 'pending') {
            $recipient->update(['status' => $newStatus]);
            
            // update master count (represents processed count)
            $blast->increment('sent_count');
            
            if ($blast->sent_count >= $blast->target_count) {
                $blast->update(['status' => 'completed']);
            }
        } elseif ($recipient->status === 'sent' && $newStatus === 'failed') {
            // If already marked as sent but user marks it as failed later
            $recipient->update(['status' => 'failed']);
            // no need to increment sent_count as it was already incremented when it was marked sent
        }

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $blast = $this->getBlastQuery()->findOrFail($id);
        $blast->delete();

        return redirect()
            ->route('crm.wa-blasts.index')
            ->with('success', 'WA blast campaign successfully deleted.');
    }
}
