{{-- resources/views/admin/clients/show.blade.php --}}

@extends('layouts.admin')

@section('title', 'Client Details - ' . $client->full_name)

@section('content-header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">Client Details</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.clients.index') }}">Clients</a></li>
            <li class="breadcrumb-item active">{{ $client->full_name }}</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-md-4">
        <!-- Profile Card -->
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <div class="text-center">
                    <img class="profile-user-img img-fluid img-circle" 
                         src="{{ $client->profile_image ? asset('storage/' . $client->profile_image) : asset('images/default-avatar.png') }}"
                         alt="Profile" style="width: 100px; height: 100px; object-fit: cover;">
                </div>
                <h3 class="profile-username text-center">{{ $client->full_name }}</h3>
                <p class="text-muted text-center">
                    @if($client->status === 'active')
                        <span class="badge badge-success">Active</span>
                    @else
                        <span class="badge badge-secondary">Inactive</span>
                    @endif
                </p>

                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Email</b> <a class="float-right">{{ $client->email }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Phone</b> <a class="float-right">{{ $client->phone ?: 'N/A' }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Date of Birth</b> 
                        <a class="float-right">{{ $client->date_of_birth ? $client->date_of_birth->format('M d, Y') : 'N/A' }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Gender</b> <a class="float-right">{{ ucfirst($client->gender ?? 'N/A') }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Timezone</b> <a class="float-right">{{ $client->timezone }}</a>
                    </li>
                </ul>

                <a href="{{ route('admin.clients.edit', $client) }}" class="btn btn-primary btn-block">
                    <i class="fas fa-edit"></i> Edit Profile
                </a>
            </div>
        </div>

        <!-- Address Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-map-marker-alt mr-2"></i>Address</h3>
            </div>
            <div class="card-body">
                @if($client->full_address)
                    <p>{{ $client->full_address }}</p>
                @else
                    <p class="text-muted">No address provided</p>
                @endif
            </div>
        </div>

        <!-- Stats Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-bar mr-2"></i>Statistics</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 text-center border-right">
                        <h4 class="mb-0">{{ $totalAppointments }}</h4>
                        <small class="text-muted">Total Appointments</small>
                    </div>
                    <div class="col-6 text-center">
                        <h4 class="mb-0">{{ $completedAppointments }}</h4>
                        <small class="text-muted">Completed</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <!-- Goals & Notes -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-bullseye mr-2"></i>Goals & Notes</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Goals</h6>
                        <p>{{ $client->goals ?: 'No goals specified' }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Health Notes</h6>
                        <p>{{ $client->health_notes ?: 'No health notes' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upcoming Appointments -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-calendar-alt mr-2"></i>Upcoming Appointments</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.appointments.create', ['client_id' => $client->user_id]) }}" 
                       class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Book Appointment
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Coach</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($upcomingAppointments as $appointment)
                            <tr>
                                <td>
                                    {{ $appointment->formatted_date }}<br>
                                    <small class="text-muted">{{ $appointment->formatted_time }}</small>
                                </td>
                                <td>{{ $appointment->coach->name }}</td>
                                <td>{!! $appointment->status_badge !!}</td>
                                <td>
                                    <a href="{{ route('admin.appointments.show', $appointment) }}" 
                                       class="btn btn-info btn-xs">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">
                                    No upcoming appointments
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Appointment History -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-history mr-2"></i>Appointment History</h3>
            </div>
            <div class="card-body p-0">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Appointment #</th>
                            <th>Date</th>
                            <th>Coach</th>
                            <th>Status</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($client->appointments->take(10) as $appointment)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.appointments.show', $appointment) }}">
                                        {{ $appointment->appointment_number }}
                                    </a>
                                </td>
                                <td>{{ $appointment->formatted_date }}</td>
                                <td>{{ $appointment->coach->name }}</td>
                                <td>{!! $appointment->status_badge !!}</td>
                                <td>${{ number_format($appointment->final_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">
                                    No appointment history
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection