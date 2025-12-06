{{-- resources/views/admin/appointments/index.blade.php --}}

@extends('layouts.admin')

@section('title', 'Appointments')

@section('content-header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">Appointments</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Appointments</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<!-- Stats Cards -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $stats['today'] }}</h3>
                <p>Today's Appointments</p>
            </div>
            <div class="icon">
                <i class="fas fa-calendar-day"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $stats['this_week'] }}</h3>
                <p>This Week</p>
            </div>
            <div class="icon">
                <i class="fas fa-calendar-week"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $stats['pending'] }}</h3>
                <p>Pending</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>${{ number_format($stats['total_revenue'], 2) }}</h3>
                <p>Total Revenue</p>
            </div>
            <div class="icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
        </div>
    </div>
</div>

<!-- Main Card -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Appointments</h3>
        <div class="card-tools">
            <a href="{{ route('admin.appointments.calendar') }}" class="btn btn-info btn-sm mr-2">
                <i class="fas fa-calendar-alt"></i> Calendar View
            </a>
            <a href="{{ route('admin.appointments.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> New Appointment
            </a>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Filters -->
        <form action="{{ route('admin.appointments.index') }}" method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control form-control-sm">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            <option value="no_show" {{ request('status') == 'no_show' ? 'selected' : '' }}>No Show</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Payment</label>
                        <select name="payment_status" class="form-control form-control-sm">
                            <option value="">All Payments</option>
                            <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>Failed</option>
                            <option value="refunded" {{ request('payment_status') == 'refunded' ? 'selected' : '' }}>Refunded</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
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
                        <label>&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('admin.appointments.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Search -->
        <div class="row mb-3">
            <div class="col-md-4">
                <form action="{{ route('admin.appointments.index') }}" method="GET">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control form-control-sm" 
                               placeholder="Search by appointment #, client name, email..." 
                               value="{{ request('search') }}">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-md-8 text-right">
                <a href="{{ route('admin.appointments.export', request()->all()) }}" class="btn btn-success btn-sm">
                    <i class="fas fa-file-export"></i> Export CSV
                </a>
            </div>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th style="width: 120px;">Appointment #</th>
                        <th>Client</th>
                        <th>Coach</th>
                        <th>Date & Time</th>
                        <th>Package/Service</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Amount</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments as $appointment)
                        <tr>
                            <td>
                                <a href="{{ route('admin.appointments.show', $appointment) }}">
                                    {{ $appointment->appointment_number }}
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('admin.clients.show', $appointment->client) }}">
                                    {{ $appointment->client->full_name }}
                                </a>
                                <br>
                                <small class="text-muted">{{ $appointment->client->email }}</small>
                            </td>
                            <td>{{ $appointment->coach->name }}</td>
                            <td>
                                {{ $appointment->formatted_date }}
                                <br>
                                <small>{{ $appointment->formatted_time }}</small>
                            </td>
                            <td>
                                @if($appointment->package)
                                    <span class="badge badge-primary">{{ $appointment->package->name }}</span>
                                @elseif($appointment->service)
                                    <span class="badge badge-secondary">{{ $appointment->service->name }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{!! $appointment->type_badge !!}</td>
                            <td>{!! $appointment->status_badge !!}</td>
                            <td>{!! $appointment->payment_badge !!}</td>
                            <td>${{ number_format($appointment->final_amount, 2) }}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('admin.appointments.show', $appointment) }}" 
                                       class="btn btn-info btn-xs" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.appointments.edit', $appointment) }}" 
                                       class="btn btn-warning btn-xs" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    @if($appointment->appointment_status === 'pending')
                                        <form action="{{ route('admin.appointments.confirm', $appointment) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-xs" title="Confirm">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    @endif
                                    
                                    @if(in_array($appointment->appointment_status, ['pending', 'confirmed']))
                                        <button type="button" class="btn btn-danger btn-xs" 
                                                data-toggle="modal" 
                                                data-target="#cancelModal{{ $appointment->id }}" 
                                                title="Cancel">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @endif
                                </div>
                                
                                <!-- Cancel Modal -->
                                <div class="modal fade" id="cancelModal{{ $appointment->id }}" tabindex="-1">
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
                                                    <div class="form-group">
                                                        <label>Cancellation Reason <span class="text-danger">*</span></label>
                                                        <textarea name="cancellation_reason" class="form-control" 
                                                                  rows="3" required></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-danger">Cancel Appointment</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                <i class="fas fa-calendar-times fa-3x mb-3"></i>
                                <p>No appointments found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-end">
            {{ $appointments->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection