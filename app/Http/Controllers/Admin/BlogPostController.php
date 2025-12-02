<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class BlogPostController extends Controller
{
    public function index(Request $request)
    {
        $query = BlogPost::with(['category', 'author', 'tags']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('blog_category_id', $request->category);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by author
        if ($request->filled('author')) {
            $query->where('user_id', $request->author);
        }

        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $posts = $query->paginate(15);
        $categories = BlogCategory::orderBy('name')->get();

        // Stats
        $stats = [
            'total' => BlogPost::count(),
            'published' => BlogPost::where('status', 'published')->count(),
            'draft' => BlogPost::where('status', 'draft')->count(),
            'scheduled' => BlogPost::where('status', 'scheduled')->count(),
        ];

        return view('admin.blog.posts.index', compact('posts', 'categories', 'stats'));
    }

    public function create()
    {
        $categories = BlogCategory::where('is_active', true)->orderBy('name')->get();
        $tags = BlogTag::orderBy('name')->get();

        return view('admin.blog.posts.create', compact('categories', 'tags'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_posts',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'blog_category_id' => 'nullable|exists:blog_categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:blog_tags,id',
            'status' => 'required|in:draft,published,scheduled',
            'published_at' => 'nullable|date',
            'meta_title' => 'nullable|string|max:70',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:255',
            'is_featured' => 'boolean',
            'allow_comments' => 'boolean',
        ]);

        // Generate slug
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['title']);
        $validated['slug'] = $this->generateUniqueSlug($validated['slug']);

        // Handle checkboxes
        $validated['is_featured'] = $request->has('is_featured');
        $validated['allow_comments'] = $request->has('allow_comments');

        // Set user
        $validated['user_id'] = auth()->id();

        // Handle publish date
        if ($validated['status'] === 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        // Handle featured image
        if ($request->hasFile('featured_image')) {
            $validated['featured_image'] = $request->file('featured_image')
                ->store('blog/posts', 'public');
        }

        $post = BlogPost::create($validated);

        // Sync tags
        if ($request->has('tags')) {
            $post->tags()->sync($request->tags);
        }

        return redirect()->route('admin.blog.posts.index')
            ->with('success', 'Post created successfully.');
    }

    public function show(BlogPost $post)
    {
        $post->load(['category', 'author', 'tags']);
        return view('admin.blog.posts.show', compact('post'));
    }

    public function edit(BlogPost $post)
    {
        $post->load('tags');
        $categories = BlogCategory::where('is_active', true)->orderBy('name')->get();
        $tags = BlogTag::orderBy('name')->get();

        return view('admin.blog.posts.edit', compact('post', 'categories', 'tags'));
    }

    public function update(Request $request, BlogPost $post)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_posts,slug,' . $post->id,
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'blog_category_id' => 'nullable|exists:blog_categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:blog_tags,id',
            'status' => 'required|in:draft,published,scheduled',
            'published_at' => 'nullable|date',
            'meta_title' => 'nullable|string|max:70',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:255',
            'is_featured' => 'boolean',
            'allow_comments' => 'boolean',
        ]);

        // Generate slug
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
            if ($validated['slug'] !== $post->slug) {
                $validated['slug'] = $this->generateUniqueSlug($validated['slug'], $post->id);
            }
        }

        // Handle checkboxes
        $validated['is_featured'] = $request->has('is_featured');
        $validated['allow_comments'] = $request->has('allow_comments');

        // Handle publish date
        if ($validated['status'] === 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        // Handle featured image removal
        if ($request->has('remove_featured_image')) {
            if ($post->featured_image) {
                Storage::disk('public')->delete($post->featured_image);
            }
            $validated['featured_image'] = null;
        }

        // Handle featured image upload
        if ($request->hasFile('featured_image')) {
            if ($post->featured_image) {
                Storage::disk('public')->delete($post->featured_image);
            }
            $validated['featured_image'] = $request->file('featured_image')
                ->store('blog/posts', 'public');
        }

        $post->update($validated);

        // Sync tags
        $post->tags()->sync($request->tags ?? []);

        return redirect()->route('admin.blog.posts.index')
            ->with('success', 'Post updated successfully.');
    }

    public function destroy(BlogPost $post)
    {
        if ($post->featured_image) {
            Storage::disk('public')->delete($post->featured_image);
        }

        $post->tags()->detach();
        $post->delete();

        return redirect()->route('admin.blog.posts.index')
            ->with('success', 'Post deleted successfully.');
    }

    // Upload image from Summernote
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $path = $request->file('image')->store('blog/content', 'public');

        return response()->json([
            'url' => Storage::url($path),
        ]);
    }

    private function generateUniqueSlug(string $slug, ?int $excludeId = null): string
    {
        $original = $slug;
        $count = 1;

        while (true) {
            $query = BlogPost::where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
            if (!$query->exists()) {
                break;
            }
            $slug = $original . '-' . $count++;
        }

        return $slug;
    }
}