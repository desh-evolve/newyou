@extends('layouts.admin')

@section('title', 'Blog Tags')

@section('content-header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0">Blog Tags</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Tags</li>
            </ol>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">All Tags</h3>
                    <div class="card-tools">
                        <form action="{{ route('admin.blog.tags.index') }}" method="GET" class="input-group input-group-sm" style="width: 250px;">
                            <input type="text" name="search" class="form-control" placeholder="Search tags..." value="{{ request('search') }}">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-default">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Posts</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tags as $tag)
                                <tr>
                                    <td>{{ $tag->id }}</td>
                                    <td>
                                        <span class="badge badge-secondary">{{ $tag->name }}</span>
                                    </td>
                                    <td><code>{{ $tag->slug }}</code></td>
                                    <td>
                                        <span class="badge badge-info">{{ $tag->posts_count }}</span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.blog.tags.edit', $tag) }}" class="btn btn-info" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-danger" title="Delete" onclick="confirmDelete({{ $tag->id }})">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                        <form id="delete-form-{{ $tag->id }}" action="{{ route('admin.blog.tags.destroy', $tag) }}" method="POST" class="d-none">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                                        <p class="text-muted mb-0">No tags found.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($tags->hasPages())
                    <div class="card-footer">
                        {{ $tags->withQueryString()->links() }}
                    </div>
                @endif
            </div>
        </div>

        <div class="col-md-4">
            <!-- Quick Add Tag -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Add New Tag</h3>
                </div>
                <form action="{{ route('admin.blog.tags.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="slug">Slug</label>
                            <input type="text" name="slug" id="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug') }}" placeholder="Auto-generated">
                            @error('slug')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-plus mr-1"></i> Add Tag
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function confirmDelete(id) {
    if (confirm('Are you sure you want to delete this tag?')) {
        document.getElementById('delete-form-' + id).submit();
    }
}

$('#name').on('keyup blur', function() {
    if (!$('#slug').val()) {
        $('#slug').val($(this).val().toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, ''));
    }
});
</script>
@endpush