<?php
// app/Http/Controllers/Chat/TagController.php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\Chat\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    /** List all tags */
    public function index()
    {
        return response()->json(Tag::all());
    }

    /** Show a single tag */
    public function show($id)
    {
        return response()->json(Tag::findOrFail($id));
    }

    /** Create a new tag */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'  => 'required|string|unique:tags,name',
            'color' => 'nullable|string|size:7', // hex color
        ]);
        $tag = Tag::create($data);
        return response()->json($tag, 201);
    }

    /** Update an existing tag */
    public function update(Request $request, $id)
    {
        $tag = Tag::findOrFail($id);
        $data = $request->validate([
            'name'  => 'sometimes|string|unique:tags,name,'.$tag->id,
            'color' => 'nullable|string|size:7',
        ]);
        $tag->update($data);
        return response()->json($tag);
    }

    /** Delete a tag */
    public function destroy($id)
    {
        Tag::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}
