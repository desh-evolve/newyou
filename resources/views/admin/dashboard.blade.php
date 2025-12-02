@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content-header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0">Dashboard</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item active">Dashboard</li>
            </ol>
        </div>
    </div>
@endsection

@section('content')
    <!-- Stats Cards Row 1 -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['total_posts'] }}</h3>
                    <p>Total Posts</p>
                </div>
                <div class="icon"><i class="fas fa-file-alt"></i></div>
                <a href="{{ route('admin.blog.posts.index') }}" class="small-box-footer">View All <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['published_posts'] }}</h3>
                    <p>Published</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
                <a href="{{ route('admin.blog.posts.index', ['status' => 'published']) }}" class="small-box-footer">View <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['categories'] }}</h3>
                    <p>Categories</p>
                </div>
                <div class="icon"><i class="fas fa-folder"></i></div>
                <a href="{{ route('admin.blog.categories.index') }}" class="small-box-footer">Manage <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $stats['tags'] }}</h3>
                    <p>Tags</p>
                </div>
                <div class="icon"><i class="fas fa-tags"></i></div>
                <a href="{{ route('admin.blog.tags.index') }}" class="small-box-footer">Manage <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>

    <!-- Stats Cards Row 2 -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ $stats['draft_posts'] }}</h3>
                    <p>Drafts</p>
                </div>
                <div class="icon"><i class="fas fa-edit"></i></div>
                <a href="{{ route('admin.blog.posts.index', ['status' => 'draft']) }}" class="small-box-footer">View <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $stats['scheduled_posts'] }}</h3>
                    <p>Scheduled</p>
                </div>
                <div class="icon"><i class="fas fa-clock"></i></div>
                <a href="{{ route('admin.blog.posts.index', ['status' => 'scheduled']) }}" class="small-box-footer">View <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-teal">
                <div class="inner">
                    <h3>{{ number_format($stats['total_views']) }}</h3>
                    <p>Total Views</p>
                </div>
                <div class="icon"><i class="fas fa-eye"></i></div>
                <span class="small-box-footer">&nbsp;</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-purple">
                <div class="inner">
                    <h3>{{ $stats['users'] }}</h3>
                    <p>Users</p>
                </div>
                <div class="icon"><i class="fas fa-users"></i></div>
                <a href="{{ route('admin.users.index') }}" class="small-box-footer">Manage <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Posts -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-clock mr-2"></i>Recent Posts</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.blog.posts.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> New Post
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentPosts as $post)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.blog.posts.show', $post) }}">{{ Str::limit($post->title, 40) }}</a>
                                    </td>
                                    <td>
                                        @if($post->category)
                                            <span class="badge badge-info">{{ $post->category->name }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($post->status == 'published')
                                            <span class="badge badge-success">Published</span>
                                        @elseif($post->status == 'draft')
                                            <span class="badge badge-warning">Draft</span>
                                        @else
                                            <span class="badge badge-primary">Scheduled</span>
                                        @endif
                                    </td>
                                    <td>{{ $post->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <a href="{{ route('admin.blog.posts.edit', $post) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <p class="text-muted mb-2">No posts yet.</p>
                                        <a href="{{ route('admin.blog.posts.create') }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-plus mr-1"></i> Create First Post
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($recentPosts->count() > 0)
                    <div class="card-footer text-center">
                        <a href="{{ route('admin.blog.posts.index') }}">View All Posts</a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-bolt mr-2"></i>Quick Actions</h3>
                </div>
                <div class="card-body">
                    <a href="{{ route('admin.blog.posts.create') }}" class="btn btn-primary btn-block mb-2">
                        <i class="fas fa-plus mr-1"></i> New Post
                    </a>
                    <a href="{{ route('admin.blog.categories.create') }}" class="btn btn-info btn-block mb-2">
                        <i class="fas fa-folder-plus mr-1"></i> New Category
                    </a>
                    <a href="{{ route('admin.blog.tags.index') }}" class="btn btn-secondary btn-block">
                        <i class="fas fa-tags mr-1"></i> Manage Tags
                    </a>
                </div>
            </div>

            <!-- Draft Posts -->
            <div class="card">
                <div class="card-header bg-warning">
                    <h3 class="card-title"><i class="fas fa-edit mr-2"></i>Drafts</h3>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($draftPosts as $post)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <a href="{{ route('admin.blog.posts.edit', $post) }}">{{ Str::limit($post->title, 30) }}</a>
                                <small class="text-muted">{{ $post->created_at->diffForHumans() }}</small>
                            </li>
                        @empty
                            <li class="list-group-item text-center text-muted py-3">No drafts</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <!-- Popular Posts -->
            <div class="card">
                <div class="card-header bg-info">
                    <h3 class="card-title"><i class="fas fa-fire mr-2"></i>Most Viewed</h3>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($popularPosts as $post)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <a href="{{ route('admin.blog.posts.show', $post) }}">{{ Str::limit($post->title, 25) }}</a>
                                <span class="badge badge-info">{{ number_format($post->views) }}</span>
                            </li>
                        @empty
                            <li class="list-group-item text-center text-muted py-3">No posts yet</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection