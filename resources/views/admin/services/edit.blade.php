@extends('layouts.admin')

@section('title', 'Edit Service')
@section('page-title', 'Edit Service')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.services.index') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.services.index') }}">Services</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <form action="{{ route('admin.services.update', $service) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Service: {{ $service->name }}</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="name">Service Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $service->name) }}" required>
                                @error('name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="slug">Slug</label>
                                <input type="text" class="form-control @error('slug') is-invalid @enderror" 
                                       id="slug" name="slug" value="{{ old('slug', $service->slug) }}">
                                @error('slug')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="4">{{ old('description', $service->description) }}</textarea>
                        @error('description')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="price">Price ($) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" class="form-control @error('price') is-invalid @enderror" 
                                       id="price" name="price" value="{{ old('price', $service->price) }}" required>
                                @error('price')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="duration">Duration (minutes)</label>
                                <input type="number" min="1" class="form-control @error('duration') is-invalid @enderror" 
                                       id="duration" name="duration" value="{{ old('duration', $service->duration) }}">
                                @error('duration')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="sort_order">Sort Order</label>
                                <input type="number" min="0" class="form-control @error('sort_order') is-invalid @enderror" 
                                       id="sort_order" name="sort_order" value="{{ old('sort_order', $service->sort_order) }}">
                                @error('sort_order')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="icon">Icon Class (FontAwesome)</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="icon-preview">
                                            <i class="{{ $service->icon ?: 'fas fa-icons' }}"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control @error('icon') is-invalid @enderror" 
                                           id="icon" name="icon" value="{{ old('icon', $service->icon) }}" placeholder="fas fa-star">
                                </div>
                                @error('icon')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status">Status <span class="text-danger">*</span></label>
                                <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="active" {{ old('status', $service->status) == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status', $service->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="image">Service Image</label>
                        @if($service->image)
                        <div class="mb-3">
                            <img src="{{ $service->image_url }}" alt="{{ $service->name }}" class="image-preview">
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
                            <label class="custom-file-label" for="image">{{ $service->image ? 'Choose new file' : 'Choose file' }}</label>
                        </div>
                        @error('image')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                        <div id="image-preview-container" class="mt-3" style="display: none;">
                            <img id="image-preview" src="" alt="Preview" class="image-preview">
                        </div>
                    </div>
                </div>
                
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Service
                    </button>
                    <a href="{{ route('admin.services.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Service Details</h3>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-5">Created:</dt>
                    <dd class="col-sm-7">{{ $service->created_at->format('M d, Y H:i') }}</dd>
                    
                    <dt class="col-sm-5">Updated:</dt>
                    <dd class="col-sm-7">{{ $service->updated_at->format('M d, Y H:i') }}</dd>
                    
                    <dt class="col-sm-5">Packages:</dt>
                    <dd class="col-sm-7">{{ $service->packages->count() }}</dd>
                </dl>
            </div>
        </div>

        @if($service->packages->count() > 0)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Assigned to Packages</h3>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @foreach($service->packages as $package)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>{{ $package->name }}</span>
                        <a href="{{ route('admin.packages.edit', $package) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-edit"></i>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Icon preview
    $('#icon').on('input', function() {
        const iconClass = $(this).val();
        if (iconClass) {
            $('#icon-preview').html('<i class="' + iconClass + '"></i>');
        } else {
            $('#icon-preview').html('<i class="fas fa-icons"></i>');
        }
    });

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
        } else {
            $('#image-preview-container').hide();
            $(this).next('.custom-file-label').text('Choose file');
        }
    });
});
</script>
@endpush