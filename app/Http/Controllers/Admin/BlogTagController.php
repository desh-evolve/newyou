<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogTag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogTagController extends Controller
{
    public function index(Request $request)
    {
        $query = BlogTag::withCount('posts');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $tags = $query->orderBy('name')->paginate(20);

        return view('admin.blog.tags.index', compact('tags'));
    }

    public function create()
    {
        return view('admin.blog.tags.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_tags',
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);

        BlogTag::create($validated);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'tag' => BlogTag::latest()->first()]);
        }

        return redirect()->route('admin.blog.tags.index')
            ->with('success', 'Tag created successfully.');
    }

    public function edit(BlogTag $tag)
    {
        return view('admin.blog.tags.edit', compact('tag'));
    }

    public function update(Request $request, BlogTag $tag)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_tags,slug,' . $tag->id,
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);

        $tag->update($validated);

        return redirect()->route('admin.blog.tags.index')
            ->with('success', 'Tag updated successfully.');
    }

    public function destroy(BlogTag $tag)
    {
        $tag->posts()->detach();
        $tag->delete();

        return redirect()->route('admin.blog.tags.index')
            ->with('success', 'Tag deleted successfully.');
    }
}