{{-- resources/views/admin/clients/edit.blade.php --}}

@extends('layouts.admin')

@section('title', 'Edit Client - ' . $client->full_name)

@section('content-header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">Edit Client</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.clients.index') }}">Clients</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<form action="{{ route('admin.clients.update', $client) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="row">
        <!-- Main Form -->
        <div class="col-md-8">
            <!-- Account Information -->
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-circle mr-2"></i>Account Information
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Full Name <span class="text-danger">*</span></label>
                                <input type="text" 
                                       name="name" 
                                       id="name" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       value="{{ old('name', $client->user->name ?? '') }}" 
                                       placeholder="Enter full name"
                                       required>
                                @error('name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email Address <span class="text-danger">*</span></label>
                                <input type="email" 
                                       name="email" 
                                       id="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       value="{{ old('email', $client->user->email ?? '') }}" 
                                       placeholder="Enter email address"
                                       required>
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Personal Information -->
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-id-card mr-2"></i>Personal Information
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="text" 
                                       name="phone" 
                                       id="phone" 
                                       class="form-control @error('phone') is-invalid @enderror" 
                                       value="{{ old('phone', $client->phone) }}" 
                                       placeholder="Enter phone number">
                                @error('phone')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="alternate_phone">Alternate Phone</label>
                                <input type="text" 
                                       name="alternate_phone" 
                                       id="alternate_phone" 
                                       class="form-control @error('alternate_phone') is-invalid @enderror" 
                                       value="{{ old('alternate_phone', $client->alternate_phone) }}" 
                                       placeholder="Enter alternate phone">
                                @error('alternate_phone')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="date_of_birth">Date of Birth</label>
                                <input type="date" 
                                       name="date_of_birth" 
                                       id="date_of_birth" 
                                       class="form-control @error('date_of_birth') is-invalid @enderror" 
                                       value="{{ old('date_of_birth', $client->date_of_birth ? $client->date_of_birth->format('Y-m-d') : '') }}"
                                       max="{{ date('Y-m-d') }}">
                                @error('date_of_birth')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="gender">Gender</label>
                                <select name="gender" id="gender" class="form-control @error('gender') is-invalid @enderror">
                                    <option value="">-- Select Gender --</option>
                                    <option value="male" {{ old('gender', $client->gender) == 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('gender', $client->gender) == 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="other" {{ old('gender', $client->gender) == 'other' ? 'selected' : '' }}>Other</option>
                                    <option value="prefer_not_to_say" {{ old('gender', $client->gender) == 'prefer_not_to_say' ? 'selected' : '' }}>Prefer not to say</option>
                                </select>
                                @error('gender')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="timezone">Timezone</label>
                                <select name="timezone" id="timezone" class="form-control select2 @error('timezone') is-invalid @enderror">
                                    <option value="UTC" {{ old('timezone', $client->timezone) == 'UTC' ? 'selected' : '' }}>UTC</option>
                                    <option value="America/New_York" {{ old('timezone', $client->timezone) == 'America/New_York' ? 'selected' : '' }}>Eastern Time (US & Canada)</option>
                                    <option value="America/Chicago" {{ old('timezone', $client->timezone) == 'America/Chicago' ? 'selected' : '' }}>Central Time (US & Canada)</option>
                                    <option value="America/Denver" {{ old('timezone', $client->timezone) == 'America/Denver' ? 'selected' : '' }}>Mountain Time (US & Canada)</option>
                                    <option value="America/Los_Angeles" {{ old('timezone', $client->timezone) == 'America/Los_Angeles' ? 'selected' : '' }}>Pacific Time (US & Canada)</option>
                                    <option value="Europe/London" {{ old('timezone', $client->timezone) == 'Europe/London' ? 'selected' : '' }}>London</option>
                                    <option value="Europe/Paris" {{ old('timezone', $client->timezone) == 'Europe/Paris' ? 'selected' : '' }}>Paris</option>
                                    <option value="Asia/Tokyo" {{ old('timezone', $client->timezone) == 'Asia/Tokyo' ? 'selected' : '' }}>Tokyo</option>
                                    <option value="Asia/Shanghai" {{ old('timezone', $client->timezone) == 'Asia/Shanghai' ? 'selected' : '' }}>Beijing</option>
                                    <option value="Asia/Kolkata" {{ old('timezone', $client->timezone) == 'Asia/Kolkata' ? 'selected' : '' }}>Mumbai</option>
                                    <option value="Australia/Sydney" {{ old('timezone', $client->timezone) == 'Australia/Sydney' ? 'selected' : '' }}>Sydney</option>
                                </select>
                                @error('timezone')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="preferred_communication">Preferred Communication</label>
                                <select name="preferred_communication" id="preferred_communication" class="form-control @error('preferred_communication') is-invalid @enderror">
                                    <option value="email" {{ old('preferred_communication', $client->preferred_communication) == 'email' ? 'selected' : '' }}>Email</option>
                                    <option value="phone" {{ old('preferred_communication', $client->preferred_communication) == 'phone' ? 'selected' : '' }}>Phone</option>
                                    <option value="sms" {{ old('preferred_communication', $client->preferred_communication) == 'sms' ? 'selected' : '' }}>SMS</option>
                                </select>
                                @error('preferred_communication')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Address Information -->
            <div class="card card-secondary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-map-marker-alt mr-2"></i>Address Information
                    </h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="address">Street Address</label>
                        <textarea name="address" 
                                  id="address" 
                                  class="form-control @error('address') is-invalid @enderror" 
                                  rows="2" 
                                  placeholder="Enter street address">{{ old('address', $client->address) }}</textarea>
                        @error('address')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="city">City</label>
                                <input type="text" 
                                       name="city" 
                                       id="city" 
                                       class="form-control @error('city') is-invalid @enderror" 
                                       value="{{ old('city', $client->city) }}" 
                                       placeholder="Enter city">
                                @error('city')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="state">State / Province</label>
                                <input type="text" 
                                       name="state" 
                                       id="state" 
                                       class="form-control @error('state') is-invalid @enderror" 
                                       value="{{ old('state', $client->state) }}" 
                                       placeholder="Enter state/province">
                                @error('state')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="postal_code">Postal / ZIP Code</label>
                                <input type="text" 
                                       name="postal_code" 
                                       id="postal_code" 
                                       class="form-control @error('postal_code') is-invalid @enderror" 
                                       value="{{ old('postal_code', $client->postal_code) }}" 
                                       placeholder="Enter postal code">
                                @error('postal_code')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Country</label>
                                <input type="text" 
                                       name="country" 
                                       id="country" 
                                       class="form-control @error('country') is-invalid @enderror" 
                                       value="{{ old('country', $client->country) }}" 
                                       placeholder="Enter country">
                                @error('country')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-notes-medical mr-2"></i>Additional Information
                    </h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="goals">Goals & Objectives</label>
                        <textarea name="goals" 
                                  id="goals" 
                                  class="form-control @error('goals') is-invalid @enderror" 
                                  rows="3" 
                                  placeholder="What are the client's goals?">{{ old('goals', $client->goals) }}</textarea>
                        @error('goals')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="health_notes">Health Notes</label>
                        <textarea name="health_notes" 
                                  id="health_notes" 
                                  class="form-control @error('health_notes') is-invalid @enderror" 
                                  rows="3" 
                                  placeholder="Any health-related notes or considerations">{{ old('health_notes', $client->health_notes) }}</textarea>
                        @error('health_notes')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Profile Image -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-camera mr-2"></i>Profile Image
                    </h3>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <img id="imagePreview" 
                             src="{{ $client->profile_image ? asset('storage/' . $client->profile_image) : asset('images/default-avatar.png') }}" 
                             class="img-circle elevation-2" 
                             alt="Profile Image"
                             style="width: 150px; height: 150px; object-fit: cover;">
                    </div>
                    <div class="form-group">
                        <label for="profile_image" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-upload mr-1"></i> Change Image
                        </label>
                        <input type="file" 
                               name="profile_image" 
                               id="profile_image" 
                               class="d-none" 
                               accept="image/jpeg,image/png,image/jpg,image/gif">
                        @error('profile_image')
                            <span class="text-danger d-block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Emergency Contact -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-phone-alt mr-2"></i>Emergency Contact
                    </h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="emergency_contact_name">Contact Name</label>
                        <input type="text" 
                               name="emergency_contact_name" 
                               id="emergency_contact_name" 
                               class="form-control @error('emergency_contact_name') is-invalid @enderror" 
                               value="{{ old('emergency_contact_name', $client->emergency_contact_name) }}" 
                               placeholder="Emergency contact name">
                        @error('emergency_contact_name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="emergency_contact_phone">Contact Phone</label>
                        <input type="text" 
                               name="emergency_contact_phone" 
                               id="emergency_contact_phone" 
                               class="form-control @error('emergency_contact_phone') is-invalid @enderror" 
                               value="{{ old('emergency_contact_phone', $client->emergency_contact_phone) }}" 
                               placeholder="Emergency contact phone">
                        @error('emergency_contact_phone')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Status -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-toggle-on mr-2"></i>Status
                    </h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <select name="status" class="form-control @error('status') is-invalid @enderror">
                            <option value="active" {{ old('status', $client->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $client->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="text-muted">Inactive clients cannot book appointments.</small>
                    </div>
                </div>
            </div>

            <!-- Client Stats -->
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar mr-2"></i>Statistics
                    </h3>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            Total Appointments
                            <span class="badge badge-primary badge-pill">{{ $client->getTotalAppointments() }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            Completed
                            <span class="badge badge-success badge-pill">{{ $client->getCompletedAppointments() }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            Member Since
                            <span class="text-muted">{{ $client->created_at->format('M d, Y') }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary btn-lg btn-block">
                        <i class="fas fa-save mr-2"></i>Update Client
                    </button>
                    <a href="{{ route('admin.clients.show', $client) }}" class="btn btn-info btn-block">
                        <i class="fas fa-eye mr-2"></i>View Profile
                    </a>
                    <a href="{{ route('admin.clients.index') }}" class="btn btn-secondary btn-block">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </a>
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="card card-outline card-danger">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-exclamation-triangle mr-2"></i>Danger Zone
                    </h3>
                </div>
                <div class="card-body">
                    <button type="button" class="btn btn-danger btn-block" data-toggle="modal" data-target="#deleteModal">
                        <i class="fas fa-trash mr-2"></i>Delete Client
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title text-white">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Delete Client
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this client?</p>
                <p class="mb-0"><strong>{{ $client->full_name }}</strong></p>
                <p class="text-muted">{{ $client->email }}</p>
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-info-circle mr-2"></i>
                    This action will also affect all appointments associated with this client.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form action="{{ route('admin.clients.destroy', $client) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash mr-2"></i>Delete Permanently
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4'
    });

    // Image preview
    $('#profile_image').change(function() {
        var file = this.files[0];
        
        if (file) {
            if (file.size > 2 * 1024 * 1024) {
                alert('File size must be less than 2MB');
                $(this).val('');
                return;
            }
            
            var allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
            if (!allowedTypes.includes(file.type)) {
                alert('Only JPG, PNG, and GIF files are allowed');
                $(this).val('');
                return;
            }
            
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#imagePreview').attr('src', e.target.result);
            }
            reader.readAsDataURL(file);
        }
    });
});
</script>
@endpush