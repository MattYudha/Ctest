<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\CrmEmailBlast;
use App\Models\CrmContact;
use App\Jobs\ProcessEmailBlast;
use Illuminate\Http\Request;
use App\Models\CrmEmailBlastRecipient;
use App\Models\LetterTemplate;

class EmailBlastController extends Controller
{
    private function getBlastQuery()
    {
        $query = CrmEmailBlast::query();
        if (auth()->user()->isSales()) {
            $query->where('created_by', auth()->id());
        }
        return $query;
    }

    public function index()
    {
        $blasts = $this->getBlastQuery()->latest()->paginate(10);
        return view('crm.email_blasts.index', compact('blasts'));
    }

    public function create(Request $request)
    {
        $contactQuery = CrmContact::whereNotNull('email')
            ->where('email', '!=', '')
            ->where('email', '!=', '-')
            ->where('email', 'LIKE', '%@%');
            
        if (auth()->user()->isSales()) {
            $contactQuery->where('created_by', auth()->id());
        }

        // calculate total contacts with valid emails
        $contactCount = (clone $contactQuery)->count();

        // retrieve valid contact data to populate the checkbox list
        $allContacts = (clone $contactQuery)->get(['id', 'company_name', 'email']);

        $templates = \App\Models\CrmTemplate::where('type', 'email')->get();

        return view('crm.email_blasts.create', compact('contactCount', 'allContacts', 'templates'));
    }

    public function show(Request $request, $id)
    {
        $blast = $this->getBlastQuery()->with(['recipients.contact', 'creator'])->findOrFail($id);

        // response for real-time ajax polling
        if ($request->ajax()) {
            $sentCount = $blast->recipients->where('status', 'sent')->count();
            $failedCount = $blast->recipients->where('status', 'failed')->count();

            return response()->json([
                'status' => $blast->status,
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
                'recipients' => $blast->recipients->map(function ($r) {
                    return [
                        'id' => $r->id,
                        'status' => $r->status,
                        'error_message' => $r->error_message,
                    ];
                }),
            ]);
        }

        return view('crm.email_blasts.show', compact('blast'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
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
            $contacts = $contactQuery->whereNotNull('email')
                ->where('email', '!=', '')
                ->where('email', '!=', '-')
                ->where('email', 'LIKE', '%@%')
                ->get();
        }

        if ($contacts->isEmpty()) {
            return back()->with('error', 'No valid/selected contacts found.');
        }

        // create master blast record
        $blast = CrmEmailBlast::create([
            'subject' => $request->subject,
            'body' => $request->body,
            'status' => 'draft',
            'target_count' => $contacts->count(),
            'created_by' => auth()->id(),
        ]);

        // log all targets to recipients with 'pending' status
        $recipientsData = [];
        $now = now();
        foreach ($contacts as $contact) {
            $recipientsData[] = [
                'email_blast_id' => $blast->id,
                'contact_id' => $contact->id,
                'email' => $contact->email,
                'status' => 'pending',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        // bulk insert for performance
        CrmEmailBlastRecipient::insert($recipientsData);

        // dispatch to queue
        ProcessEmailBlast::dispatch($blast);

        return redirect()
            ->route('crm.email-blasts.show', $blast->id)
            ->with('success', 'Email blast processing started.');
    }

    public function destroy($id)
    {
        $blast = $this->getBlastQuery()->findOrFail($id);

        // prevent deletion if currently processing
        if ($blast->status === 'processing') {
            return redirect()
                ->route('crm.email-blasts.index')
                ->with(
                    'error',
                    'System rejected! You cannot delete an Email Blast that is currently in the delivery process.',
                );
        }

        // if safe (draft or completed), proceed with deletion
        $blast->delete();

        return redirect()
            ->route('crm.email-blasts.index')
            ->with('success', 'Email blast history successfully deleted from the system.');
    }
}
