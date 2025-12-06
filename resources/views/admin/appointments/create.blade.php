{{-- resources/views/admin/appointments/create.blade.php --}}

@extends('layouts.admin')

@section('title', 'Create Appointment')

@section('content-header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">Create New Appointment</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.appointments.index') }}">Appointments</a></li>
            <li class="breadcrumb-item active">Create</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<form action="{{ route('admin.appointments.store') }}" method="POST" id="appointmentForm">
    @csrf
    <div class="row">
        <div class="col-md-8">
            <!-- Main Form Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Appointment Details</h3>
                </div>
                <div class="card-body">
                    <!-- Client Selection -->
                    <div class="form-group">
                        <label>Select Client <span class="text-danger">*</span></label>
                        <select name="client_user_id" class="form-control select2" required>
                            <option value="">-- Select Client --</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->user_id }}" 
                                    {{ old('client_user_id') == $client->user_id ? 'selected' : '' }}>
                                    {{ $client->full_name }} ({{ $client->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('client_user_id')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Coach Selection -->
                    <div class="form-group">
                        <label>Select Coach <span class="text-danger">*</span></label>
                        <select name="coach_id" id="coachSelect" class="form-control" required>
                            <option value="">-- Select Coach --</option>
                            @foreach($coaches as $coach)
                                <option value="{{ $coach->id }}" 
                                    {{ old('coach_id', $selectedSlot->coach_id ?? '') == $coach->id ? 'selected' : '' }}>
                                    {{ $coach->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('coach_id')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="row">
                        <!-- Date Selection -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Select Date <span class="text-danger">*</span></label>
                                <input type="date" name="appointment_date" id="appointmentDate" 
                                       class="form-control" min="{{ date('Y-m-d') }}" 
                                       value="{{ old('appointment_date', $selectedSlot->slot_date ?? request('date')) }}" required>
                                @error('appointment_date')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Time Slot Selection -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Select Time Slot <span class="text-danger">*</span></label>
                                <select name="time_slot_id" id="timeSlotSelect" class="form-control" required>
                                    <option value="">-- Select time slot --</option>
                                    @if($selectedSlot)
                                        <option value="{{ $selectedSlot->id }}" selected>
                                            {{ $selectedSlot->formatted_time }}
                                        </option>
                                    @endif
                                </select>
                                <small class="text-muted" id="slotLoading" style="display: none;">
                                    <i class="fas fa-spinner fa-spin"></i> Loading slots...
                                </small>
                                @error('time_slot_id')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Appointment Type -->
                    <div class="form-group">
                        <label>Appointment Type <span class="text-danger">*</span></label>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="typeVideo" name="appointment_type" value="video_call" 
                                           class="custom-control-input" {{ old('appointment_type', 'video_call') == 'video_call' ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="typeVideo">
                                        <i class="fas fa-video mr-1"></i> Video Call
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="typePhone" name="appointment_type" value="phone_call" 
                                           class="custom-control-input" {{ old('appointment_type') == 'phone_call' ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="typePhone">
                                        <i class="fas fa-phone mr-1"></i> Phone Call
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="typeInPerson" name="appointment_type" value="in_person" 
                                           class="custom-control-input" {{ old('appointment_type') == 'in_person' ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="typeInPerson">
                                        <i class="fas fa-user mr-1"></i> In Person
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Meeting Link -->
                    <div class="form-group" id="meetingLinkGroup">
                        <label>Meeting Link</label>
                        <input type="url" name="meeting_link" class="form-control" 
                               placeholder="https://zoom.us/j/..." value="{{ old('meeting_link') }}">
                        <small class="text-muted">Provide a video conferencing link (Zoom, Google Meet, etc.)</small>
                    </div>

                    <!-- Meeting Location -->
                    <div class="form-group" id="meetingLocationGroup" style="display: none;">
                        <label>Meeting Location</label>
                        <input type="text" name="meeting_location" class="form-control" 
                               placeholder="Enter meeting address..." value="{{ old('meeting_location') }}">
                    </div>

                    <!-- Notes -->
                    <div class="form-group">
                        <label>Client Notes</label>
                        <textarea name="client_notes" class="form-control" rows="3" 
                                  placeholder="Any notes from the client...">{{ old('client_notes') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label>Admin Notes</label>
                        <textarea name="admin_notes" class="form-control" rows="3" 
                                  placeholder="Internal notes (not visible to client)...">{{ old('admin_notes') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Package/Service Selection -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Package / Service</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Select Package</label>
                        <select name="package_id" id="packageSelect" class="form-control">
                            <option value="">-- No Package --</option>
                            @foreach($packages as $package)
                                <option value="{{ $package->id }}" 
                                        data-price="{{ $package->price }}" 
                                        data-discount="{{ $package->discount_price ?? $package->price }}"
                                        {{ old('package_id') == $package->id ? 'selected' : '' }}>
                                    {{ $package->name }} - ${{ number_format($package->discount_price ?? $package->price, 2) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="text-center text-muted my-3">- OR -</div>

                    <div class="form-group">
                        <label>Select Service</label>
                        <select name="service_id" id="serviceSelect" class="form-control">
                            <option value="">-- No Service --</option>
                            @foreach($services as $service)
                                <option value="{{ $service->id }}" 
                                        data-price="{{ $service->price }}"
                                        {{ old('service_id') == $service->id ? 'selected' : '' }}>
                                    {{ $service->name }} - ${{ number_format($service->price, 2) }} ({{ $service->duration }} min)
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Pricing Summary -->
            <div class="card">
                <div class="card-header bg-primary">
                    <h3 class="card-title">Pricing Summary</h3>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td>Subtotal:</td>
                            <td class="text-right" id="subtotal">$0.00</td>
                        </tr>
                        <tr>
                            <td>Discount:</td>
                            <td class="text-right text-success" id="discount">-$0.00</td>
                        </tr>
                        <tr class="border-top">
                            <th>Total:</th>
                            <th class="text-right" id="total">$0.00</th>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary btn-lg btn-block">
                        <i class="fas fa-calendar-plus mr-2"></i> Create Appointment
                    </button>
                    <a href="{{ route('admin.appointments.index') }}" class="btn btn-secondary btn-block">
                        Cancel
                    </a>
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
        theme: 'bootstrap4',
        placeholder: 'Search...',
        allowClear: true
    });

    // Load available slots when coach or date changes
    $('#coachSelect, #appointmentDate').change(function() {
        var coachId = $('#coachSelect').val();
        var date = $('#appointmentDate').val();
        
        if (coachId && date) {
            loadTimeSlots(coachId, date);
        } else {
            $('#timeSlotSelect').html('<option value="">-- Select time slot --</option>');
        }
    });

    function loadTimeSlots(coachId, date) {
        $('#slotLoading').show();
        $('#timeSlotSelect').prop('disabled', true);
        
        $.get('{{ route("admin.appointments.available-slots") }}', {
            coach_id: coachId,
            date: date
        }, function(slots) {
            var html = '<option value="">-- Select time slot --</option>';
            
            if (slots.length === 0) {
                html = '<option value="">No available slots</option>';
            } else {
                slots.forEach(function(slot) {
                    html += '<option value="' + slot.id + '">' + slot.time + ' (' + slot.duration + ' min)</option>';
                });
            }
            
            $('#timeSlotSelect').html(html).prop('disabled', false);
            $('#slotLoading').hide();
        }).fail(function() {
            $('#timeSlotSelect').html('<option value="">Error loading slots</option>').prop('disabled', false);
            $('#slotLoading').hide();
        });
    }

    // Toggle meeting link/location based on type
    $('input[name="appointment_type"]').change(function() {
        var type = $(this).val();
        
        if (type === 'in_person') {
            $('#meetingLinkGroup').hide();
            $('#meetingLocationGroup').show();
        } else if (type === 'video_call') {
            $('#meetingLinkGroup').show();
            $('#meetingLocationGroup').hide();
        } else {
            $('#meetingLinkGroup').hide();
            $('#meetingLocationGroup').hide();
        }
    });

    // Package/Service selection (mutually exclusive)
    $('#packageSelect').change(function() {
        if ($(this).val()) {
            $('#serviceSelect').val('').prop('disabled', true);
        } else {
            $('#serviceSelect').prop('disabled', false);
        }
        updatePricing();
    });

    $('#serviceSelect').change(function() {
        if ($(this).val()) {
            $('#packageSelect').val('').prop('disabled', true);
        } else {
            $('#packageSelect').prop('disabled', false);
        }
        updatePricing();
    });

    function updatePricing() {
        var subtotal = 0;
        var discount = 0;
        var total = 0;

        var packageOption = $('#packageSelect option:selected');
        var serviceOption = $('#serviceSelect option:selected');

        if (packageOption.val()) {
            subtotal = parseFloat(packageOption.data('price')) || 0;
            var discountPrice = parseFloat(packageOption.data('discount')) || subtotal;
            discount = subtotal - discountPrice;
            total = discountPrice;
        } else if (serviceOption.val()) {
            subtotal = parseFloat(serviceOption.data('price')) || 0;
            total = subtotal;
        }

        $('#subtotal').text('$' + subtotal.toFixed(2));
        $('#discount').text('-$' + discount.toFixed(2));
        $('#total').text('$' + total.toFixed(2));
    }

    // Initial trigger
    $('input[name="appointment_type"]:checked').trigger('change');
    updatePricing();
});
</script>
@endpush