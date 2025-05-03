<?php
// app/Http/Controllers/Chat/TemplateController.php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\Chat\Template;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    /** List all templates */
    public function index()
    {
        return response()->json(Template::all());
    }

    /** Show a single template */
    public function show($id)
    {
        return response()->json(Template::findOrFail($id));
    }

    /** Create a new template */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|unique:templates,name',
            'content'    => 'required|string',
            'approved'   => 'boolean',
        ]);
        $data['created_by'] = auth()->id();
        $template = Template::create($data);
        return response()->json($template, 201);
    }

    /** Update an existing template */
    public function update(Request $request, $id)
    {
        $template = Template::findOrFail($id);
        $data = $request->validate([
            'name'     => 'sometimes|string|unique:templates,name,'.$template->id,
            'content'  => 'sometimes|string',
            'approved' => 'boolean',
        ]);
        $template->update($data);
        return response()->json($template);
    }

    /** Delete a template */
    public function destroy($id)
    {
        Template::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}
