{{-- resources/views/admin/time-slots/index.blade.php --}}

@extends('layouts.admin')

@section('title', 'Time Slots')

@section('content-header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">Time Slots Management</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Time Slots</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Time Slots</h3>
        <div class="card-tools">
            <a href="{{ route('admin.time-slots.calendar') }}" class="btn btn-info btn-sm mr-2">
                <i class="fas fa-calendar-alt"></i> Calendar View
            </a>
            <a href="{{ route('admin.time-slots.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Generate Slots
            </a>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Filters -->
        <form action="{{ route('admin.time-slots.index') }}" method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Coach</label>
                        <select name="coach_id" class="form-control form-control-sm">
                            <option value="">All Coaches</option>
                            @foreach($coaches as $coach)
                                <option value="{{ $coach->id }}" {{ request('coach_id') == $coach->id ? 'selected' : '' }}>
                                    {{ $coach->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control form-control-sm">
                            <option value="">All Status</option>
                            <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Available</option>
                            <option value="locked" {{ request('status') == 'locked' ? 'selected' : '' }}>Locked</option>
                            <option value="booked" {{ request('status') == 'booked' ? 'selected' : '' }}>Booked</option>
                            <option value="blocked" {{ request('status') == 'blocked' ? 'selected' : '' }}>Blocked</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>From Date</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>To Date</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="d-block">&nbsp;</label>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" name="show_past" id="showPast" 
                                   value="1" {{ request('show_past') ? 'checked' : '' }}>
                            <label class="custom-control-label" for="showPast">Show Past</label>
                        </div>
                    </div>
                </div>
                <div class="col-md-1">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary btn-sm btn-block">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <!-- Bulk Actions -->
        <div class="mb-3">
            <form id="bulkDeleteForm" action="{{ route('admin.time-slots.bulk-delete') }}" method="POST" class="d-inline">
                @csrf
                <input type="hidden" name="slot_ids" id="bulkSlotIds">
                <button type="submit" class="btn btn-danger btn-sm" id="bulkDeleteBtn" disabled>
                    <i class="fas fa-trash"></i> Delete Selected
                </button>
            </form>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th style="width: 40px;">
                            <input type="checkbox" id="selectAll">
                        </th>
                        <th>Coach</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th>Appointment</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($timeSlots as $slot)
                        <tr class="{{ $slot->is_past ? 'table-secondary' : '' }}">
                            <td>
                                @if($slot->slot_status === 'available' && !$slot->is_past)
                                    <input type="checkbox" class="slot-checkbox" value="{{ $slot->id }}">
                                @endif
                            </td>
                            <td>{{ $slot->coach->name }}</td>
                            <td>{{ $slot->formatted_date }}</td>
                            <td>{{ $slot->formatted_time }}</td>
                            <td>{{ $slot->duration_minutes }} min</td>
                            <td>
                                @switch($slot->slot_status)
                                    @case('available')
                                        <span class="badge badge-success">Available</span>
                                        @break
                                    @case('locked')
                                        <span class="badge badge-warning">Locked</span>
                                        @if($slot->lockedByUser)
                                            <br><small class="text-muted">by {{ $slot->lockedByUser->name }}</small>
                                        @endif
                                        @break
                                    @case('booked')
                                        <span class="badge badge-info">Booked</span>
                                        @break
                                    @case('blocked')
                                        <span class="badge badge-danger">Blocked</span>
                                        @break
                                @endswitch
                            </td>
                            <td>
                                @if($slot->appointment)
                                    <a href="{{ route('admin.appointments.show', $slot->appointment) }}">
                                        {{ $slot->appointment->appointment_number }}
                                    </a>
                                    <br>
                                    <small class="text-muted">{{ $slot->appointment->client->full_name }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    @if($slot->slot_status === 'available' && !$slot->is_past)
                                        <a href="{{ route('admin.appointments.create', ['slot_id' => $slot->id]) }}" 
                                           class="btn btn-success btn-xs" title="Book">
                                            <i class="fas fa-calendar-plus"></i>
                                        </a>
                                        <form action="{{ route('admin.time-slots.block', $slot) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-warning btn-xs" title="Block">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.time-slots.destroy', $slot) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Are you sure?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-xs" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @elseif($slot->slot_status === 'locked')
                                        <form action="{{ route('admin.time-slots.unlock', $slot) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-info btn-xs" title="Unlock">
                                                <i class="fas fa-unlock"></i>
                                            </button>
                                        </form>
                                    @elseif($slot->slot_status === 'blocked')
                                        <form action="{{ route('admin.time-slots.unblock', $slot) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-xs" title="Unblock">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-clock fa-3x mb-3"></i>
                                <p>No time slots found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-end">
            {{ $timeSlots->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Select all checkbox
    $('#selectAll').change(function() {
        $('.slot-checkbox').prop('checked', $(this).prop('checked'));
        updateBulkDeleteButton();
    });
    
    // Individual checkbox
    $('.slot-checkbox').change(function() {
        updateBulkDeleteButton();
    });
    
    function updateBulkDeleteButton() {
        var selected = $('.slot-checkbox:checked').length;
        $('#bulkDeleteBtn').prop('disabled', selected === 0);
        
        var ids = [];
        $('.slot-checkbox:checked').each(function() {
            ids.push($(this).val());
        });
        $('#bulkSlotIds').val(JSON.stringify(ids));
    }
    
    // Bulk delete form
    $('#bulkDeleteForm').submit(function(e) {
        if (!confirm('Are you sure you want to delete ' + $('.slot-checkbox:checked').length + ' slots?')) {
            e.preventDefault();
        }
    });
});
</script>
@endpush