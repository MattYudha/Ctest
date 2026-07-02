<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\CrmContact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        $query = CrmContact::latest();
        
        if (auth()->user()->isSales()) {
            $query->where('created_by', auth()->id());
        }
        
        $contacts = $query->get();
        return view('crm.contacts.index', compact('contacts'));
    }

    public function create()
    {
        return view('crm.contacts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'website_url' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:crm_contacts,email',
            'source' => 'nullable|string|max:255',
        ]);

        $validated['has_website'] = $request->has('has_website');
        $validated['website_url'] = $request->input('website_url') ?: '-';
        $validated['email'] = $request->input('email') ?: null;
        $validated['source'] = $request->input('source') ?: 'user input';
        $validated['created_by'] = auth()->id();

        CrmContact::create($validated);

        return redirect()->route('crm.contacts.index')->with('success', 'New contact successfully added.');
    }

    private function getContactQuery()
    {
        $query = CrmContact::query();
        if (auth()->user()->isSales()) {
            $query->where('created_by', auth()->id());
        }
        return $query;
    }

    public function show($id)
    {
        $contact = $this->getContactQuery()->findOrFail($id);
        return view('crm.contacts.show', compact('contact'));
    }

    public function edit($id)
    {
        $contact = $this->getContactQuery()->findOrFail($id);
        return view('crm.contacts.edit', compact('contact'));
    }

    public function update(Request $request, $id)
    {
        $contact = $this->getContactQuery()->findOrFail($id);

        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'website_url' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:crm_contacts,email,' . $id,
            'source' => 'nullable|string|max:255',
        ]);

        $validated['has_website'] = $request->has('has_website');
        $validated['website_url'] = $request->input('website_url') ?: '-';
        $validated['email'] = $request->input('email') ?: null;
        $validated['source'] = $request->input('source') ?: 'user input';

        $contact->update($validated);

        return redirect()->route('crm.contacts.index')->with('success', 'Contact data successfully updated.');
    }

    public function destroy($id)
    {
        $contact = $this->getContactQuery()->findOrFail($id);
        $contact->delete();

        return redirect()->route('crm.contacts.index')->with('success', 'Contact successfully deleted.');
    }
}
