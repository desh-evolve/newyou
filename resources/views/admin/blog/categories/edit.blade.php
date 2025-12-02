@extends('layouts.admin')

@section('title', 'Edit Category')

@section('content-header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0">Edit Category</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.blog.categories.index') }}">Categories</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </div>
    </div>
@endsection

@section('content')
    <form action="{{ route('admin.blog.categories.update', $category) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Category Details</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $category->name) }}" required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="slug">Slug</label>
                            <input type="text" name="slug" id="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $category->slug) }}">
                            @error('slug')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="4">{{ old('description', $category->description) }}</textarea>
                            @error('description')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Settings</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="image">Image</label>
                            @if($category->image)
                                <div class="mb-2 image-upload-wrapper">
                                    <img src="{{ Storage::url($category->image) }}" class="img-thumbnail" style="max-height: 150px;" id="current-image">
                                    <button type="button" class="btn btn-danger btn-sm remove-btn" onclick="removeImage()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    <input type="hidden" name="remove_image" id="remove_image" value="0">
                                </div>
                            @endif
                            <div class="custom-file">
                                <input type="file" name="image" id="image" class="custom-file-input @error('image') is-invalid @enderror" accept="image/*" onchange="previewImage(this)">
                                <label class="custom-file-label" for="image">Choose new file</label>
                            </div>
                            <img id="image-preview" class="mt-2 img-thumbnail d-none" style="max-height: 150px;">
                            @error('image')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="order">Display Order</label>
                            <input type="number" name="order" id="order" class="form-control @error('order') is-invalid @enderror" value="{{ old('order', $category->order) }}" min="0">
                            @error('order')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" name="is_active" id="is_active" class="custom-control-input" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">Active</label>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Update Category
                        </button>
                        <a href="{{ route('admin.blog.categories.index') }}" class="btn btn-default">Cancel</a>
                    </div>
                </div>

                <!-- Info Card -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Information</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <tr>
                                <td>Posts</td>
                                <td><span class="badge badge-info">{{ $category->posts_count ?? $category->posts()->count() }}</span></td>
                            </tr>
                            <tr>
                                <td>Created</td>
                                <td>{{ $category->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td>Updated</td>
                                <td>{{ $category->updated_at->format('M d, Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
function previewImage(input) {
    const preview = document.getElementById('image-preview');
    const label = input.nextElementSibling;
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('d-none');
        };
        reader.readAsDataURL(input.files[0]);
        label.textContent = input.files[0].name;
    }
}

function removeImage() {
    document.getElementById('remove_image').value = '1';
    document.getElementById('current-image').parentElement.style.display = 'none';
}
</script>
@endpush