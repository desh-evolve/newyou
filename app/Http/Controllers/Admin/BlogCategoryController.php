<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class BlogCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = BlogCategory::withCount('posts');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $categories = $query->orderBy('order')->orderBy('name')->paginate(15);

        return view('admin.blog.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.blog.categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_categories',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['is_active'] = $request->has('is_active');

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('blog/categories', 'public');
        }

        BlogCategory::create($validated);

        return redirect()->route('admin.blog.categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit(BlogCategory $category)
    {
        return view('admin.blog.categories.edit', compact('category'));
    }

    public function update(Request $request, BlogCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_categories,slug,' . $category->id,
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['is_active'] = $request->has('is_active');

        if ($request->hasFile('image')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $validated['image'] = $request->file('image')->store('blog/categories', 'public');
        }

        if ($request->has('remove_image') && $category->image) {
            Storage::disk('public')->delete($category->image);
            $validated['image'] = null;
        }

        $category->update($validated);

        return redirect()->route('admin.blog.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(BlogCategory $category)
    {
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        return redirect()->route('admin.blog.categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}