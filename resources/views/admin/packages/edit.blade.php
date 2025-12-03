@extends('layouts.admin')

@section('title', 'Edit Package')
@section('page-title', 'Edit Package')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.services.index') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.packages.index') }}">Packages</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<form action="{{ route('admin.packages.update', $package) }}" method="POST" enctype="multipart/form-data" id="package-form">
    @csrf
    @method('PUT')
    
    <div class="row">
        <div class="col-md-8">
            <!-- Basic Information -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Package: {{ $package->name }}</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="name">Package Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $package->name) }}" required>
                                @error('name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="slug">Slug</label>
                                <input type="text" class="form-control @error('slug') is-invalid @enderror" 
                                       id="slug" name="slug" value="{{ old('slug', $package->slug) }}">
                                @error('slug')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="4">{{ old('description', $package->description) }}</textarea>
                        @error('description')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="price">Price ($) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" class="form-control @error('price') is-invalid @enderror" 
                                       id="price" name="price" value="{{ old('price', $package->price) }}" required>
                                @error('price')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="discount_price">Discount Price ($)</label>
                                <input type="number" step="0.01" min="0" class="form-control @error('discount_price') is-invalid @enderror" 
                                       id="discount_price" name="discount_price" value="{{ old('discount_price', $package->discount_price) }}">
                                @error('discount_price')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="validity_days">Validity (Days)</label>
                                <input type="number" min="1" class="form-control @error('validity_days') is-invalid @enderror" 
                                       id="validity_days" name="validity_days" value="{{ old('validity_days', $package->validity_days) }}">
                                @error('validity_days')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="status">Status <span class="text-danger">*</span></label>
                                <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="active" {{ old('status', $package->status) == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status', $package->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="sort_order">Sort Order</label>
                                <input type="number" min="0" class="form-control @error('sort_order') is-invalid @enderror" 
                                       id="sort_order" name="sort_order" value="{{ old('sort_order', $package->sort_order) }}">
                                @error('sort_order')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div class="custom-control custom-checkbox mt-2">
                                    <input type="checkbox" class="custom-control-input" id="is_featured" name="is_featured" value="1" {{ old('is_featured', $package->is_featured) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_featured">
                                        <i class="fas fa-star text-warning"></i> Featured Package
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="image">Package Image</label>
                        @if($package->image)
                        <div class="mb-3">
                            <img src="{{ $package->image_url }}" alt="{{ $package->name }}" class="image-preview">
                            <div class="mt-2">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="remove_image" name="remove_image" value="1">
                                    <label class="custom-control-label text-danger" for="remove_image">Remove current image</label>
                                </div>
                            </div>
                        </div>
                        @endif
                        <div class="custom-file">
                            <input type="file" class="custom-file-input @error('image') is-invalid @enderror" 
                                   id="image" name="image" accept="image/*">
                            <label class="custom-file-label" for="image">{{ $package->image ? 'Choose new file' : 'Choose file' }}</label>
                        </div>
                        @error('image')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                        <div id="image-preview-container" class="mt-3" style="display: none;">
                            <img id="image-preview" src="" alt="Preview" class="image-preview">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Services Assignment -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-link"></i> Assigned Services</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-success btn-sm" id="add-service-btn">
                            <i class="fas fa-plus"></i> Add Service
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="services-container">
                        @foreach($package->packageServices as $index => $ps)
                        <div class="service-item" data-service-id="{{ $ps->service_id }}">
                            <div class="row align-items-center">
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        <label class="small">Service</label>
                                        <select class="form-control form-control-sm service-select" name="services[{{ $index }}][id]" required>
                                            <option value="">Select Service</option>
                                            @foreach($services as $service)
                                                <option value="{{ $service->id }}" data-price="{{ $service->price }}" {{ $ps->service_id == $service->id ? 'selected' : '' }}>
                                                    {{ $service->name }} (${{ number_format($service->price, 2) }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-0">
                                        <label class="small">Quantity</label>
                                        <input type="number" class="form-control form-control-sm quantity-input" name="services[{{ $index }}][quantity]" value="{{ $ps->quantity }}" min="1" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-0">
                                        <label class="small">Custom Price</label>
                                        <input type="number" step="0.01" min="0" class="form-control form-control-sm custom-price-input" name="services[{{ $index }}][custom_price]" value="{{ $ps->custom_price }}" placeholder="Default">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-0">
                                        <label class="small">Notes</label>
                                        <input type="text" class="form-control form-control-sm" name="services[{{ $index }}][notes]" value="{{ $ps->notes }}" placeholder="Optional">
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-danger btn-sm remove-service-btn mt-4">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    <div id="no-services-message" class="text-center py-4 text-muted" style="{{ $package->packageServices->count() > 0 ? 'display: none;' : '' }}">
                        <i class="fas fa-info-circle fa-2x mb-2"></i>
                        <p class="mb-0">No services added yet. Click "Add Service" to include services in this package.</p>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Total Services:</strong> <span id="total-services-count">{{ $package->packageServices->count() }}</span>
                        </div>
                        <div class="col-md-6 text-right">
                            <strong>Services Value:</strong> $<span id="total-services-value">{{ number_format($package->services_value, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Available Services -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Available Services</h3>
                </div>
                <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                    <ul class="list-group list-group-flush" id="available-services-list">
                        @foreach($services as $service)
                        <li class="list-group-item d-flex justify-content-between align-items-center available-service-item {{ $package->packageServices->where('service_id', $service->id)->count() > 0 ? 'bg-light' : '' }}" 
                            data-id="{{ $service->id }}"
                            data-name="{{ $service->name }}"
                            data-price="{{ $service->price }}">
                            <div>
                                <strong>{{ $service->name }}</strong>
                                <br>
                                <small class="text-success">${{ number_format($service->price, 2) }}</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary add-service-quick" data-id="{{ $service->id }}" {{ $package->packageServices->where('service_id', $service->id)->count() > 0 ? 'disabled' : '' }}>
                                <i class="fas fa-plus"></i>
                            </button>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <!-- Summary -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">Package Summary</h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td>Package Price:</td>
                            <td class="text-right"><strong>$<span id="summary-package-price">{{ number_format($package->effective_price, 2) }}</span></strong></td>
                        </tr>
                        <tr>
                            <td>Services Value:</td>
                            <td class="text-right">$<span id="summary-services-value">{{ number_format($package->services_value, 2) }}</span></td>
                        </tr>
                        <tr class="border-top">
                            <td>Customer Savings:</td>
                            <td class="text-right text-success"><strong>$<span id="summary-savings">{{ number_format($package->savings, 2) }}</span></strong></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary btn-block btn-lg">
                        <i class="fas fa-save"></i> Update Package
                    </button>
                    <a href="{{ route('admin.packages.index') }}" class="btn btn-secondary btn-block">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </div>

            <!-- Package Info -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Package Info</h3>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Created:</dt>
                        <dd class="col-sm-7">{{ $package->created_at->format('M d, Y') }}</dd>
                        <dt class="col-sm-5">Updated:</dt>
                        <dd class="col-sm-7">{{ $package->updated_at->format('M d, Y') }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Service Row Template -->
<template id="service-row-template">
    <div class="service-item" data-service-id="">
        <div class="row align-items-center">
            <div class="col-md-4">
                <div class="form-group mb-0">
                    <label class="small">Service</label>
                    <select class="form-control form-control-sm service-select" name="services[__INDEX__][id]" required>
                        <option value="">Select Service</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}" data-price="{{ $service->price }}">{{ $service->name }} (${{ number_format($service->price, 2) }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group mb-0">
                    <label class="small">Quantity</label>
                    <input type="number" class="form-control form-control-sm quantity-input" name="services[__INDEX__][quantity]" value="1" min="1" required>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group mb-0">
                    <label class="small">Custom Price</label>
                    <input type="number" step="0.01" min="0" class="form-control form-control-sm custom-price-input" name="services[__INDEX__][custom_price]" placeholder="Default">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group mb-0">
                    <label class="small">Notes</label>
                    <input type="text" class="form-control form-control-sm" name="services[__INDEX__][notes]" placeholder="Optional">
                </div>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-danger btn-sm remove-service-btn mt-4">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
</template>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let serviceIndex = {{ $package->packageServices->count() }};
    const servicesContainer = $('#services-container');
    const template = $('#service-row-template').html();

    // Image preview
    $('#image').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#image-preview').attr('src', e.target.result);
                $('#image-preview-container').show();
            }
            reader.readAsDataURL(file);
            $(this).next('.custom-file-label').text(file.name);
        }
    });

    // Add service row
    function addServiceRow(serviceId = '', quantity = 1) {
        const row = template.replace(/__INDEX__/g, serviceIndex);
        servicesContainer.append(row);
        
        const newRow = servicesContainer.find('.service-item').last();
        
        if (serviceId) {
            newRow.find('.service-select').val(serviceId);
            newRow.attr('data-service-id', serviceId);
        }
        if (quantity) {
            newRow.find('.quantity-input').val(quantity);
        }
        
        serviceIndex++;
        updateServicesList();
        calculateTotals();
        $('#no-services-message').hide();
    }

    $('#add-service-btn').on('click', function() {
        addServiceRow();
    });

    $(document).on('click', '.add-service-quick', function() {
        const serviceId = $(this).data('id');
        if (servicesContainer.find(`[data-service-id="${serviceId}"]`).length > 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Already Added',
                text: 'This service is already in the package.',
                timer: 2000,
                showConfirmButton: false
            });
            return;
        }
        addServiceRow(serviceId);
    });

    $(document).on('click', '.remove-service-btn', function() {
        $(this).closest('.service-item').remove();
        updateServicesList();
        calculateTotals();
        if (servicesContainer.find('.service-item').length === 0) {
            $('#no-services-message').show();
        }
    });

    $(document).on('change', '.service-select', function() {
        const row = $(this).closest('.service-item');
        row.attr('data-service-id', $(this).val());
        updateServicesList();
        calculateTotals();
    });

    $(document).on('input', '.quantity-input, .custom-price-input', function() {
        calculateTotals();
    });

    $('#price, #discount_price').on('input', function() {
        calculateTotals();
    });

    function updateServicesList() {
        const addedServices = [];
        servicesContainer.find('.service-item').each(function() {
            const serviceId = $(this).attr('data-service-id');
            if (serviceId) {
                addedServices.push(serviceId);
            }
        });

        $('#available-services-list .available-service-item').each(function() {
            const serviceId = $(this).data('id').toString();
            if (addedServices.includes(serviceId)) {
                $(this).addClass('bg-light').find('.add-service-quick').prop('disabled', true);
            } else {
                $(this).removeClass('bg-light').find('.add-service-quick').prop('disabled', false);
            }
        });
    }

    function calculateTotals() {
        let totalServices = 0;
        let totalValue = 0;

        servicesContainer.find('.service-item').each(function() {
            const select = $(this).find('.service-select');
            const quantity = parseInt($(this).find('.quantity-input').val()) || 1;
            const customPrice = parseFloat($(this).find('.custom-price-input').val());
            
            if (select.val()) {
                const defaultPrice = parseFloat(select.find(':selected').data('price')) || 0;
                const price = isNaN(customPrice) ? defaultPrice : customPrice;
                
                totalServices++;
                totalValue += price * quantity;
            }
        });

        const packagePrice = parseFloat($('#discount_price').val()) || parseFloat($('#price').val()) || 0;
        const savings = Math.max(0, totalValue - packagePrice);

        $('#total-services-count').text(totalServices);
        $('#total-services-value').text(totalValue.toFixed(2));
        $('#summary-services-value').text(totalValue.toFixed(2));
        $('#summary-package-price').text(packagePrice.toFixed(2));
        $('#summary-savings').text(savings.toFixed(2));
    }

    // Initial
    updateServicesList();
    calculateTotals();
});
</script>
@endpush