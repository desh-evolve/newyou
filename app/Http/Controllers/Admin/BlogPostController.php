<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BlogPostController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = BlogPost::with(['category', 'author', 'tags'])->where('status', '!=', 'delete');

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
        $categories = BlogCategory::where('status', 'active')->orderBy('name')->get();

        // Stats
        $stats = [
            'total' => BlogPost::where('status', '!=', 'delete')->count(),
            'published' => BlogPost::where('status', 'published')->count(),
            'draft' => BlogPost::where('status', 'draft')->count(),
            'scheduled' => BlogPost::where('status', 'scheduled')->count(),
        ];

        return view('admin.blog.posts.index', compact('posts', 'categories', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = BlogCategory::where('status', 'active')->orderBy('name')->get();
        $tags = BlogTag::where('status', 'active')->orderBy('name')->get();

        return view('admin.blog.posts.create', compact('categories', 'tags'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
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
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Generate slug
        $slug = $request->slug ?? Str::slug($request->title);
        $slug = $this->generateUniqueSlug($slug);

        // Handle publish date
        $publishedAt = null;
        if ($request->status === 'published' && empty($request->published_at)) {
            $publishedAt = now();
        } elseif ($request->published_at) {
            $publishedAt = $request->published_at;
        }

        $data = [
            'title' => $request->title,
            'slug' => $slug,
            'excerpt' => $request->excerpt,
            'content' => $request->content,
            'blog_category_id' => $request->blog_category_id,
            'user_id' => Auth::id(),
            'status' => $request->status,
            'published_at' => $publishedAt,
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'meta_keywords' => $request->meta_keywords,
            'is_featured' => $request->has('is_featured') ? 1 : 0,
            'allow_comments' => $request->has('allow_comments') ? 1 : 0,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ];

        // Handle featured image
        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')->store('blog/posts', 'public');
        }

        $post = BlogPost::create($data);

        // Sync tags
        if ($request->has('tags')) {
            $post->tags()->attach($request->tags);
        }

        return redirect()->route('admin.blog.posts.index')
            ->with('success', 'Post created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(BlogPost $post)
    {
        if ($post->status === 'delete') {
            abort(404);
        }

        $post->load(['category', 'author', 'tags']);
        return view('admin.blog.posts.show', compact('post'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BlogPost $post)
    {
        if ($post->status === 'delete') {
            abort(404);
        }

        $post->load('tags');
        $categories = BlogCategory::where('status', 'active')->orderBy('name')->get();
        $tags = BlogTag::where('status', 'active')->orderBy('name')->get();
        $postTags = $post->tags->pluck('id')->toArray();

        return view('admin.blog.posts.edit', compact('post', 'categories', 'tags', 'postTags'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BlogPost $post)
    {
        if ($post->status === 'delete') {
            abort(404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
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
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Generate slug
        $slug = $request->slug ?? Str::slug($request->title);
        if ($slug !== $post->slug) {
            $slug = $this->generateUniqueSlug($slug, $post->id);
        }

        // Handle publish date
        $publishedAt = $post->published_at;
        if ($request->status === 'published' && empty($request->published_at) && empty($post->published_at)) {
            $publishedAt = now();
        } elseif ($request->published_at) {
            $publishedAt = $request->published_at;
        }

        $data = [
            'title' => $request->title,
            'slug' => $slug,
            'excerpt' => $request->excerpt,
            'content' => $request->content,
            'blog_category_id' => $request->blog_category_id,
            'status' => $request->status,
            'published_at' => $publishedAt,
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'meta_keywords' => $request->meta_keywords,
            'is_featured' => $request->has('is_featured') ? 1 : 0,
            'allow_comments' => $request->has('allow_comments') ? 1 : 0,
            'updated_by' => Auth::id(),
        ];

        // Handle featured image removal
        if ($request->has('remove_featured_image') && $post->featured_image) {
            Storage::disk('public')->delete($post->featured_image);
            $data['featured_image'] = null;
        }

        // Handle featured image upload
        if ($request->hasFile('featured_image')) {
            if ($post->featured_image) {
                Storage::disk('public')->delete($post->featured_image);
            }
            $data['featured_image'] = $request->file('featured_image')->store('blog/posts', 'public');
        }

        $post->update($data);

        // Sync tags
        $post->tags()->sync($request->tags ?? []);

        return redirect()->route('admin.blog.posts.index')
            ->with('success', 'Post updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BlogPost $post)
    {
        if ($post->status === 'delete') {
            abort(404);
        }

        $post->update([
            'status' => 'delete',
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('admin.blog.posts.index')
            ->with('success', 'Post deleted successfully.');
    }

    /**
     * Upload image from Summernote editor.
     */
    public function uploadImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $path = $request->file('image')->store('blog/content', 'public');

        return response()->json([
            'url' => Storage::url($path),
        ]);
    }

    /**
     * Generate unique slug for blog post.
     */
    private function generateUniqueSlug(string $slug, ?int $excludeId = null): string
    {
        $original = $slug;
        $count = 1;

        while (true) {
            $query = BlogPost::where('slug', $slug)->where('status', '!=', 'delete');
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