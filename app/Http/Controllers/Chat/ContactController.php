<?php
// app/Http/Controllers/Chat/ContactController.php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\Chat\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /** List all contacts */
    public function index()
    {
        return response()->json(Contact::all());
    }

    /** Show a single contact */
    public function show($id)
    {
        $contact = Contact::findOrFail($id);
        return response()->json($contact);
    }

    /** Create a new contact */
    public function store(Request $request)
    {
        $data = $request->validate([
            'wa_id'   => 'required|string|unique:contacts,wa_id',
            'name'    => 'nullable|string',
            'metadata'=> 'nullable|array',
        ]);
        $contact = Contact::create($data);
        return response()->json($contact, 201);
    }

    /** Update an existing contact */
    public function update(Request $request, $id)
    {
        $contact = Contact::findOrFail($id);
        $data = $request->validate([
            'wa_id'    => 'sometimes|string|unique:contacts,wa_id,'.$contact->id,
            'name'     => 'nullable|string',
            'metadata' => 'nullable|array',
        ]);
        $contact->update($data);
        return response()->json($contact);
    }

    /** Delete a contact */
    public function destroy($id)
    {
        Contact::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}
