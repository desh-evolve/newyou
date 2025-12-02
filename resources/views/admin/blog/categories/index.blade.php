@extends('layouts.admin')

@section('title', 'Blog Categories')

@section('content-header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0">Blog Categories</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Categories</li>
            </ol>
        </div>
    </div>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Categories</h3>
            <div class="card-tools">
                <a href="{{ route('admin.blog.categories.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus mr-1"></i> Add Category
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <form action="{{ route('admin.blog.categories.index') }}" method="GET" class="mb-4">
                <div class="row">
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Search categories..." value="{{ request('search') }}">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-default">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-control" onchange="this.form.submit()">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('admin.blog.categories.index') }}" class="btn btn-default btn-block">
                            <i class="fas fa-redo mr-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th style="width: 80px;">Image</th>
                            <th>Name</th>
                            <th>Slug</th>
                            <th style="width: 100px;">Posts</th>
                            <th style="width: 100px;">Order</th>
                            <th style="width: 100px;">Status</th>
                            <th style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td>{{ $category->id }}</td>
                                <td>
                                    @if($category->image)
                                        <img src="{{ Storage::url($category->image) }}" alt="{{ $category->name }}" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                    @else
                                        <div class="bg-secondary d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                            <i class="fas fa-folder text-white"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $category->name }}</strong>
                                    @if($category->description)
                                        <br><small class="text-muted">{{ Str::limit($category->description, 50) }}</small>
                                    @endif
                                </td>
                                <td><code>{{ $category->slug }}</code></td>
                                <td>
                                    <span class="badge badge-info">{{ $category->posts_count }} posts</span>
                                </td>
                                <td>{{ $category->order }}</td>
                                <td>
                                    @if($category->is_active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('admin.blog.categories.edit', $category) }}" class="btn btn-info btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger btn-sm" title="Delete" onclick="confirmDelete({{ $category->id }})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <form id="delete-form-{{ $category->id }}" action="{{ route('admin.blog.categories.destroy', $category) }}" method="POST" class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No categories found.</p>
                                    <a href="{{ route('admin.blog.categories.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus mr-1"></i> Create First Category
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $categories->withQueryString()->links() }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function confirmDelete(id) {
    if (confirm('Are you sure you want to delete this category? Posts in this category will become uncategorized.')) {
        document.getElementById('delete-form-' + id).submit();
    }
}
</script>
@endpush