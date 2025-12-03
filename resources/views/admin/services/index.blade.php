@extends('layouts.admin')

@section('title', 'Services')
@section('page-title', 'Services')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.services.index') }}">Home</a></li>
    <li class="breadcrumb-item active">Services</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Services</h3>
        <div class="card-tools">
            <a href="{{ route('admin.services.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add New Service
            </a>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Filters -->
        <form action="{{ route('admin.services.index') }}" method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <input type="text" name="search" class="form-control" placeholder="Search by name or description..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-info btn-block">
                        <i class="fas fa-search"></i> Filter
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.services.index') }}" class="btn btn-secondary btn-block">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </div>
        </form>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th width="5%">#</th>
                        <th width="10%">Image</th>
                        <th>Name</th>
                        <th width="12%">Price</th>
                        <th width="10%">Duration</th>
                        <th width="10%">Status</th>
                        <th width="8%">Order</th>
                        <th width="15%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($services as $index => $service)
                    <tr>
                        <td>{{ $services->firstItem() + $index }}</td>
                        <td>
                            @if($service->image)
                                <img src="{{ $service->image_url }}" alt="{{ $service->name }}" class="img-thumbnail" style="max-width: 60px; max-height: 60px;">
                            @else
                                <span class="text-muted"><i class="fas fa-image fa-2x"></i></span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $service->name }}</strong>
                            @if($service->icon)
                                <i class="{{ $service->icon }} ml-2"></i>
                            @endif
                            <br>
                            <small class="text-muted">{{ Str::limit($service->description, 50) }}</small>
                        </td>
                        <td>{{ $service->formatted_price }}</td>
                        <td>{{ $service->formatted_duration }}</td>
                        <td>
                            <span class="status-badge status-{{ $service->status }}">
                                {{ ucfirst($service->status) }}
                            </span>
                        </td>
                        <td>{{ $service->sort_order }}</td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('admin.services.show', $service) }}" class="btn btn-info btn-action" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.services.edit', $service) }}" class="btn btn-warning btn-action" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-secondary btn-action" onclick="toggleStatus('{{ route('admin.services.toggle-status', $service) }}', this)" title="Toggle Status">
                                    <i class="fas fa-toggle-{{ $service->status == 'active' ? 'on' : 'off' }}"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-action" onclick="confirmDelete('delete-form-{{ $service->id }}', 'service')" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <form id="delete-form-{{ $service->id }}" action="{{ route('admin.services.destroy', $service) }}" method="POST" style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">No services found.</p>
                            <a href="{{ route('admin.services.create') }}" class="btn btn-primary mt-3">Add First Service</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4 d-flex justify-content-between align-items-center">
            <div>
                Showing {{ $services->firstItem() ?? 0 }} to {{ $services->lastItem() ?? 0 }} of {{ $services->total() }} entries
            </div>
            <div>
                {{ $services->links() }}
            </div>
        </div>
    </div>
</div>
@endsection