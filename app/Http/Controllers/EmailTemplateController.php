<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public function index()
    {
        return response()->json(EmailTemplate::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:email_templates',
            'subject' => 'required|string',
            'body' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $template = EmailTemplate::create($validated);
        return response()->json($template, 201);
    }

    public function show(EmailTemplate $emailTemplate)
    {
        return response()->json($emailTemplate);
    }

    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string',
            'body' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $emailTemplate->update($validated);
        return response()->json($emailTemplate);
    }

    public function destroy(EmailTemplate $emailTemplate)
    {
        $emailTemplate->delete();
        return response()->json(['message' => 'Template supprimé']);
    }
}
