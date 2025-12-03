@extends('layouts.admin')

@section('title', 'Assign Services to Package')
@section('page-title', 'Assign Services to Package')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.services.index') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.package-services.index') }}">Package Services</a></li>
    <li class="breadcrumb-item active">Assign</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-4">
        <!-- Package Selection -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title"><i class="fas fa-box"></i> Select Package</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.package-services.assign') }}" method="GET">
                    <div class="form-group">
                        <label>Choose a Package</label>
                        <select name="package_id" class="form-control select2" onchange="this.form.submit()" style="width: 100%;">
                            <option value="">-- Select Package --</option>
                            @foreach($packages as $package)
                                <option value="{{ $package->id }}" {{ $selectedPackage && $selectedPackage->id == $package->id ? 'selected' : '' }}>
                                    {{ $package->name }} ({{ $package->services->count() }} services)
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>

        @if($selectedPackage)
        <!-- Package Info -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Package Details</h3>
            </div>
            <div class="card-body">
                @if($selectedPackage->image)
                    <img src="{{ $selectedPackage->image_url }}" alt="{{ $selectedPackage->name }}" class="img-fluid rounded mb-3">
                @endif
                
                <h5>{{ $selectedPackage->name }}</h5>
                <p class="text-muted small">{{ Str::limit($selectedPackage->description, 100) }}</p>
                
                <table class="table table-sm table-borderless">
                    <tr>
                        <td>Price:</td>
                        <td class="text-right">
                            @if($selectedPackage->discount_price)
                                <del class="text-danger">{{ $selectedPackage->formatted_price }}</del>
                                <strong class="text-success">{{ $selectedPackage->formatted_discount_price }}</strong>
                            @else
                                <strong>{{ $selectedPackage->formatted_price }}</strong>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Status:</td>
                        <td class="text-right">
                            <span class="status-badge status-{{ $selectedPackage->status }}">{{ ucfirst($selectedPackage->status) }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td>Validity:</td>
                        <td class="text-right">{{ $selectedPackage->validity_days ? $selectedPackage->validity_days . ' days' : 'Unlimited' }}</td>
                    </tr>
                </table>
                
                <a href="{{ route('admin.packages.edit', $selectedPackage) }}" class="btn btn-warning btn-block btn-sm">
                    <i class="fas fa-edit"></i> Edit Package
                </a>
            </div>
        </div>

        <!-- Summary -->
        <div class="card">
            <div class="card-header bg-success text-white">
                <h3 class="card-title"><i class="fas fa-calculator"></i> Summary</h3>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td>Total Services:</td>
                        <td class="text-right"><strong id="summary-total-services">{{ $selectedPackage->packageServices->count() }}</strong></td>
                    </tr>
                    <tr>
                        <td>Services Value:</td>
                        <td class="text-right"><strong id="summary-services-value">${{ number_format($selectedPackage->services_value, 2) }}</strong></td>
                    </tr>
                    <tr>
                        <td>Package Price:</td>
                        <td class="text-right">${{ number_format($selectedPackage->effective_price, 2) }}</td>
                    </tr>
                    <tr class="border-top">
                        <td>Customer Savings:</td>
                        <td class="text-right text-success"><strong id="summary-savings">${{ number_format($selectedPackage->savings, 2) }}</strong></td>
                    </tr>
                </table>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-8">
        @if($selectedPackage)
        <form action="{{ route('admin.package-services.store-assignment') }}" method="POST" id="assignment-form">
            @csrf
            <input type="hidden" name="package_id" value="{{ $selectedPackage->id }}">

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-boxes"></i> 
                        Services for "{{ $selectedPackage->name }}"
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-success btn-sm" id="add-service-btn">
                            <i class="fas fa-plus"></i> Add Service
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="services-container">
                        @forelse($selectedPackage->packageServices as $index => $ps)
                        <div class="service-item" data-service-id="{{ $ps->service_id }}">
                            <div class="row align-items-end">
                                <div class="col-md-4">
                                    <div class="form-group mb-2">
                                        <label class="small font-weight-bold">Service</label>
                                        <select class="form-control service-select" name="services[{{ $index }}][service_id]" required>
                                            <option value="">Select Service</option>
                                            @foreach($services as $service)
                                                <option value="{{ $service->id }}" 
                                                        data-price="{{ $service->price }}"
                                                        data-duration="{{ $service->duration }}"
                                                        {{ $ps->service_id == $service->id ? 'selected' : '' }}>
                                                    {{ $service->name }} (${{ number_format($service->price, 2) }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-2">
                                        <label class="small font-weight-bold">Qty</label>
                                        <input type="number" class="form-control quantity-input" 
                                               name="services[{{ $index }}][quantity]" 
                                               value="{{ $ps->quantity }}" min="1" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-2">
                                        <label class="small font-weight-bold">Custom Price</label>
                                        <input type="number" step="0.01" min="0" 
                                               class="form-control custom-price-input" 
                                               name="services[{{ $index }}][custom_price]" 
                                               value="{{ $ps->custom_price }}" 
                                               placeholder="Default">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-2">
                                        <label class="small font-weight-bold">Notes</label>
                                        <input type="text" class="form-control" 
                                               name="services[{{ $index }}][notes]" 
                                               value="{{ $ps->notes }}" 
                                               placeholder="Optional notes">
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group mb-2">
                                        <button type="button" class="btn btn-danger btn-block remove-service-btn">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <small class="text-muted service-info">
                                        <span class="service-price-display">Price: ${{ number_format($ps->effective_price, 2) }}</span>
                                        <span class="mx-2">|</span>
                                        <span class="service-total-display">Total: ${{ number_format($ps->total_price, 2) }}</span>
                                    </small>
                                </div>
                            </div>
                            <hr class="my-3">
                        </div>
                        @empty
                        <div id="no-services-message" class="text-center py-5">
                            <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">No services assigned yet</h5>
                            <p class="text-muted">Click "Add Service" to start adding services to this package.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <span class="text-muted">
                                <i class="fas fa-info-circle"></i> 
                                Drag services to reorder. Changes are saved when you click "Save".
                            </span>
                        </div>
                        <div class="col-md-6 text-right">
                            <a href="{{ route('admin.packages.show', $selectedPackage) }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> Save Assignments
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Available Services Quick Add -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-bolt"></i> Quick Add Services</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($services as $service)
                    <div class="col-md-4 mb-3">
                        <div class="card h-100 quick-add-card {{ $selectedPackage->packageServices->where('service_id', $service->id)->count() > 0 ? 'border-success' : '' }}" 
                             data-id="{{ $service->id }}">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">{{ $service->name }}</h6>
                                        <p class="text-success mb-1"><strong>${{ number_format($service->price, 2) }}</strong></p>
                                        @if($service->duration)
                                            <small class="text-muted">{{ $service->formatted_duration }}</small>
                                        @endif
                                    </div>
                                    <button type="button" 
                                            class="btn btn-sm {{ $selectedPackage->packageServices->where('service_id', $service->id)->count() > 0 ? 'btn-success' : 'btn-outline-primary' }} quick-add-btn"
                                            data-id="{{ $service->id }}"
                                            data-name="{{ $service->name }}"
                                            data-price="{{ $service->price }}"
                                            {{ $selectedPackage->packageServices->where('service_id', $service->id)->count() > 0 ? 'disabled' : '' }}>
                                        <i class="fas {{ $selectedPackage->packageServices->where('service_id', $service->id)->count() > 0 ? 'fa-check' : 'fa-plus' }}"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @else
        <!-- No Package Selected -->
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-hand-point-left fa-4x text-muted mb-4"></i>
                <h4 class="text-muted">Select a Package</h4>
                <p class="text-muted">Please select a package from the left panel to manage its services.</p>
            </div>
        </div>
        @endif
    </div>
</div>

@if($selectedPackage)
<!-- Service Row Template -->
<template id="service-row-template">
    <div class="service-item" data-service-id="">
        <div class="row align-items-end">
            <div class="col-md-4">
                <div class="form-group mb-2">
                    <label class="small font-weight-bold">Service</label>
                    <select class="form-control service-select" name="services[__INDEX__][service_id]" required>
                        <option value="">Select Service</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}" 
                                    data-price="{{ $service->price }}"
                                    data-duration="{{ $service->duration }}">
                                {{ $service->name }} (${{ number_format($service->price, 2) }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group mb-2">
                    <label class="small font-weight-bold">Qty</label>
                    <input type="number" class="form-control quantity-input" name="services[__INDEX__][quantity]" value="1" min="1" required>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group mb-2">
                    <label class="small font-weight-bold">Custom Price</label>
                    <input type="number" step="0.01" min="0" class="form-control custom-price-input" name="services[__INDEX__][custom_price]" placeholder="Default">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group mb-2">
                    <label class="small font-weight-bold">Notes</label>
                    <input type="text" class="form-control" name="services[__INDEX__][notes]" placeholder="Optional notes">
                </div>
            </div>
            <div class="col-md-1">
                <div class="form-group mb-2">
                    <button type="button" class="btn btn-danger btn-block remove-service-btn">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <small class="text-muted service-info">
                    <span class="service-price-display">Price: $0.00</span>
                    <span class="mx-2">|</span>
                    <span class="service-total-display">Total: $0.00</span>
                </small>
            </div>
        </div>
        <hr class="my-3">
    </div>
</template>
@endif
@endsection

@if($selectedPackage)
@push('styles')
<style>
    .service-item {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 10px;
        border-left: 4px solid #007bff;
    }
    .service-item:hover {
        background-color: #e9ecef;
    }
    .quick-add-card {
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .quick-add-card:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    .quick-add-card.border-success {
        background-color: #f8fff8;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
$(document).ready(function() {
    let serviceIndex = {{ $selectedPackage->packageServices->count() }};
    const servicesContainer = document.getElementById('services-container');
    const template = document.getElementById('service-row-template');
    const packagePrice = {{ $selectedPackage->effective_price }};

    // Make services sortable
    if (servicesContainer) {
        new Sortable(servicesContainer, {
            animation: 150,
            handle: '.service-item',
            ghostClass: 'bg-light'
        });
    }

    // Add service row
    function addServiceRow(serviceId = '', serviceName = '', servicePrice = 0) {
        // Hide no services message
        $('#no-services-message').hide();

        const templateContent = template.innerHTML.replace(/__INDEX__/g, serviceIndex);
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = templateContent;
        const newRow = tempDiv.firstElementChild;
        
        servicesContainer.appendChild(newRow);
        
        if (serviceId) {
            $(newRow).find('.service-select').val(serviceId);
            $(newRow).attr('data-service-id', serviceId);
            updateServiceInfo($(newRow));
        }
        
        serviceIndex++;
        updateQuickAddButtons();
        calculateSummary();
    }

    // Add service button
    $('#add-service-btn').on('click', function() {
        addServiceRow();
    });

    // Quick add button
    $(document).on('click', '.quick-add-btn:not(:disabled)', function() {
        const serviceId = $(this).data('id');
        const serviceName = $(this).data('name');
        const servicePrice = $(this).data('price');
        
        // Check if already added
        if ($(`#services-container [data-service-id="${serviceId}"]`).length > 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Already Added',
                text: 'This service is already in the package.',
                timer: 2000,
                showConfirmButton: false
            });
            return;
        }
        
        addServiceRow(serviceId, serviceName, servicePrice);
        
        // Visual feedback
        $(this).removeClass('btn-outline-primary').addClass('btn-success').prop('disabled', true);
        $(this).find('i').removeClass('fa-plus').addClass('fa-check');
        $(this).closest('.quick-add-card').addClass('border-success');
    });

    // Remove service
    $(document).on('click', '.remove-service-btn', function() {
        const row = $(this).closest('.service-item');
        const serviceId = row.attr('data-service-id');
        
        row.fadeOut(200, function() {
            $(this).remove();
            updateQuickAddButtons();
            calculateSummary();
            
            // Show no services message if empty
            if ($('#services-container .service-item').length === 0) {
                $('#no-services-message').show();
            }
        });
    });

    // Service selection change
    $(document).on('change', '.service-select', function() {
        const row = $(this).closest('.service-item');
        const oldServiceId = row.attr('data-service-id');
        const newServiceId = $(this).val();
        
        row.attr('data-service-id', newServiceId);
        
        // Clear custom price when changing service
        row.find('.custom-price-input').val('');
        
        updateServiceInfo(row);
        updateQuickAddButtons();
        calculateSummary();
    });

    // Quantity/Price change
    $(document).on('input', '.quantity-input, .custom-price-input', function() {
        const row = $(this).closest('.service-item');
        updateServiceInfo(row);
        calculateSummary();
    });

    // Update service info display
    function updateServiceInfo(row) {
        const select = row.find('.service-select');
        const selectedOption = select.find(':selected');
        const defaultPrice = parseFloat(selectedOption.data('price')) || 0;
        const customPrice = parseFloat(row.find('.custom-price-input').val());
        const quantity = parseInt(row.find('.quantity-input').val()) || 1;
        
        const effectivePrice = isNaN(customPrice) || customPrice === 0 ? defaultPrice : customPrice;
        const totalPrice = effectivePrice * quantity;
        
        row.find('.service-price-display').text(`Price: $${effectivePrice.toFixed(2)}`);
        row.find('.service-total-display').text(`Total: $${totalPrice.toFixed(2)}`);
    }

    // Update quick add buttons
    function updateQuickAddButtons() {
        const addedServices = [];
        $('#services-container .service-item').each(function() {
            const serviceId = $(this).attr('data-service-id');
            if (serviceId) {
                addedServices.push(serviceId.toString());
            }
        });

        $('.quick-add-btn').each(function() {
            const serviceId = $(this).data('id').toString();
            if (addedServices.includes(serviceId)) {
                $(this).removeClass('btn-outline-primary').addClass('btn-success').prop('disabled', true);
                $(this).find('i').removeClass('fa-plus').addClass('fa-check');
                $(this).closest('.quick-add-card').addClass('border-success');
            } else {
                $(this).removeClass('btn-success').addClass('btn-outline-primary').prop('disabled', false);
                $(this).find('i').removeClass('fa-check').addClass('fa-plus');
                $(this).closest('.quick-add-card').removeClass('border-success');
            }
        });
    }

    // Calculate summary
    function calculateSummary() {
        let totalServices = 0;
        let totalValue = 0;

        $('#services-container .service-item').each(function() {
            const select = $(this).find('.service-select');
            if (!select.val()) return;
            
            const selectedOption = select.find(':selected');
            const defaultPrice = parseFloat(selectedOption.data('price')) || 0;
            const customPrice = parseFloat($(this).find('.custom-price-input').val());
            const quantity = parseInt($(this).find('.quantity-input').val()) || 1;
            
            const effectivePrice = isNaN(customPrice) || customPrice === 0 ? defaultPrice : customPrice;
            
            totalServices++;
            totalValue += effectivePrice * quantity;
        });

        const savings = Math.max(0, totalValue - packagePrice);

        $('#summary-total-services').text(totalServices);
        $('#summary-services-value').text('$' + totalValue.toFixed(2));
        $('#summary-savings').text('$' + savings.toFixed(2));
    }

    // Form validation before submit
    $('#assignment-form').on('submit', function(e) {
        const services = $('#services-container .service-item');
        
        if (services.length === 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'No Services',
                text: 'Please add at least one service to the package.',
            });
            return false;
        }

        // Check for duplicate services
        const serviceIds = [];
        let hasDuplicates = false;
        
        services.each(function() {
            const serviceId = $(this).find('.service-select').val();
            if (serviceId && serviceIds.includes(serviceId)) {
                hasDuplicates = true;
            }
            serviceIds.push(serviceId);
        });

        if (hasDuplicates) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Duplicate Services',
                text: 'Each service can only be added once to a package.',
            });
            return false;
        }

        return true;
    });

    // Initial calculation
    calculateSummary();
});
</script>
@endpush
@endif