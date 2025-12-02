<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_posts' => BlogPost::count(),
            'published_posts' => BlogPost::where('status', 'published')->count(),
            'draft_posts' => BlogPost::where('status', 'draft')->count(),
            'scheduled_posts' => BlogPost::where('status', 'scheduled')->count(),
            'categories' => BlogCategory::count(),
            'tags' => BlogTag::count(),
            'users' => User::count(),
            'total_views' => BlogPost::sum('views'),
        ];

        $recentPosts = BlogPost::with('category', 'author')
            ->latest()
            ->take(5)
            ->get();

        $draftPosts = BlogPost::where('status', 'draft')
            ->latest()
            ->take(5)
            ->get();

        $popularPosts = BlogPost::where('status', 'published')
            ->orderBy('views', 'desc')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentPosts', 'draftPosts', 'popularPosts'));
    }
}