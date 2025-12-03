@extends('layouts.admin')

@section('title', 'Packages')
@section('page-title', 'Packages')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.services.index') }}">Home</a></li>
    <li class="breadcrumb-item active">Packages</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Packages</h3>
        <div class="card-tools">
            <a href="{{ route('admin.packages.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add New Package
            </a>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Filters -->
        <form action="{{ route('admin.packages.index') }}" method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <input type="text" name="search" class="form-control" placeholder="Search by name..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <select name="featured" class="form-control">
                            <option value="">All</option>
                            <option value="1" {{ request('featured') == '1' ? 'selected' : '' }}>Featured</option>
                            <option value="0" {{ request('featured') == '0' ? 'selected' : '' }}>Not Featured</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-info btn-block">
                        <i class="fas fa-search"></i> Filter
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.packages.index') }}" class="btn btn-secondary btn-block">
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
                        <th width="8%">Image</th>
                        <th>Name</th>
                        <th width="12%">Price</th>
                        <th width="8%">Services</th>
                        <th width="8%">Status</th>
                        <th width="8%">Featured</th>
                        <th width="15%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($packages as $index => $package)
                    <tr>
                        <td>{{ $packages->firstItem() + $index }}</td>
                        <td>
                            @if($package->image)
                                <img src="{{ $package->image_url }}" alt="{{ $package->name }}" class="img-thumbnail" style="max-width: 60px; max-height: 60px;">
                            @else
                                <span class="text-muted"><i class="fas fa-image fa-2x"></i></span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $package->name }}</strong>
                            @if($package->validity_days)
                                <span class="badge badge-info">{{ $package->validity_days }} days</span>
                            @endif
                            <br>
                            <small class="text-muted">{{ Str::limit($package->description, 50) }}</small>
                        </td>
                        <td>
                            @if($package->discount_price)
                                <span class="text-danger"><del>{{ $package->formatted_price }}</del></span><br>
                                <strong class="text-success">{{ $package->formatted_discount_price }}</strong>
                                <span class="badge badge-success">{{ $package->discount_percentage }}% OFF</span>
                            @else
                                <strong>{{ $package->formatted_price }}</strong>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-primary">{{ $package->services_count }} services</span>
                        </td>
                        <td>
                            <span class="status-badge status-{{ $package->status }}">
                                {{ ucfirst($package->status) }}
                            </span>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm {{ $package->is_featured ? 'btn-warning' : 'btn-outline-secondary' }}" 
                                    onclick="toggleFeatured('{{ route('admin.packages.toggle-featured', $package) }}', this)">
                                <i class="fas fa-star"></i>
                            </button>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('admin.packages.show', $package) }}" class="btn btn-info btn-action" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.packages.edit', $package) }}" class="btn btn-warning btn-action" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('admin.package-services.assign', ['package_id' => $package->id]) }}" class="btn btn-success btn-action" title="Assign Services">
                                    <i class="fas fa-link"></i>
                                </a>
                                <button type="button" class="btn btn-secondary btn-action" onclick="toggleStatus('{{ route('admin.packages.toggle-status', $package) }}', this)" title="Toggle Status">
                                    <i class="fas fa-toggle-{{ $package->status == 'active' ? 'on' : 'off' }}"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-action" onclick="confirmDelete('delete-form-{{ $package->id }}', 'package')" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <form id="delete-form-{{ $package->id }}" action="{{ route('admin.packages.destroy', $package) }}" method="POST" style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">No packages found.</p>
                            <a href="{{ route('admin.packages.create') }}" class="btn btn-primary mt-3">Add First Package</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4 d-flex justify-content-between align-items-center">
            <div>
                Showing {{ $packages->firstItem() ?? 0 }} to {{ $packages->lastItem() ?? 0 }} of {{ $packages->total() }} entries
            </div>
            <div>
                {{ $packages->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleFeatured(url, element) {
    $.post(url)
        .done(function(response) {
            if (response.success) {
                const btn = $(element);
                if (response.is_featured) {
                    btn.removeClass('btn-outline-secondary').addClass('btn-warning');
                } else {
                    btn.removeClass('btn-warning').addClass('btn-outline-secondary');
                }
                
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message,
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        })
        .fail(function() {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Something went wrong. Please try again.'
            });
        });
}
</script>
@endpush