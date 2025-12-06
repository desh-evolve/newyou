{{-- resources/views/admin/appointments/show.blade.php --}}

@extends('layouts.admin')

@section('title', 'Appointment Details - ' . $appointment->appointment_number)

@section('content-header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">Appointment Details</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.appointments.index') }}">Appointments</a></li>
            <li class="breadcrumb-item active">{{ $appointment->appointment_number }}</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<div class="row">
    <!-- Main Info -->
    <div class="col-md-8">
        <!-- Appointment Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-calendar-check mr-2"></i>
                    {{ $appointment->appointment_number }}
                </h3>
                <div class="card-tools">
                    {!! $appointment->status_badge !!}
                    {!! $appointment->payment_badge !!}
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th style="width: 140px;">Date:</th>
                                <td>
                                    <i class="fas fa-calendar mr-2 text-primary"></i>
                                    {{ $appointment->formatted_date }}
                                </td>
                            </tr>
                            <tr>
                                <th>Time:</th>
                                <td>
                                    <i class="fas fa-clock mr-2 text-primary"></i>
                                    {{ $appointment->formatted_time }}
                                </td>
                            </tr>
                            <tr>
                                <th>Duration:</th>
                                <td>{{ $appointment->duration_minutes }} minutes</td>
                            </tr>
                            <tr>
                                <th>Type:</th>
                                <td>{!! $appointment->type_badge !!}</td>
                            </tr>
                            @if($appointment->meeting_link)
                            <tr>
                                <th>Meeting Link:</th>
                                <td>
                                    <a href="{{ $appointment->meeting_link }}" target="_blank">
                                        {{ Str::limit($appointment->meeting_link, 40) }}
                                        <i class="fas fa-external-link-alt ml-1"></i>
                                    </a>
                                </td>
                            </tr>
                            @endif
                            @if($appointment->meeting_location)
                            <tr>
                                <th>Location:</th>
                                <td>{{ $appointment->meeting_location }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th style="width: 140px;">Package:</th>
                                <td>
                                    @if($appointment->package)
                                        <span class="badge badge-primary">{{ $appointment->package->name }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Service:</th>
                                <td>
                                    @if($appointment->service)
                                        <span class="badge badge-secondary">{{ $appointment->service->name }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Amount:</th>
                                <td>${{ number_format($appointment->amount, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Discount:</th>
                                <td>${{ number_format($appointment->discount_amount, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Final Amount:</th>
                                <td><strong class="text-success">${{ number_format($appointment->final_amount, 2) }}</strong></td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($appointment->client_notes)
                <hr>
                <div class="row">
                    <div class="col-12">
                        <h6><i class="fas fa-sticky-note mr-2"></i>Client Notes:</h6>
                        <p class="text-muted">{{ $appointment->client_notes }}</p>
                    </div>
                </div>
                @endif

                @if($appointment->admin_notes)
                <hr>
                <div class="row">
                    <div class="col-12">
                        <h6><i class="fas fa-user-shield mr-2"></i>Admin Notes:</h6>
                        <p class="text-muted">{{ $appointment->admin_notes }}</p>
                    </div>
                </div>
                @endif

                @if($appointment->cancellation_reason)
                <hr>
                <div class="alert alert-danger">
                    <h6><i class="fas fa-times-circle mr-2"></i>Cancellation Reason:</h6>
                    <p class="mb-0">{{ $appointment->cancellation_reason }}</p>
                    <small class="text-muted">
                        Cancelled by {{ $appointment->cancelledByUser->name ?? 'Unknown' }} 
                        on {{ $appointment->cancelled_at->format('M d, Y H:i A') }}
                    </small>
                </div>
                @endif
            </div>
            <div class="card-footer">
                <div class="row">
                    <div class="col-md-6">
                        <!-- Status Actions -->
                        @if($appointment->appointment_status === 'pending')
                            <form action="{{ route('admin.appointments.confirm', $appointment) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check"></i> Confirm Appointment
                                </button>
                            </form>
                        @endif

                        @if($appointment->appointment_status === 'confirmed' && $appointment->can_start)
                            <form action="{{ route('admin.appointments.start', $appointment) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-play"></i> Start Session
                                </button>
                            </form>
                        @endif

                        @if($appointment->appointment_status === 'in_progress')
                            <form action="{{ route('admin.appointments.complete', $appointment) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check-circle"></i> Complete Session
                                </button>
                            </form>
                        @endif

                        @if(in_array($appointment->appointment_status, ['pending', 'confirmed']))
                            <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#cancelModal">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        @endif

                        @if($appointment->appointment_status === 'confirmed' && $appointment->is_past)
                            <form action="{{ route('admin.appointments.no-show', $appointment) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-secondary">
                                    <i class="fas fa-user-slash"></i> Mark No-Show
                                </button>
                            </form>
                        @endif
                    </div>
                    <div class="col-md-6 text-right">
                        <a href="{{ route('admin.appointments.edit', $appointment) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('admin.appointments.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-credit-card mr-2"></i>Payment Information</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th>Payment Status:</th>
                                <td>{!! $appointment->payment_badge !!}</td>
                            </tr>
                            <tr>
                                <th>Amount:</th>
                                <td>${{ number_format($appointment->final_amount, 2) }}</td>
                            </tr>
                            @if($appointment->stripe_payment_intent_id)
                            <tr>
                                <th>Stripe Payment ID:</th>
                                <td><code>{{ $appointment->stripe_payment_intent_id }}</code></td>
                            </tr>
                            @endif
                        </table>
                    </div>
                    <div class="col-md-6">
                        @if($appointment->payment_status === 'pending')
                            <div class="alert alert-warning">
                                <h6><i class="fas fa-exclamation-triangle mr-2"></i>Payment Pending</h6>
                                <p class="mb-2">This appointment has not been paid yet.</p>
                                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#markPaidModal">
                                    <i class="fas fa-check"></i> Mark as Paid
                                </button>
                            </div>
                        @elseif($appointment->payment_status === 'paid')
                            <div class="alert alert-success">
                                <h6><i class="fas fa-check-circle mr-2"></i>Payment Received</h6>
                                <p class="mb-2">Payment has been successfully processed.</p>
                                @if($appointment->appointment_status !== 'refunded')
                                <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#refundModal">
                                    <i class="fas fa-undo"></i> Process Refund
                                </button>
                                @endif
                            </div>
                        @elseif($appointment->payment_status === 'refunded')
                            <div class="alert alert-info">
                                <h6><i class="fas fa-undo mr-2"></i>Refunded</h6>
                                <p class="mb-0">Payment has been refunded to the client.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-sticky-note mr-2"></i>Session Notes</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addNoteModal">
                        <i class="fas fa-plus"></i> Add Note
                    </button>
                </div>
            </div>
            <div class="card-body">
                @forelse($appointment->notes as $note)
                    <div class="card mb-3 {{ $note->is_pinned ? 'border-warning' : '' }}" id="note-{{ $note->id }}">
                        <div class="card-header py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    @if($note->is_pinned)
                                        <i class="fas fa-thumbtack text-warning mr-2"></i>
                                    @endif
                                    <strong>{{ $note->title ?: 'Untitled Note' }}</strong>
                                    {!! $note->type_badge !!}
                                    {!! $note->visibility_badge !!}
                                </div>
                                <div>
                                    <small class="text-muted">
                                        By {{ $note->coach->name ?? 'Unknown' }} • {{ $note->created_at->diffForHumans() }}
                                    </small>
                                    <div class="btn-group btn-group-sm ml-2">
                                        <button type="button" 
                                                class="btn btn-outline-secondary toggle-pin-btn" 
                                                data-note-id="{{ $note->id }}"
                                                data-url="{{ route('admin.appointments.notes.toggle-pin', [$appointment->id, $note->id]) }}"
                                                title="Toggle Pin">
                                            <i class="fas fa-thumbtack"></i>
                                        </button>
                                        <a href="{{ route('admin.appointments.notes.edit', [$appointment->id, $note->id]) }}" 
                                        class="btn btn-outline-primary" 
                                        title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-outline-danger delete-note-btn" 
                                                data-note-id="{{ $note->id }}"
                                                data-url="{{ route('admin.appointments.notes.destroy', [$appointment->id, $note->id]) }}"
                                                title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body py-2">
                            {!! nl2br(e($note->note_content)) !!}
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-sticky-note fa-3x mb-3"></i>
                        <p>No notes added yet.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-md-4">
        <!-- Client Info Card -->
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user mr-2"></i>Client Information</h3>
            </div>
            <div class="card-body box-profile">
                <div class="text-center mb-3">
                    <img class="profile-user-img img-fluid img-circle" 
                         src="{{ $appointment->client->profile_image ? asset('storage/' . $appointment->client->profile_image) : asset('images/default-avatar.png') }}"
                         alt="Client profile">
                </div>
                <h3 class="profile-username text-center">{{ $appointment->client->full_name }}</h3>
                <p class="text-muted text-center">{{ $appointment->client->email }}</p>

                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Phone</b> 
                        <a class="float-right">{{ $appointment->client->phone ?: 'N/A' }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Location</b> 
                        <a class="float-right">{{ $appointment->client->city ?: 'N/A' }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Total Appointments</b> 
                        <a class="float-right">{{ $appointment->client->getTotalAppointments() }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Completed</b> 
                        <a class="float-right">{{ $appointment->client->getCompletedAppointments() }}</a>
                    </li>
                </ul>

                <a href="{{ route('admin.clients.show', $appointment->client) }}" class="btn btn-primary btn-block">
                    <i class="fas fa-eye"></i> View Full Profile
                </a>
            </div>
        </div>

        <!-- Coach Info Card -->
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-tie mr-2"></i>Coach Information</h3>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <img class="img-circle mr-3" 
                         src="{{ asset('images/default-avatar.png') }}"
                         alt="Coach" style="width: 50px; height: 50px;">
                    <div>
                        <h5 class="mb-0">{{ $appointment->coach->name }}</h5>
                        <small class="text-muted">{{ $appointment->coach->email }}</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Timeline Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-history mr-2"></i>Timeline</h3>
            </div>
            <div class="card-body">
                <div class="timeline timeline-inverse">
                    <div class="time-label">
                        <span class="bg-success">Created</span>
                    </div>
                    <div>
                        <i class="fas fa-calendar-plus bg-primary"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="far fa-clock"></i> {{ $appointment->created_at->format('M d, Y H:i') }}</span>
                            <h3 class="timeline-header">Appointment Created</h3>
                        </div>
                    </div>

                    @if($appointment->confirmed_at)
                    <div>
                        <i class="fas fa-check bg-success"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="far fa-clock"></i> {{ $appointment->confirmed_at->format('M d, Y H:i') }}</span>
                            <h3 class="timeline-header">Appointment Confirmed</h3>
                        </div>
                    </div>
                    @endif

                    @if($appointment->completed_at)
                    <div>
                        <i class="fas fa-check-circle bg-success"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="far fa-clock"></i> {{ $appointment->completed_at->format('M d, Y H:i') }}</span>
                            <h3 class="timeline-header">Session Completed</h3>
                        </div>
                    </div>
                    @endif

                    @if($appointment->cancelled_at)
                    <div>
                        <i class="fas fa-times bg-danger"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="far fa-clock"></i> {{ $appointment->cancelled_at->format('M d, Y H:i') }}</span>
                            <h3 class="timeline-header">Appointment Cancelled</h3>
                        </div>
                    </div>
                    @endif

                    <div>
                        <i class="far fa-clock bg-gray"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.appointments.cancel', $appointment) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Cancel Appointment</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        This will cancel the appointment and notify the client.
                        @if($appointment->payment_status === 'paid')
                            <br><strong>A refund will be processed automatically.</strong>
                        @endif
                    </div>
                    <div class="form-group">
                        <label>Cancellation Reason <span class="text-danger">*</span></label>
                        <textarea name="cancellation_reason" class="form-control" rows="3" required 
                                  placeholder="Please provide a reason for cancellation..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times"></i> Cancel Appointment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Mark as Paid Modal -->
<div class="modal fade" id="markPaidModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.payments.mark-paid', $appointment) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Mark as Paid</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        Amount: <strong>${{ number_format($appointment->final_amount, 2) }}</strong>
                    </div>
                    <div class="form-group">
                        <label>Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="2" 
                                  placeholder="e.g., Cash payment received, Check #123, etc."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Mark as Paid
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Refund Modal -->
<div class="modal fade" id="refundModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.payments.refund', $appointment) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Process Refund</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        This will process a refund through Stripe.
                    </div>
                    <div class="form-group">
                        <label>Refund Amount (Leave empty for full refund)</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$</span>
                            </div>
                            <input type="number" name="amount" class="form-control" step="0.01" min="0" 
                                   max="{{ $appointment->final_amount }}" 
                                   placeholder="{{ number_format($appointment->final_amount, 2) }}">
                        </div>
                        <small class="text-muted">Max: ${{ number_format($appointment->final_amount, 2) }}</small>
                    </div>
                    <div class="form-group">
                        <label>Refund Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="2" required 
                                  placeholder="Please provide a reason for the refund..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-undo"></i> Process Refund
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Note Modal -->
<div class="modal fade" id="addNoteModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.appointments.notes.store', $appointment) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Session Note</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Title (Optional)</label>
                                <input type="text" name="title" class="form-control" placeholder="Note title...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Type</label>
                                <select name="note_type" class="form-control">
                                    <option value="general">General</option>
                                    <option value="progress">Progress</option>
                                    <option value="goal">Goal</option>
                                    <option value="action_item">Action Item</option>
                                    <option value="follow_up">Follow Up</option>
                                    <option value="private">Private</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Visibility</label>
                                <select name="visibility" class="form-control">
                                    <option value="coach_only">Coach Only</option>
                                    <option value="admin_coach">Admin & Coach</option>
                                    <option value="all">Visible to All</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Note Content <span class="text-danger">*</span></label>
                        <textarea name="note_content" class="form-control" rows="6" required 
                                  placeholder="Write your session notes here..."></textarea>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_pinned" value="1" class="form-check-input" id="isPinned">
                        <label class="form-check-label" for="isPinned">Pin this note</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Note
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Toggle pin using data-url attribute
    $('.toggle-pin-btn').click(function() {
        var btn = $(this);
        var url = btn.data('url');
        var noteId = btn.data('note-id');
        
        btn.prop('disabled', true);
        
        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    // Toggle the icon and border
                    var noteCard = $('#note-' + noteId);
                    if (response.is_pinned) {
                        noteCard.addClass('border-warning');
                        noteCard.find('.fa-thumbtack').first().remove();
                        noteCard.find('.card-header strong').before('<i class="fas fa-thumbtack text-warning mr-2"></i>');
                    } else {
                        noteCard.removeClass('border-warning');
                        noteCard.find('.card-header > div > div:first-child > .fa-thumbtack').remove();
                    }
                    
                    // Or just reload for simplicity
                    location.reload();
                } else {
                    alert(response.message || 'Failed to toggle pin.');
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                alert('An error occurred. Please try again.');
            },
            complete: function() {
                btn.prop('disabled', false);
            }
        });
    });
    
    // Delete note using data-url attribute
    $('.delete-note-btn').click(function() {
        if (!confirm('Are you sure you want to delete this note?')) {
            return;
        }
        
        var btn = $(this);
        var url = btn.data('url');
        var noteId = btn.data('note-id');
        
        btn.prop('disabled', true);
        
        $.ajax({
            url: url,
            type: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    // Remove the note card with animation
                    $('#note-' + noteId).fadeOut(300, function() {
                        $(this).remove();
                        
                        // Check if no notes left
                        if ($('.card-body .card').length === 0) {
                            location.reload();
                        }
                    });
                } else {
                    alert(response.message || 'Failed to delete note.');
                    btn.prop('disabled', false);
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                alert('An error occurred. Please try again.');
                btn.prop('disabled', false);
            }
        });
    });
});
</script>
@endpush