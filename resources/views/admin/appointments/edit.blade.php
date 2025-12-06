{{-- resources/views/admin/appointments/edit.blade.php --}}

@extends('layouts.admin')

@section('title', 'Edit Appointment - ' . $appointment->appointment_number)

@section('content-header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">Edit Appointment</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.appointments.index') }}">Appointments</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.appointments.show', $appointment) }}">{{ $appointment->appointment_number }}</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<form action="{{ route('admin.appointments.update', $appointment) }}" method="POST">
    @csrf
    @method('PUT')
    
    <div class="row">
        <!-- Main Form -->
        <div class="col-md-8">
            <!-- Appointment Info (Read Only) -->
            <div class="card card-secondary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle mr-2"></i>Appointment Information
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Appointment Number</label>
                                <input type="text" class="form-control" value="{{ $appointment->appointment_number }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Client</label>
                                <input type="text" class="form-control" value="{{ $appointment->client->full_name ?? 'N/A' }}" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Date</label>
                                <input type="text" class="form-control" value="{{ $appointment->formatted_date }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Time</label>
                                <input type="text" class="form-control" value="{{ $appointment->formatted_time }}" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Coach</label>
                                <input type="text" class="form-control" value="{{ $appointment->coach->name ?? 'N/A' }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Duration</label>
                                <input type="text" class="form-control" value="{{ $appointment->duration_minutes }} minutes" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Editable Fields -->
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-edit mr-2"></i>Edit Details
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Appointment Type -->
                    <div class="form-group">
                        <label>Appointment Type <span class="text-danger">*</span></label>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="custom-control custom-radio">
                                    <input type="radio" 
                                           id="typeVideo" 
                                           name="appointment_type" 
                                           value="video_call" 
                                           class="custom-control-input" 
                                           {{ old('appointment_type', $appointment->appointment_type) == 'video_call' ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="typeVideo">
                                        <i class="fas fa-video mr-1 text-info"></i> Video Call
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="custom-control custom-radio">
                                    <input type="radio" 
                                           id="typePhone" 
                                           name="appointment_type" 
                                           value="phone_call" 
                                           class="custom-control-input" 
                                           {{ old('appointment_type', $appointment->appointment_type) == 'phone_call' ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="typePhone">
                                        <i class="fas fa-phone mr-1 text-success"></i> Phone Call
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="custom-control custom-radio">
                                    <input type="radio" 
                                           id="typeInPerson" 
                                           name="appointment_type" 
                                           value="in_person" 
                                           class="custom-control-input" 
                                           {{ old('appointment_type', $appointment->appointment_type) == 'in_person' ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="typeInPerson">
                                        <i class="fas fa-user mr-1 text-primary"></i> In Person
                                    </label>
                                </div>
                            </div>
                        </div>
                        @error('appointment_type')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Meeting Link (for video calls) -->
                    <div class="form-group" id="meetingLinkGroup">
                        <label for="meeting_link">Meeting Link</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-link"></i></span>
                            </div>
                            <input type="url" 
                                   name="meeting_link" 
                                   id="meeting_link" 
                                   class="form-control @error('meeting_link') is-invalid @enderror" 
                                   value="{{ old('meeting_link', $appointment->meeting_link) }}" 
                                   placeholder="https://zoom.us/j/...">
                        </div>
                        @error('meeting_link')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                        <small class="text-muted">Zoom, Google Meet, or other video conferencing link</small>
                    </div>

                    <!-- Meeting Location (for in-person) -->
                    <div class="form-group" id="meetingLocationGroup" style="display: none;">
                        <label for="meeting_location">Meeting Location</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                            </div>
                            <input type="text" 
                                   name="meeting_location" 
                                   id="meeting_location" 
                                   class="form-control @error('meeting_location') is-invalid @enderror" 
                                   value="{{ old('meeting_location', $appointment->meeting_location) }}" 
                                   placeholder="Enter meeting address">
                        </div>
                        @error('meeting_location')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Admin Notes -->
                    <div class="form-group">
                        <label for="admin_notes">Admin Notes</label>
                        <textarea name="admin_notes" 
                                  id="admin_notes" 
                                  class="form-control @error('admin_notes') is-invalid @enderror" 
                                  rows="4" 
                                  placeholder="Internal notes (not visible to client)">{{ old('admin_notes', $appointment->admin_notes) }}</textarea>
                        @error('admin_notes')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                        <small class="text-muted">These notes are only visible to admins and coaches.</small>
                    </div>
                </div>
            </div>

            <!-- Status Update -->
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-sync-alt mr-2"></i>Update Status
                    </h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="appointment_status">Appointment Status</label>
                        <select name="appointment_status" 
                                id="appointment_status" 
                                class="form-control @error('appointment_status') is-invalid @enderror">
                            <option value="pending" {{ old('appointment_status', $appointment->appointment_status) == 'pending' ? 'selected' : '' }}>
                                Pending
                            </option>
                            <option value="confirmed" {{ old('appointment_status', $appointment->appointment_status) == 'confirmed' ? 'selected' : '' }}>
                                Confirmed
                            </option>
                            <option value="in_progress" {{ old('appointment_status', $appointment->appointment_status) == 'in_progress' ? 'selected' : '' }}>
                                In Progress
                            </option>
                            <option value="completed" {{ old('appointment_status', $appointment->appointment_status) == 'completed' ? 'selected' : '' }}>
                                Completed
                            </option>
                            <option value="cancelled" {{ old('appointment_status', $appointment->appointment_status) == 'cancelled' ? 'selected' : '' }}>
                                Cancelled
                            </option>
                            <option value="no_show" {{ old('appointment_status', $appointment->appointment_status) == 'no_show' ? 'selected' : '' }}>
                                No Show
                            </option>
                            <option value="rescheduled" {{ old('appointment_status', $appointment->appointment_status) == 'rescheduled' ? 'selected' : '' }}>
                                Rescheduled
                            </option>
                        </select>
                        @error('appointment_status')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Cancellation Reason (shown if cancelled) -->
                    <div class="form-group" id="cancellationReasonGroup" style="display: none;">
                        <label for="cancellation_reason">Cancellation Reason</label>
                        <textarea name="cancellation_reason" 
                                  id="cancellation_reason" 
                                  class="form-control" 
                                  rows="3" 
                                  placeholder="Please provide a reason for cancellation">{{ old('cancellation_reason', $appointment->cancellation_reason) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Current Status -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle mr-2"></i>Current Status
                    </h3>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <th>Status:</th>
                            <td>{!! $appointment->status_badge !!}</td>
                        </tr>
                        <tr>
                            <th>Payment:</th>
                            <td>{!! $appointment->payment_badge !!}</td>
                        </tr>
                        <tr>
                            <th>Type:</th>
                            <td>{!! $appointment->type_badge !!}</td>
                        </tr>
                        <tr>
                            <th>Created:</th>
                            <td>{{ $appointment->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                        @if($appointment->confirmed_at)
                        <tr>
                            <th>Confirmed:</th>
                            <td>{{ $appointment->confirmed_at->format('M d, Y H:i') }}</td>
                        </tr>
                        @endif
                        @if($appointment->completed_at)
                        <tr>
                            <th>Completed:</th>
                            <td>{{ $appointment->completed_at->format('M d, Y H:i') }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>

            <!-- Package/Service Info -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-box mr-2"></i>Package / Service
                    </h3>
                </div>
                <div class="card-body">
                    @if($appointment->package)
                        <p class="mb-2">
                            <span class="badge badge-primary">Package</span>
                            <strong>{{ $appointment->package->name }}</strong>
                        </p>
                    @endif
                    @if($appointment->service)
                        <p class="mb-2">
                            <span class="badge badge-secondary">Service</span>
                            <strong>{{ $appointment->service->name }}</strong>
                        </p>
                    @endif
                    @if(!$appointment->package && !$appointment->service)
                        <p class="text-muted mb-0">No package or service selected.</p>
                    @endif
                </div>
            </div>

            <!-- Payment Info -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-dollar-sign mr-2"></i>Payment Details
                    </h3>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <td>Subtotal:</td>
                            <td class="text-right">${{ number_format($appointment->amount, 2) }}</td>
                        </tr>
                        @if($appointment->discount_amount > 0)
                        <tr>
                            <td>Discount:</td>
                            <td class="text-right text-success">-${{ number_format($appointment->discount_amount, 2) }}</td>
                        </tr>
                        @endif
                        <tr class="border-top">
                            <th>Total:</th>
                            <th class="text-right">${{ number_format($appointment->final_amount, 2) }}</th>
                        </tr>
                        <tr>
                            <td>Status:</td>
                            <td class="text-right">{!! $appointment->payment_badge !!}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Client Notes (Read Only) -->
            @if($appointment->client_notes)
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-comment mr-2"></i>Client Notes
                    </h3>
                </div>
                <div class="card-body">
                    <p class="mb-0 text-muted">{{ $appointment->client_notes }}</p>
                </div>
            </div>
            @endif

            <!-- Actions -->
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary btn-lg btn-block">
                        <i class="fas fa-save mr-2"></i>Save Changes
                    </button>
                    <a href="{{ route('admin.appointments.show', $appointment) }}" class="btn btn-info btn-block">
                        <i class="fas fa-eye mr-2"></i>View Details
                    </a>
                    <a href="{{ route('admin.appointments.index') }}" class="btn btn-secondary btn-block">
                        <i class="fas fa-arrow-left mr-2"></i>Back to List
                    </a>
                </div>
            </div>

            <!-- Danger Zone -->
            @if(!in_array($appointment->appointment_status, ['completed', 'cancelled']))
            <div class="card card-outline card-danger">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-exclamation-triangle mr-2"></i>Danger Zone
                    </h3>
                </div>
                <div class="card-body">
                    <button type="button" 
                            class="btn btn-danger btn-block" 
                            data-toggle="modal" 
                            data-target="#deleteModal">
                        <i class="fas fa-trash mr-2"></i>Delete Appointment
                    </button>
                </div>
            </div>
            @endif
        </div>
    </div>
</form>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title text-white">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Delete Appointment
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this appointment?</p>
                <div class="alert alert-warning">
                    <strong>Appointment:</strong> {{ $appointment->appointment_number }}<br>
                    <strong>Client:</strong> {{ $appointment->client->full_name ?? 'N/A' }}<br>
                    <strong>Date:</strong> {{ $appointment->formatted_date }} at {{ $appointment->formatted_time }}
                </div>
                <p class="text-danger mb-0">
                    <i class="fas fa-exclamation-circle mr-1"></i>
                    This action cannot be undone. The time slot will be released.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form action="{{ route('admin.appointments.destroy', $appointment) }}" method="POST" class="d-inline">
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

@push('scripts')
<script>
$(document).ready(function() {
    // Toggle meeting link/location based on appointment type
    function toggleMeetingFields() {
        var type = $('input[name="appointment_type"]:checked').val();
        
        if (type === 'video_call') {
            $('#meetingLinkGroup').show();
            $('#meetingLocationGroup').hide();
        } else if (type === 'in_person') {
            $('#meetingLinkGroup').hide();
            $('#meetingLocationGroup').show();
        } else {
            $('#meetingLinkGroup').hide();
            $('#meetingLocationGroup').hide();
        }
    }
    
    // Toggle cancellation reason based on status
    function toggleCancellationReason() {
        var status = $('#appointment_status').val();
        
        if (status === 'cancelled') {
            $('#cancellationReasonGroup').show();
        } else {
            $('#cancellationReasonGroup').hide();
        }
    }
    
    // Initial toggle
    toggleMeetingFields();
    toggleCancellationReason();
    
    // On change events
    $('input[name="appointment_type"]').change(toggleMeetingFields);
    $('#appointment_status').change(toggleCancellationReason);
    
    // Confirm status change to cancelled
    $('#appointment_status').change(function() {
        var status = $(this).val();
        
        if (status === 'cancelled') {
            if (!confirm('Are you sure you want to cancel this appointment? This will notify the client.')) {
                $(this).val('{{ $appointment->appointment_status }}');
                toggleCancellationReason();
            }
        }
    });
});
</script>
@endpush