@extends('layouts.admin')

@section('title', 'Edit Tag')

@section('content-header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0">Edit Tag</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.blog.tags.index') }}">Tags</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Tag</h3>
                </div>
                <form action="{{ route('admin.blog.tags.update', $tag) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $tag->name) }}" required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="slug">Slug</label>
                            <input type="text" name="slug" id="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $tag->slug) }}">
                            @error('slug')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Update Tag
                        </button>
                        <a href="{{ route('admin.blog.tags.index') }}" class="btn btn-default">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Information</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <tr>
                            <td><strong>Posts using this tag</strong></td>
                            <td><span class="badge badge-info">{{ $tag->posts()->count() }}</span></td>
                        </tr>
                        <tr>
                            <td><strong>Created</strong></td>
                            <td>{{ $tag->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Updated</strong></td>
                            <td>{{ $tag->updated_at->format('M d, Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection