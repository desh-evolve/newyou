@extends('layouts.admin')

@section('title', 'View Package')
@section('page-title', 'Package Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.services.index') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.packages.index') }}">Packages</a></li>
    <li class="breadcrumb-item active">View</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    {{ $package->name }}
                    @if($package->is_featured)
                        <span class="badge badge-warning"><i class="fas fa-star"></i> Featured</span>
                    @endif
                </h3>
                <div class="card-tools">
                    <a href="{{ route('admin.packages.edit', $package) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        @if($package->image)
                            <img src="{{ $package->image_url }}" alt="{{ $package->name }}" class="img-fluid rounded">
                        @else
                            <div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="height: 200px; border-radius: 0.25rem;">
                                <i class="fas fa-image fa-4x"></i>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-8">
                        <dl class="row">
                            <dt class="col-sm-4">Slug:</dt>
                            <dd class="col-sm-8"><code>{{ $package->slug }}</code></dd>
                            
                            <dt class="col-sm-4">Price:</dt>
                            <dd class="col-sm-8">
                                @if($package->discount_price)
                                    <span class="text-danger"><del>{{ $package->formatted_price }}</del></span>
                                    <strong class="text-success ml-2">{{ $package->formatted_discount_price }}</strong>
                                    <span class="badge badge-success">{{ $package->discount_percentage }}% OFF</span>
                                @else
                                    <strong class="text-success">{{ $package->formatted_price }}</strong>
                                @endif
                            </dd>
                            
                            <dt class="col-sm-4">Validity:</dt>
                            <dd class="col-sm-8">{{ $package->validity_days ? $package->validity_days . ' days' : 'Unlimited' }}</dd>
                            
                            <dt class="col-sm-4">Status:</dt>
                            <dd class="col-sm-8">
                                <span class="status-badge status-{{ $package->status }}">{{ ucfirst($package->status) }}</span>
                            </dd>
                            
                            <dt class="col-sm-4">Services Value:</dt>
                            <dd class="col-sm-8">${{ number_format($package->services_value, 2) }}</dd>
                            
                            <dt class="col-sm-4">Customer Savings:</dt>
                            <dd class="col-sm-8 text-success"><strong>${{ number_format($package->savings, 2) }}</strong></dd>
                            
                            <dt class="col-sm-4">Created:</dt>
                            <dd class="col-sm-8">{{ $package->created_at->format('M d, Y H:i A') }}</dd>
                        </dl>
                    </div>
                </div>

                @if($package->description)
                <hr>
                <h5>Description</h5>
                <p class="text-muted">{{ $package->description }}</p>
                @endif
            </div>
        </div>

        <!-- Included Services -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-boxes"></i> Included Services ({{ $package->packageServices->count() }})</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.package-services.assign', ['package_id' => $package->id]) }}" class="btn btn-success btn-sm">
                        <i class="fas fa-edit"></i> Manage Services
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                @if($package->packageServices->count() > 0)
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Service</th>
                            <th width="15%">Quantity</th>
                            <th width="15%">Price</th>
                            <th width="15%">Total</th>
                            <th width="20%">Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($package->packageServices as $ps)
                        <tr>
                            <td>
                                <strong>{{ $ps->service->name }}</strong>
                                @if($ps->service->icon)
                                    <i class="{{ $ps->service->icon }} ml-2"></i>
                                @endif
                            </td>
                            <td>{{ $ps->quantity }}</td>
                            <td>
                                @if($ps->custom_price)
                                    <span class="text-info" title="Custom price">${{ number_format($ps->custom_price, 2) }}</span>
                                @else
                                    ${{ number_format($ps->service->price, 2) }}
                                @endif
                            </td>
                            <td><strong>{{ $ps->formatted_total_price }}</strong></td>
                            <td><small class="text-muted">{{ $ps->notes ?: '-' }}</small></td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-light">
                            <th colspan="3" class="text-right">Total Services Value:</th>
                            <th colspan="2"><strong>${{ number_format($package->services_value, 2) }}</strong></th>
                        </tr>
                    </tfoot>
                </table>
                @else
                <div class="p-4 text-center text-muted">
                    <i class="fas fa-inbox fa-2x mb-2"></i>
                    <p class="mb-0">No services assigned to this package yet.</p>
                    <a href="{{ route('admin.package-services.assign', ['package_id' => $package->id]) }}" class="btn btn-primary mt-3">
                        <i class="fas fa-plus"></i> Assign Services
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Quick Actions</h3>
            </div>
            <div class="card-body">
                <a href="{{ route('admin.packages.edit', $package) }}" class="btn btn-warning btn-block">
                    <i class="fas fa-edit"></i> Edit Package
                </a>
                <a href="{{ route('admin.package-services.assign', ['package_id' => $package->id]) }}" class="btn btn-success btn-block">
                    <i class="fas fa-link"></i> Manage Services
                </a>
                <button type="button" class="btn btn-{{ $package->status == 'active' ? 'secondary' : 'primary' }} btn-block" onclick="toggleStatus('{{ route('admin.packages.toggle-status', $package) }}', this)">
                    <i class="fas fa-toggle-{{ $package->status == 'active' ? 'off' : 'on' }}"></i> 
                    {{ $package->status == 'active' ? 'Deactivate' : 'Activate' }}
                </button>
                <hr>
                <button type="button" class="btn btn-danger btn-block" onclick="confirmDelete('delete-package-form', 'package')">
                    <i class="fas fa-trash"></i> Delete Package
                </button>
                <form id="delete-package-form" action="{{ route('admin.packages.destroy', $package) }}" method="POST" style="display: none;">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
        </div>

        <!-- Stats -->
        <div class="card">
            <div class="card-header bg-info text-white">
                <h3 class="card-title">Package Statistics</h3>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <h3 class="text-primary">{{ $package->packageServices->count() }}</h3>
                        <p class="text-muted mb-0">Services</p>
                    </div>
                    <div class="col-6">
                        <h3 class="text-success">{{ $package->discount_percentage }}%</h3>
                        <p class="text-muted mb-0">Discount</p>
                    </div>
                </div>
                <hr>
                <div class="row text-center">
                    <div class="col-6">
                        <h5>${{ number_format($package->services_value, 2) }}</h5>
                        <p class="text-muted mb-0">Services Value</p>
                    </div>
                    <div class="col-6">
                        <h5 class="text-success">${{ number_format($package->savings, 2) }}</h5>
                        <p class="text-muted mb-0">Customer Saves</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection