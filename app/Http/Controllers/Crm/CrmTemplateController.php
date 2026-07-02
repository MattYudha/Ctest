<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CrmTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $templates = \App\Models\CrmTemplate::latest()->get();
        return view('crm.templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('crm.templates.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:email,wa',
            'subject' => 'required_if:type,email|nullable|string|max:255',
            'body' => 'required|string',
        ]);

        \App\Models\CrmTemplate::create([
            'name' => $request->name,
            'type' => $request->type,
            'subject' => $request->type === 'email' ? $request->subject : null,
            'body' => $request->body,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('crm.templates.index')->with('success', 'Template created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $template = \App\Models\CrmTemplate::findOrFail($id);
        return view('crm.templates.show', compact('template'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $template = \App\Models\CrmTemplate::findOrFail($id);
        return view('crm.templates.edit', compact('template'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:email,wa',
            'subject' => 'required_if:type,email|nullable|string|max:255',
            'body' => 'required|string',
        ]);

        $template = \App\Models\CrmTemplate::findOrFail($id);
        
        $template->update([
            'name' => $request->name,
            'type' => $request->type,
            'subject' => $request->type === 'email' ? $request->subject : null,
            'body' => $request->body,
        ]);

        return redirect()->route('crm.templates.index')->with('success', 'Template updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $template = \App\Models\CrmTemplate::findOrFail($id);
        $template->delete();

        return redirect()->route('crm.templates.index')->with('success', 'Template deleted successfully.');
    }
}
