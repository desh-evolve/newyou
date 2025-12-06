{{-- resources/views/admin/time-slots/edit.blade.php --}}

@extends('layouts.admin')

@section('title', 'Edit Time Slot')

@section('content-header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">Edit Time Slot</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.time-slots.index') }}">Time Slots</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-clock mr-2"></i>Slot Details
                </h3>
            </div>
            <form action="{{ route('admin.time-slots.update', $timeSlot) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <!-- Coach Info (Read Only) -->
                    <div class="form-group">
                        <label>Coach</label>
                        <input type="text" class="form-control" value="{{ $timeSlot->coach->name }}" readonly>
                    </div>

                    <!-- Date (Read Only) -->
                    <div class="form-group">
                        <label>Date</label>
                        <input type="text" class="form-control" value="{{ $timeSlot->formatted_date }}" readonly>
                    </div>

                    <div class="row">
                        <!-- Start Time -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="start_time">Start Time <span class="text-danger">*</span></label>
                                <input type="time" 
                                       name="start_time" 
                                       id="start_time" 
                                       class="form-control @error('start_time') is-invalid @enderror" 
                                       value="{{ old('start_time', \Carbon\Carbon::parse($timeSlot->start_time)->format('H:i')) }}" 
                                       required>
                                @error('start_time')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- End Time -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="end_time">End Time <span class="text-danger">*</span></label>
                                <input type="time" 
                                       name="end_time" 
                                       id="end_time" 
                                       class="form-control @error('end_time') is-invalid @enderror" 
                                       value="{{ old('end_time', \Carbon\Carbon::parse($timeSlot->end_time)->format('H:i')) }}" 
                                       required>
                                @error('end_time')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Duration Display -->
                    <div class="form-group">
                        <label>Duration</label>
                        <div id="durationDisplay" class="form-control-plaintext">
                            <span class="badge badge-info">{{ $timeSlot->duration_minutes }} minutes</span>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea name="notes" 
                                  id="notes" 
                                  class="form-control @error('notes') is-invalid @enderror" 
                                  rows="3" 
                                  placeholder="Optional notes about this time slot...">{{ old('notes', $timeSlot->notes) }}</textarea>
                        @error('notes')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-2"></i>Update Slot
                    </button>
                    <a href="{{ route('admin.time-slots.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Slot Info Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle mr-2"></i>Slot Information
                </h3>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm">
                    <tr>
                        <th>Status:</th>
                        <td>
                            @switch($timeSlot->slot_status)
                                @case('available')
                                    <span class="badge badge-success">Available</span>
                                    @break
                                @case('locked')
                                    <span class="badge badge-warning">Locked</span>
                                    @break
                                @case('booked')
                                    <span class="badge badge-info">Booked</span>
                                    @break
                                @case('blocked')
                                    <span class="badge badge-danger">Blocked</span>
                                    @break
                            @endswitch
                        </td>
                    </tr>
                    <tr>
                        <th>Created:</th>
                        <td>{{ $timeSlot->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Updated:</th>
                        <td>{{ $timeSlot->updated_at->format('M d, Y H:i') }}</td>
                    </tr>
                    @if($timeSlot->locked_by)
                    <tr>
                        <th>Locked By:</th>
                        <td>{{ $timeSlot->lockedByUser->name ?? 'Unknown' }}</td>
                    </tr>
                    <tr>
                        <th>Locked At:</th>
                        <td>{{ $timeSlot->locked_at ? $timeSlot->locked_at->format('M d, Y H:i') : '-' }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        <!-- Actions Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-cogs mr-2"></i>Actions
                </h3>
            </div>
            <div class="card-body">
                @if($timeSlot->slot_status === 'available')
                    <a href="{{ route('admin.appointments.create', ['slot_id' => $timeSlot->id]) }}" 
                       class="btn btn-success btn-block mb-2">
                        <i class="fas fa-calendar-plus mr-2"></i>Book Appointment
                    </a>
                    <form action="{{ route('admin.time-slots.block', $timeSlot) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-warning btn-block mb-2">
                            <i class="fas fa-ban mr-2"></i>Block Slot
                        </button>
                    </form>
                @elseif($timeSlot->slot_status === 'locked')
                    <form action="{{ route('admin.time-slots.unlock', $timeSlot) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-info btn-block mb-2">
                            <i class="fas fa-unlock mr-2"></i>Unlock Slot
                        </button>
                    </form>
                @elseif($timeSlot->slot_status === 'blocked')
                    <form action="{{ route('admin.time-slots.unblock', $timeSlot) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success btn-block mb-2">
                            <i class="fas fa-check mr-2"></i>Unblock Slot
                        </button>
                    </form>
                @elseif($timeSlot->slot_status === 'booked' && $timeSlot->appointment)
                    <a href="{{ route('admin.appointments.show', $timeSlot->appointment) }}" 
                       class="btn btn-info btn-block mb-2">
                        <i class="fas fa-eye mr-2"></i>View Appointment
                    </a>
                @endif

                @if($timeSlot->slot_status !== 'booked')
                    <form action="{{ route('admin.time-slots.destroy', $timeSlot) }}" 
                          method="POST" 
                          onsubmit="return confirm('Are you sure you want to delete this slot?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fas fa-trash mr-2"></i>Delete Slot
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Calculate duration on time change
    function calculateDuration() {
        var startTime = $('#start_time').val();
        var endTime = $('#end_time').val();
        
        if (startTime && endTime) {
            var start = new Date('2000-01-01 ' + startTime);
            var end = new Date('2000-01-01 ' + endTime);
            var diff = (end - start) / 1000 / 60; // minutes
            
            if (diff > 0) {
                $('#durationDisplay').html('<span class="badge badge-info">' + diff + ' minutes</span>');
            } else {
                $('#durationDisplay').html('<span class="badge badge-danger">Invalid time range</span>');
            }
        }
    }
    
    $('#start_time, #end_time').on('change', calculateDuration);
});
</script>
@endpush