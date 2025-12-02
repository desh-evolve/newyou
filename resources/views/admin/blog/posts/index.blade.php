@extends('layouts.admin')

@section('title', 'Blog Posts')

@section('content-header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0">Blog Posts</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Posts</li>
            </ol>
        </div>
    </div>
@endsection

@section('content')
    <!-- Stats Cards -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['total'] }}</h3>
                    <p>Total Posts</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-alt"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['published'] }}</h3>
                    <p>Published</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['draft'] }}</h3>
                    <p>Drafts</p>
                </div>
                <div class="icon">
                    <i class="fas fa-edit"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $stats['scheduled'] }}</h3>
                    <p>Scheduled</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Posts</h3>
            <div class="card-tools">
                <a href="{{ route('admin.blog.posts.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus mr-1"></i> New Post
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <form action="{{ route('admin.blog.posts.index') }}" method="GET" class="mb-4">
                <div class="row">
                    <div class="col-md-3">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Search posts..." value="{{ request('search') }}">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-default">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <select name="category" class="form-control" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-control" onchange="this.form.submit()">
                            <option value="">All Status</option>
                            <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="sort" class="form-control" onchange="this.form.submit()">
                            <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Latest</option>
                            <option value="title" {{ request('sort') == 'title' ? 'selected' : '' }}>Title</option>
                            <option value="views" {{ request('sort') == 'views' ? 'selected' : '' }}>Most Views</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('admin.blog.posts.index') }}" class="btn btn-default btn-block">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>
                </div>
            </form>

            <!-- Posts Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th style="width: 80px;">Image</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Author</th>
                            <th style="width: 100px;">Status</th>
                            <th style="width: 80px;">Views</th>
                            <th style="width: 120px;">Date</th>
                            <th style="width: 130px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($posts as $post)
                            <tr>
                                <td>{{ $post->id }}</td>
                                <td>
                                    @if($post->featured_image)
                                        <img src="{{ Storage::url($post->featured_image) }}" alt="{{ $post->title }}" class="img-thumbnail" style="width: 60px; height: 45px; object-fit: cover;">
                                    @else
                                        <div class="bg-secondary d-flex align-items-center justify-content-center" style="width: 60px; height: 45px;">
                                            <i class="fas fa-image text-white"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.blog.posts.show', $post) }}" class="font-weight-bold text-dark">
                                        {{ Str::limit($post->title, 50) }}
                                    </a>
                                    @if($post->is_featured)
                                        <i class="fas fa-star text-warning ml-1" title="Featured"></i>
                                    @endif
                                    <br>
                                    <small class="text-muted">
                                        @foreach($post->tags->take(3) as $tag)
                                            <span class="badge badge-light">{{ $tag->name }}</span>
                                        @endforeach
                                        @if($post->tags->count() > 3)
                                            <span class="badge badge-light">+{{ $post->tags->count() - 3 }}</span>
                                        @endif
                                    </small>
                                </td>
                                <td>
                                    @if($post->category)
                                        <span class="badge badge-info">{{ $post->category->name }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $post->author->name ?? 'Unknown' }}</td>
                                <td>
                                    @switch($post->status)
                                        @case('published')
                                            <span class="badge badge-success">Published</span>
                                            @break
                                        @case('draft')
                                            <span class="badge badge-warning">Draft</span>
                                            @break
                                        @case('scheduled')
                                            <span class="badge badge-primary">Scheduled</span>
                                            @break
                                    @endswitch
                                </td>
                                <td>{{ number_format($post->views) }}</td>
                                <td>
                                    <small>
                                        {{ $post->published_at ? $post->published_at->format('M d, Y') : $post->created_at->format('M d, Y') }}
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.blog.posts.show', $post) }}" class="btn btn-default" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.blog.posts.edit', $post) }}" class="btn btn-info" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger" title="Delete" onclick="confirmDelete({{ $post->id }})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <form id="delete-form-{{ $post->id }}" action="{{ route('admin.blog.posts.destroy', $post) }}" method="POST" class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <i class="fas fa-file-alt fa-4x text-muted mb-3"></i>
                                    <p class="text-muted mb-3">No posts found.</p>
                                    <a href="{{ route('admin.blog.posts.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus mr-1"></i> Create First Post
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $posts->withQueryString()->links() }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function confirmDelete(id) {
    if (confirm('Are you sure you want to delete this post? This action cannot be undone.')) {
        document.getElementById('delete-form-' + id).submit();
    }
}
</script>