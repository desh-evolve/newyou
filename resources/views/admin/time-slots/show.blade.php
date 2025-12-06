{{-- resources/views/admin/time-slots/show.blade.php --}}

@extends('layouts.admin')

@section('title', 'Time Slot Details')

@section('content-header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">Time Slot Details</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.time-slots.index') }}">Time Slots</a></li>
            <li class="breadcrumb-item active">Details</li>
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
                    <i class="fas fa-clock mr-2"></i>Slot Information
                </h3>
                <div class="card-tools">
                    @switch($timeSlot->slot_status)
                        @case('available')
                            <span class="badge badge-success badge-lg">Available</span>
                            @break
                        @case('locked')
                            <span class="badge badge-warning badge-lg">Locked</span>
                            @break
                        @case('booked')
                            <span class="badge badge-info badge-lg">Booked</span>
                            @break
                        @case('blocked')
                            <span class="badge badge-danger badge-lg">Blocked</span>
                            @break
                    @endswitch
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th style="width: 120px;">Coach:</th>
                                <td>{{ $timeSlot->coach->name }}</td>
                            </tr>
                            <tr>
                                <th>Date:</th>
                                <td>
                                    <i class="fas fa-calendar text-primary mr-2"></i>
                                    {{ $timeSlot->formatted_date }}
                                </td>
                            </tr>
                            <tr>
                                <th>Time:</th>
                                <td>
                                    <i class="fas fa-clock text-primary mr-2"></i>
                                    {{ $timeSlot->formatted_time }}
                                </td>
                            </tr>
                            <tr>
                                <th>Duration:</th>
                                <td>{{ $timeSlot->duration_minutes }} minutes</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th style="width: 120px;">Created:</th>
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

                @if($timeSlot->notes)
                    <hr>
                    <h6>Notes</h6>
                    <p>{{ $timeSlot->notes }}</p>
                @endif
            </div>
            <div class="card-footer">
                @if($timeSlot->slot_status !== 'booked')
                    <a href="{{ route('admin.time-slots.edit', $timeSlot) }}" class="btn btn-warning">
                        <i class="fas fa-edit mr-2"></i>Edit
                    </a>
                @endif
                <a href="{{ route('admin.time-slots.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-2"></i>Back
                </a>
            </div>
        </div>

        <!-- Appointment Info (if booked) -->
        @if($timeSlot->slot_status === 'booked' && $timeSlot->appointment)
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-check mr-2"></i>Linked Appointment
                    </h3>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th style="width: 150px;">Appointment #:</th>
                            <td>
                                <a href="{{ route('admin.appointments.show', $timeSlot->appointment) }}">
                                    {{ $timeSlot->appointment->appointment_number }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>Client:</th>
                            <td>{{ $timeSlot->appointment->client->full_name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>{!! $timeSlot->appointment->status_badge !!}</td>
                        </tr>
                        <tr>
                            <th>Payment:</th>
                            <td>{!! $timeSlot->appointment->payment_badge !!}</td>
                        </tr>
                    </table>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.appointments.show', $timeSlot->appointment) }}" class="btn btn-info">
                        <i class="fas fa-eye mr-2"></i>View Appointment
                    </a>
                </div>
            </div>
        @endif
    </div>

    <div class="col-md-4">
        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-bolt mr-2"></i>Quick Actions
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
                    <form action="{{ route('admin.time-slots.destroy', $timeSlot) }}" 
                          method="POST" 
                          onsubmit="return confirm('Delete this slot?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fas fa-trash mr-2"></i>Delete Slot
                        </button>
                    </form>
                @elseif($timeSlot->slot_status === 'locked')
                    <form action="{{ route('admin.time-slots.unlock', $timeSlot) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-info btn-block">
                            <i class="fas fa-unlock mr-2"></i>Unlock Slot
                        </button>
                    </form>
                @elseif($timeSlot->slot_status === 'blocked')
                    <form action="{{ route('admin.time-slots.unblock', $timeSlot) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success btn-block">
                            <i class="fas fa-check mr-2"></i>Unblock Slot
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection