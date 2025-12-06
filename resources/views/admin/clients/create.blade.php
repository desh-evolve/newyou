{{-- resources/views/admin/clients/create.blade.php --}}

@extends('layouts.admin')

@section('title', 'Add New Client')

@section('content-header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">Add New Client</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.clients.index') }}">Clients</a></li>
            <li class="breadcrumb-item active">Add New</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<form action="{{ route('admin.clients.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
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
                                       value="{{ old('name') }}" 
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
                                       value="{{ old('email') }}" 
                                       placeholder="Enter email address"
                                       required>
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password">Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" 
                                           name="password" 
                                           id="password" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           placeholder="Enter password"
                                           required>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                @error('password')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <small class="text-muted">Minimum 8 characters</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password_confirmation">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" 
                                       name="password_confirmation" 
                                       id="password_confirmation" 
                                       class="form-control" 
                                       placeholder="Confirm password"
                                       required>
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
                                       value="{{ old('phone') }}" 
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
                                       value="{{ old('alternate_phone') }}" 
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
                                       value="{{ old('date_of_birth') }}"
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
                                    <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
                                    <option value="prefer_not_to_say" {{ old('gender') == 'prefer_not_to_say' ? 'selected' : '' }}>Prefer not to say</option>
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
                                    <option value="UTC" {{ old('timezone', 'UTC') == 'UTC' ? 'selected' : '' }}>UTC</option>
                                    <option value="America/New_York" {{ old('timezone') == 'America/New_York' ? 'selected' : '' }}>Eastern Time (US & Canada)</option>
                                    <option value="America/Chicago" {{ old('timezone') == 'America/Chicago' ? 'selected' : '' }}>Central Time (US & Canada)</option>
                                    <option value="America/Denver" {{ old('timezone') == 'America/Denver' ? 'selected' : '' }}>Mountain Time (US & Canada)</option>
                                    <option value="America/Los_Angeles" {{ old('timezone') == 'America/Los_Angeles' ? 'selected' : '' }}>Pacific Time (US & Canada)</option>
                                    <option value="Europe/London" {{ old('timezone') == 'Europe/London' ? 'selected' : '' }}>London</option>
                                    <option value="Europe/Paris" {{ old('timezone') == 'Europe/Paris' ? 'selected' : '' }}>Paris</option>
                                    <option value="Asia/Tokyo" {{ old('timezone') == 'Asia/Tokyo' ? 'selected' : '' }}>Tokyo</option>
                                    <option value="Asia/Shanghai" {{ old('timezone') == 'Asia/Shanghai' ? 'selected' : '' }}>Beijing</option>
                                    <option value="Asia/Kolkata" {{ old('timezone') == 'Asia/Kolkata' ? 'selected' : '' }}>Mumbai</option>
                                    <option value="Australia/Sydney" {{ old('timezone') == 'Australia/Sydney' ? 'selected' : '' }}>Sydney</option>
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
                                    <option value="email" {{ old('preferred_communication', 'email') == 'email' ? 'selected' : '' }}>Email</option>
                                    <option value="phone" {{ old('preferred_communication') == 'phone' ? 'selected' : '' }}>Phone</option>
                                    <option value="sms" {{ old('preferred_communication') == 'sms' ? 'selected' : '' }}>SMS</option>
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
                                  placeholder="Enter street address">{{ old('address') }}</textarea>
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
                                       value="{{ old('city') }}" 
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
                                       value="{{ old('state') }}" 
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
                                       value="{{ old('postal_code') }}" 
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
                                       value="{{ old('country') }}" 
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
                                  placeholder="What are the client's goals?">{{ old('goals') }}</textarea>
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
                                  placeholder="Any health-related notes or considerations">{{ old('health_notes') }}</textarea>
                        @error('health_notes')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="text-muted">This information is confidential and only visible to coaches/admins.</small>
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
                             src="{{ asset('images/default-avatar.png') }}" 
                             class="img-circle elevation-2" 
                             alt="Profile Image"
                             style="width: 150px; height: 150px; object-fit: cover;">
                    </div>
                    <div class="form-group">
                        <label for="profile_image" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-upload mr-1"></i> Choose Image
                        </label>
                        <input type="file" 
                               name="profile_image" 
                               id="profile_image" 
                               class="d-none" 
                               accept="image/jpeg,image/png,image/jpg,image/gif">
                        @error('profile_image')
                            <span class="text-danger d-block">{{ $message }}</span>
                        @enderror
                        <small class="text-muted d-block mt-2">Max size: 2MB. Formats: JPG, PNG, GIF</small>
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
                               value="{{ old('emergency_contact_name') }}" 
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
                               value="{{ old('emergency_contact_phone') }}" 
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
                        <div class="custom-control custom-switch">
                            <input type="checkbox" 
                                   class="custom-control-input" 
                                   id="status" 
                                   name="status" 
                                   value="active"
                                   {{ old('status', 'active') == 'active' ? 'checked' : '' }}>
                            <label class="custom-control-label" for="status">Active Account</label>
                        </div>
                        <small class="text-muted">Inactive clients cannot book appointments.</small>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary btn-lg btn-block">
                        <i class="fas fa-user-plus mr-2"></i>Create Client
                    </button>
                    <a href="{{ route('admin.clients.index') }}" class="btn btn-secondary btn-block">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </a>
                </div>
            </div>

            <!-- Help -->
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle mr-2"></i>Help
                    </h3>
                </div>
                <div class="card-body">
                    <p class="text-sm">
                        <strong>Required fields:</strong> Name, Email, Password
                    </p>
                    <p class="text-sm">
                        <strong>Password:</strong> Must be at least 8 characters long.
                    </p>
                    <p class="text-sm mb-0">
                        <strong>Note:</strong> The client will receive a welcome email with their login credentials.
                    </p>
                </div>
            </div>
        </div>
    </div>
</form>
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

    // Toggle password visibility
    $('#togglePassword').click(function() {
        var passwordInput = $('#password');
        var icon = $(this).find('i');
        
        if (passwordInput.attr('type') === 'password') {
            passwordInput.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            passwordInput.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Image preview
    $('#profile_image').change(function() {
        var file = this.files[0];
        
        if (file) {
            // Validate file size (2MB)
            if (file.size > 2 * 1024 * 1024) {
                alert('File size must be less than 2MB');
                $(this).val('');
                return;
            }
            
            // Validate file type
            var allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
            if (!allowedTypes.includes(file.type)) {
                alert('Only JPG, PNG, and GIF files are allowed');
                $(this).val('');
                return;
            }
            
            // Preview image
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#imagePreview').attr('src', e.target.result);
            }
            reader.readAsDataURL(file);
        }
    });

    // Password strength indicator (optional)
    $('#password').on('keyup', function() {
        var password = $(this).val();
        var strength = 0;
        
        if (password.length >= 8) strength++;
        if (password.match(/[a-z]+/)) strength++;
        if (password.match(/[A-Z]+/)) strength++;
        if (password.match(/[0-9]+/)) strength++;
        if (password.match(/[$@#&!]+/)) strength++;
        
        var strengthText = '';
        var strengthClass = '';
        
        switch(strength) {
            case 0:
            case 1:
                strengthText = 'Weak';
                strengthClass = 'text-danger';
                break;
            case 2:
            case 3:
                strengthText = 'Medium';
                strengthClass = 'text-warning';
                break;
            case 4:
            case 5:
                strengthText = 'Strong';
                strengthClass = 'text-success';
                break;
        }
        
        // You can add a strength indicator element if needed
    });
});
</script>
@endpush