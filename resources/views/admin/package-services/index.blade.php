@extends('layouts.admin')

@section('title', 'Package Services')
@section('page-title', 'Package Services Assignments')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.services.index') }}">Home</a></li>
    <li class="breadcrumb-item active">Package Services</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Assignments</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-info btn-sm mr-2" data-toggle="modal" data-target="#bulkAssignModal">
                <i class="fas fa-layer-group"></i> Bulk Assign
            </button>
            <a href="{{ route('admin.package-services.assign') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> New Assignment
            </a>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Filters -->
        <form action="{{ route('admin.package-services.index') }}" method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <select name="package_id" class="form-control select2">
                            <option value="">All Packages</option>
                            @foreach($packages as $package)
                                <option value="{{ $package->id }}" {{ request('package_id') == $package->id ? 'selected' : '' }}>
                                    {{ $package->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <select name="service_id" class="form-control select2">
                            <option value="">All Services</option>
                            @foreach($services as $service)
                                <option value="{{ $service->id }}" {{ request('service_id') == $service->id ? 'selected' : '' }}>
                                    {{ $service->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="{{ route('admin.package-services.index') }}" class="btn btn-secondary">
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
                        <th>Package</th>
                        <th>Service</th>
                        <th width="10%">Quantity</th>
                        <th width="12%">Custom Price</th>
                        <th width="12%">Total</th>
                        <th width="15%">Notes</th>
                        <th width="10%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($packageServices as $index => $ps)
                    <tr id="row-{{ $ps->id }}">
                        <td>{{ $packageServices->firstItem() + $index }}</td>
                        <td>
                            <a href="{{ route('admin.packages.show', $ps->package) }}">
                                <strong>{{ $ps->package->name }}</strong>
                            </a>
                            <br>
                            <small class="text-muted">{{ $ps->package->formatted_price }}</small>
                        </td>
                        <td>
                            <a href="{{ route('admin.services.show', $ps->service) }}">
                                {{ $ps->service->name }}
                            </a>
                            <br>
                            <small class="text-muted">Default: ${{ number_format($ps->service->price, 2) }}</small>
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm quantity-inline" 
                                   value="{{ $ps->quantity }}" min="1" data-id="{{ $ps->id }}" style="width: 70px;">
                        </td>
                        <td>
                            <input type="number" step="0.01" class="form-control form-control-sm price-inline" 
                                   value="{{ $ps->custom_price }}" min="0" data-id="{{ $ps->id }}" style="width: 100px;" placeholder="Default">
                        </td>
                        <td class="total-cell"><strong>{{ $ps->formatted_total_price }}</strong></td>
                        <td><small>{{ Str::limit($ps->notes, 30) ?: '-' }}</small></td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('admin.package-services.assign', ['package_id' => $ps->package_id]) }}" class="btn btn-warning btn-sm" title="Edit Package Services">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-danger btn-sm" onclick="removeAssignment({{ $ps->id }})" title="Remove">
                                    <i class="fas fa-unlink"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">No assignments found.</p>
                            <a href="{{ route('admin.package-services.assign') }}" class="btn btn-primary mt-3">Create First Assignment</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4 d-flex justify-content-between align-items-center">
            <div>
                Showing {{ $packageServices->firstItem() ?? 0 }} to {{ $packageServices->lastItem() ?? 0 }} of {{ $packageServices->total() }} entries
            </div>
            <div>
                {{ $packageServices->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Bulk Assign Modal -->
<div class="modal fade" id="bulkAssignModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.package-services.bulk-assign') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-layer-group"></i> Bulk Assign Service to Packages</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Select Service <span class="text-danger">*</span></label>
                        <select name="service_id" class="form-control select2" required style="width: 100%;">
                            <option value="">Choose a service...</option>
                            @foreach($services as $service)
                                <option value="{{ $service->id }}">{{ $service->name }} (${{ number_format($service->price, 2) }})</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Select Packages <span class="text-danger">*</span></label>
                        <div class="mb-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllPackages">Select All</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAllPackages">Deselect All</button>
                        </div>
                        <div class="row">
                            @foreach($packages as $package)
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input package-checkbox" 
                                           id="package-{{ $package->id }}" name="package_ids[]" value="{{ $package->id }}">
                                    <label class="custom-control-label" for="package-{{ $package->id }}">
                                        {{ $package->name }}
                                        <small class="text-muted">({{ $package->services->count() }} services)</small>
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Quantity <span class="text-danger">*</span></label>
                                <input type="number" name="quantity" class="form-control" value="1" min="1" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Custom Price (Optional)</label>
                                <input type="number" step="0.01" name="custom_price" class="form-control" min="0" placeholder="Use default price">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Assign to Selected Packages
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize Select2 in modal
    $('#bulkAssignModal').on('shown.bs.modal', function () {
        $(this).find('.select2').select2({
            dropdownParent: $(this),
            theme: 'bootstrap-5',
            width: '100%'
        });
    });

    // Select/Deselect all packages
    $('#selectAllPackages').on('click', function() {
        $('.package-checkbox').prop('checked', true);
    });
    
    $('#deselectAllPackages').on('click', function() {
        $('.package-checkbox').prop('checked', false);
    });

    // Inline quantity update
    let updateTimeout;
    $('.quantity-inline, .price-inline').on('input', function() {
        const input = $(this);
        const id = input.data('id');
        const row = $(`#row-${id}`);
        
        clearTimeout(updateTimeout);
        updateTimeout = setTimeout(function() {
            const quantity = row.find('.quantity-inline').val();
            const customPrice = row.find('.price-inline').val();
            
            $.ajax({
                url: `/admin/package-services/${id}`,
                method: 'PUT',
                data: {
                    quantity: quantity,
                    custom_price: customPrice || null
                },
                success: function(response) {
                    if (response.success) {
                        // Flash green briefly
                        input.addClass('is-valid');
                        setTimeout(() => input.removeClass('is-valid'), 1000);
                        
                        // Update total if needed
                        Swal.fire({
                            icon: 'success',
                            title: 'Updated!',
                            text: response.message,
                            timer: 1000,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-end'
                        });
                    }
                },
                error: function() {
                    input.addClass('is-invalid');
                    setTimeout(() => input.removeClass('is-invalid'), 2000);
                }
            });
        }, 500);
    });
});

function removeAssignment(id) {
    Swal.fire({
        title: 'Remove Assignment?',
        text: 'This will remove the service from the package.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, remove it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/admin/package-services/${id}`,
                method: 'DELETE',
                success: function(response) {
                    if (response.success) {
                        $(`#row-${id}`).fadeOut(300, function() {
                            $(this).remove();
                        });
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Removed!',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Something went wrong. Please try again.'
                    });
                }
            });
        }
    });
}
</script>
@endpush