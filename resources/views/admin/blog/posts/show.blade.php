@extends('layouts.admin')

@section('title', $post->title)

@push('styles')
<style>
    .post-content img { max-width: 100%; height: auto; }
    .post-content { line-height: 1.8; }
    .meta-item { display: inline-block; margin-right: 20px; }
</style>
@endpush

@section('content-header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0">View Post</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.blog.posts.index') }}">Posts</a></li>
                <li class="breadcrumb-item active">View</li>
            </ol>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <div class="card">
                <!-- Featured Image -->
                @if($post->featured_image)
                    <img src="{{ Storage::url($post->featured_image) }}" alt="{{ $post->title }}" class="card-img-top" style="max-height: 400px; object-fit: cover;">
                @endif

                <div class="card-body">
                    <!-- Status & Category -->
                    <div class="mb-3">
                        @switch($post->status)
                            @case('published')
                                <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> Published</span>
                                @break
                            @case('draft')
                                <span class="badge badge-warning"><i class="fas fa-edit mr-1"></i> Draft</span>
                                @break
                            @case('scheduled')
                                <span class="badge badge-primary"><i class="fas fa-clock mr-1"></i> Scheduled</span>
                                @break
                        @endswitch

                        @if($post->is_featured)
                            <span class="badge badge-warning"><i class="fas fa-star mr-1"></i> Featured</span>
                        @endif

                        @if($post->category)
                            <span class="badge badge-info">{{ $post->category->name }}</span>
                        @endif
                    </div>

                    <!-- Title -->
                    <h1 class="mb-3">{{ $post->title }}</h1>

                    <!-- Meta Info -->
                    <div class="text-muted mb-4 pb-3 border-bottom">
                        <span class="meta-item">
                            <i class="fas fa-user mr-1"></i> {{ $post->author->name ?? 'Unknown' }}
                        </span>
                        <span class="meta-item">
                            <i class="fas fa-calendar mr-1"></i> 
                            {{ $post->published_at ? $post->published_at->format('F d, Y') : $post->created_at->format('F d, Y') }}
                        </span>
                        <span class="meta-item">
                            <i class="fas fa-eye mr-1"></i> {{ number_format($post->views) }} views
                        </span>
                        <span class="meta-item">
                            <i class="fas fa-clock mr-1"></i> {{ $post->reading_time }} min read
                        </span>
                    </div>

                    <!-- Excerpt -->
                    @if($post->excerpt)
                        <div class="lead text-muted mb-4 p-3 bg-light rounded">
                            {{ $post->excerpt }}
                        </div>
                    @endif

                    <!-- Content -->
                    <div class="post-content">
                        {!! $post->content !!}
                    </div>

                    <!-- Tags -->
                    @if($post->tags->count() > 0)
                        <hr class="my-4">
                        <div class="d-flex align-items-center flex-wrap">
                            <strong class="mr-2"><i class="fas fa-tags mr-1"></i> Tags:</strong>
                            @foreach($post->tags as $tag)
                                <span class="badge badge-secondary mr-1 mb-1">{{ $tag->name }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- SEO Info -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-search mr-2"></i>SEO Information</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered mb-0">
                        <tr>
                            <th style="width: 150px;">Meta Title</th>
                            <td>{{ $post->meta_title ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Meta Description</th>
                            <td>{{ $post->meta_description ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Meta Keywords</th>
                            <td>{{ $post->meta_keywords ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Slug</th>
                            <td><code>{{ $post->slug }}</code></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Actions -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-cogs mr-2"></i>Actions</h3>
                </div>
                <div class="card-body">
                    <a href="{{ route('admin.blog.posts.edit', $post) }}" class="btn btn-primary btn-block">
                        <i class="fas fa-edit mr-1"></i> Edit Post
                    </a>
                    
                    @if($post->status === 'published')
                        <a href="{{ url('/blog/' . $post->slug) }}" class="btn btn-info btn-block" target="_blank">
                            <i class="fas fa-external-link-alt mr-1"></i> View on Site
                        </a>
                    @endif

                    <hr>

                    @if($post->status !== 'published')
                        <form action="{{ route('admin.blog.posts.update', $post) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="title" value="{{ $post->title }}">
                            <input type="hidden" name="content" value="{{ $post->content }}">
                            <input type="hidden" name="status" value="published">
                            <button type="submit" class="btn btn-success btn-block">
                                <i class="fas fa-check mr-1"></i> Publish Now
                            </button>
                        </form>
                    @else
                        <form action="{{ route('admin.blog.posts.update', $post) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="title" value="{{ $post->title }}">
                            <input type="hidden" name="content" value="{{ $post->content }}">
                            <input type="hidden" name="status" value="draft">
                            <button type="submit" class="btn btn-warning btn-block">
                                <i class="fas fa-eye-slash mr-1"></i> Unpublish
                            </button>
                        </form>
                    @endif

                    <button type="button" class="btn btn-outline-danger btn-block mt-2" onclick="confirmDelete()">
                        <i class="fas fa-trash mr-1"></i> Delete Post
                    </button>
                </div>
            </div>

            <!-- Post Info -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Post Information</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <tr>
                            <td><strong>ID</strong></td>
                            <td>#{{ $post->id }}</td>
                        </tr>
                        <tr>
                            <td><strong>Status</strong></td>
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
                        </tr>
                        <tr>
                            <td><strong>Category</strong></td>
                            <td>{{ $post->category->name ?? 'None' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Tags</strong></td>
                            <td>{{ $post->tags->count() }}</td>
                        </tr>
                        <tr>
                            <td><strong>Views</strong></td>
                            <td>{{ number_format($post->views) }}</td>
                        </tr>
                        <tr>
                            <td><strong>Comments</strong></td>
                            <td>
                                @if($post->allow_comments)
                                    <span class="badge badge-success">Allowed</span>
                                @else
                                    <span class="badge badge-secondary">Disabled</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Featured</strong></td>
                            <td>
                                @if($post->is_featured)
                                    <span class="badge badge-warning"><i class="fas fa-star"></i> Yes</span>
                                @else
                                    <span class="text-muted">No</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Dates -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-calendar-alt mr-2"></i>Dates</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <tr>
                            <td><strong>Created</strong></td>
                            <td>
                                {{ $post->created_at->format('M d, Y') }}<br>
                                <small class="text-muted">{{ $post->created_at->format('H:i A') }}</small>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Updated</strong></td>
                            <td>
                                {{ $post->updated_at->format('M d, Y') }}<br>
                                <small class="text-muted">{{ $post->updated_at->format('H:i A') }}</small>
                            </td>
                        </tr>
                        @if($post->published_at)
                            <tr>
                                <td><strong>Published</strong></td>
                                <td>
                                    {{ $post->published_at->format('M d, Y') }}<br>
                                    <small class="text-muted">{{ $post->published_at->format('H:i A') }}</small>
                                </td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>

            <!-- Permalink -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-link mr-2"></i>Permalink</h3>
                </div>
                <div class="card-body">
                    <div class="input-group">
                        <input type="text" class="form-control form-control-sm" id="postUrl" value="{{ url('/blog/' . $post->slug) }}" readonly>
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary btn-sm" type="button" onclick="copyUrl()">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Author -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user mr-2"></i>Author</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fas fa-user text-white"></i>
                            </div>
                        </div>
                        <div>
                            <strong>{{ $post->author->name ?? 'Unknown' }}</strong><br>
                            <small class="text-muted">{{ $post->author->email ?? '' }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Form -->
    <form id="deleteForm" action="{{ route('admin.blog.posts.destroy', $post) }}" method="POST" class="d-none">
        @csrf
        @method('DELETE')
    </form>
@endsection

@push('scripts')
<script>
function copyUrl() {
    const urlInput = document.getElementById('postUrl');
    urlInput.select();
    document.execCommand('copy');
    
    // Show feedback
    const btn = urlInput.nextElementSibling.querySelector('button');
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check text-success"></i>';
    setTimeout(() => btn.innerHTML = originalHtml, 2000);
}

function confirmDelete() {
    if (confirm('Are you sure you want to delete this post? This action cannot be undone.')) {
        document.getElementById('deleteForm').submit();
    }
}
</script>
@endpush