@extends('layouts.admin')

@section('title', 'View Service')
@section('page-title', 'Service Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.services.index') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.services.index') }}">Services</a></li>
    <li class="breadcrumb-item active">View</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ $service->name }}</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.services.edit', $service) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        @if($service->image)
                            <img src="{{ $service->image_url }}" alt="{{ $service->name }}" class="img-fluid rounded">
                        @else
                            <div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="height: 200px; border-radius: 0.25rem;">
                                <i class="fas fa-image fa-4x"></i>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-8">
                        <dl class="row">
                            <dt class="col-sm-4">Slug:</dt>
                            <dd class="col-sm-8"><code>{{ $service->slug }}</code></dd>
                            
                            <dt class="col-sm-4">Price:</dt>
                            <dd class="col-sm-8"><strong class="text-success">{{ $service->formatted_price }}</strong></dd>
                            
                            <dt class="col-sm-4">Duration:</dt>
                            <dd class="col-sm-8">{{ $service->formatted_duration }}</dd>
                            
                            <dt class="col-sm-4">Icon:</dt>
                            <dd class="col-sm-8">
                                @if($service->icon)
                                    <i class="{{ $service->icon }}"></i> <code>{{ $service->icon }}</code>
                                @else
                                    <span class="text-muted">Not set</span>
                                @endif
                            </dd>
                            
                            <dt class="col-sm-4">Status:</dt>
                            <dd class="col-sm-8">
                                <span class="status-badge status-{{ $service->status }}">
                                    {{ ucfirst($service->status) }}
                                </span>
                            </dd>
                            
                            <dt class="col-sm-4">Sort Order:</dt>
                            <dd class="col-sm-8">{{ $service->sort_order }}</dd>
                            
                            <dt class="col-sm-4">Created:</dt>
                            <dd class="col-sm-8">{{ $service->created_at->format('M d, Y H:i A') }}</dd>
                            
                            <dt class="col-sm-4">Updated:</dt>
                            <dd class="col-sm-8">{{ $service->updated_at->format('M d, Y H:i A') }}</dd>
                        </dl>
                    </div>
                </div>

                @if($service->description)
                <hr>
                <h5>Description</h5>
                <p class="text-muted">{{ $service->description }}</p>
                @endif
            </div>
            <div class="card-footer">
                <a href="{{ route('admin.services.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
                <button type="button" class="btn btn-danger float-right" onclick="confirmDelete('delete-service-form', 'service')">
                    <i class="fas fa-trash"></i> Delete Service
                </button>
                <form id="delete-service-form" action="{{ route('admin.services.destroy', $service) }}" method="POST" style="display: none;">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Assigned Packages -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-box"></i> Assigned to Packages ({{ $service->packages->count() }})
                </h3>
            </div>
            <div class="card-body p-0">
                @if($service->packages->count() > 0)
                <ul class="list-group list-group-flush">
                    @foreach($service->packages as $package)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong>{{ $package->name }}</strong>
                            <br>
                            <small class="text-muted">
                                Qty: {{ $package->pivot->quantity }} | 
                                Price: {{ $package->pivot->custom_price ? '$'.number_format($package->pivot->custom_price, 2) : 'Default' }}
                            </small>
                        </div>
                        <a href="{{ route('admin.packages.show', $package) }}" class="btn btn-sm btn-outline-info">
                            <i class="fas fa-eye"></i>
                        </a>
                    </li>
                    @endforeach
                </ul>
                @else
                <div class="p-4 text-center text-muted">
                    <i class="fas fa-inbox fa-2x mb-2"></i>
                    <p class="mb-0">Not assigned to any packages yet.</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-bar"></i> Quick Stats</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 text-center">
                        <h3 class="text-primary">{{ $service->packages->count() }}</h3>
                        <p class="text-muted mb-0">Packages</p>
                    </div>
                    <div class="col-6 text-center">
                        <h3 class="text-success">{{ $service->formatted_price }}</h3>
                        <p class="text-muted mb-0">Price</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection