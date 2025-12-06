{{-- resources/views/client/appointments/create.blade.php --}}

@extends('layouts.client')

@section('title', 'Book Appointment')

@section('content')
<div class="container py-4">
    <div class="row mb-4">
        <div class="col">
            <h2>Book an Appointment</h2>
            <p class="text-muted">Select your preferred coach, date, and time slot.</p>
        </div>
    </div>

    <form action="{{ route('client.appointments.store') }}" method="POST" id="bookingForm">
        @csrf
        <div class="row">
            <div class="col-md-8">
                <!-- Step 1: Select Coach -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><span class="badge badge-primary mr-2">1</span>Select Coach</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($coaches as $coach)
                                <div class="col-md-6 mb-3">
                                    <div class="card coach-card {{ $selectedCoach == $coach->id ? 'border-primary' : '' }}" 
                                         data-coach-id="{{ $coach->id }}">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <img src="{{ asset('images/default-avatar.png') }}" 
                                                     class="rounded-circle mr-3" 
                                                     style="width: 60px; height: 60px;">
                                                <div>
                                                    <h6 class="mb-0">{{ $coach->name }}</h6>
                                                    <small class="text-muted">Life Coach</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <input type="hidden" name="coach_id" id="selectedCoach" value="{{ $selectedCoach }}">
                    </div>
                </div>

                <!-- Step 2: Select Date & Time -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><span class="badge badge-primary mr-2">2</span>Select Date & Time</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Select Date</label>
                                    <input type="date" id="appointmentDate" class="form-control" 
                                           min="{{ date('Y-m-d') }}" value="{{ $selectedDate ?? date('Y-m-d') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Available Time Slots</label>
                                    <div id="timeSlotsContainer">
                                        <p class="text-muted">Select a coach and date to see available slots</p>
                                    </div>
                                    <input type="hidden" name="time_slot_id" id="selectedSlot">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Appointment Type -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><span class="badge badge-primary mr-2">3</span>Appointment Type</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card type-card" data-type="video_call">
                                    <div class="card-body text-center">
                                        <i class="fas fa-video fa-3x text-primary mb-3"></i>
                                        <h6>Video Call</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card type-card" data-type="phone_call">
                                    <div class="card-body text-center">
                                        <i class="fas fa-phone fa-3x text-success mb-3"></i>
                                        <h6>Phone Call</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card type-card" data-type="in_person">
                                    <div class="card-body text-center">
                                        <i class="fas fa-user fa-3x text-info mb-3"></i>
                                        <h6>In Person</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="appointment_type" id="selectedType" value="video_call">
                    </div>
                </div>

                <!-- Step 4: Notes -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><span class="badge badge-primary mr-2">4</span>Additional Notes (Optional)</h5>
                    </div>
                    <div class="card-body">
                        <textarea name="client_notes" class="form-control" rows="3" 
                                  placeholder="Any specific topics you'd like to discuss?"></textarea>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Package/Service Selection -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Select Package or Service</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Package</label>
                            <select name="package_id" id="packageSelect" class="form-control">
                                <option value="">-- Select Package --</option>
                                @foreach($packages as $package)
                                    <option value="{{ $package->id }}" 
                                            data-price="{{ $package->discount_price ?? $package->price }}">
                                        {{ $package->name }} - ${{ number_format($package->discount_price ?? $package->price, 2) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="text-center my-2">- OR -</div>
                        <div class="form-group">
                            <label>Service</label>
                            <select name="service_id" id="serviceSelect" class="form-control">
                                <option value="">-- Select Service --</option>
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}" data-price="{{ $service->price }}">
                                        {{ $service->name }} - ${{ number_format($service->price, 2) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Booking Summary -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Booking Summary</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <td>Coach:</td>
                                <td class="text-right" id="summaryCoach">-</td>
                            </tr>
                            <tr>
                                <td>Date:</td>
                                <td class="text-right" id="summaryDate">-</td>
                            </tr>
                            <tr>
                                <td>Time:</td>
                                <td class="text-right" id="summaryTime">-</td>
                            </tr>
                            <tr>
                                <td>Type:</td>
                                <td class="text-right" id="summaryType">Video Call</td>
                            </tr>
                            <tr class="border-top">
                                <th>Total:</th>
                                <th class="text-right" id="summaryTotal">$0.00</th>
                            </tr>
                        </table>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-lg btn-block" id="bookBtn" disabled>
                            <i class="fas fa-calendar-check mr-2"></i>Proceed to Payment
                        </button>
                        <small class="text-muted d-block text-center mt-2">
                            You will be redirected to payment page
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
    .coach-card, .type-card {
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .coach-card:hover, .type-card:hover {
        border-color: #007bff;
        box-shadow: 0 0 10px rgba(0,123,255,0.2);
    }
    .coach-card.selected, .type-card.selected {
        border-color: #007bff;
        background-color: rgba(0,123,255,0.05);
    }
    .time-slot-btn {
        margin: 5px;
    }
    .time-slot-btn.selected {
        background-color: #007bff;
        color: white;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    var coaches = @json($coaches->pluck('name', 'id'));
    
    // Coach selection
    $('.coach-card').click(function() {
        $('.coach-card').removeClass('selected border-primary');
        $(this).addClass('selected border-primary');
        $('#selectedCoach').val($(this).data('coach-id'));
        updateSummary();
        loadTimeSlots();
    });
    
    // Type selection
    $('.type-card').click(function() {
        $('.type-card').removeClass('selected border-primary');
        $(this).addClass('selected border-primary');
        $('#selectedType').val($(this).data('type'));
        updateSummary();
    });
    
    // Initialize type
    $('.type-card[data-type="video_call"]').addClass('selected border-primary');
    
    // Date change
    $('#appointmentDate').change(function() {
        loadTimeSlots();
        updateSummary();
    });
    
    // Package/Service selection
    $('#packageSelect').change(function() {
        if ($(this).val()) {
            $('#serviceSelect').val('').prop('disabled', true);
        } else {
            $('#serviceSelect').prop('disabled', false);
        }
        updateSummary();
    });
    
    $('#serviceSelect').change(function() {
        if ($(this).val()) {
            $('#packageSelect').val('').prop('disabled', true);
        } else {
            $('#packageSelect').prop('disabled', false);
        }
        updateSummary();
    });
    
    function loadTimeSlots() {
        var coachId = $('#selectedCoach').val();
        var date = $('#appointmentDate').val();
        
        if (!coachId || !date) {
            $('#timeSlotsContainer').html('<p class="text-muted">Select a coach and date</p>');
            return;
        }
        
        $('#timeSlotsContainer').html('<p><i class="fas fa-spinner fa-spin"></i> Loading...</p>');
        
        $.get('{{ route("client.appointments.available-slots") }}', {
            coach_id: coachId,
            date: date
        }, function(response) {
            if (response.slots.length === 0) {
                $('#timeSlotsContainer').html('<p class="text-muted">No available slots for this date</p>');
                return;
            }
            
            var html = '<div class="d-flex flex-wrap">';
            response.slots.forEach(function(slot) {
                html += '<button type="button" class="btn btn-outline-primary time-slot-btn" data-slot-id="' + 
                        slot.id + '" data-time="' + slot.time + '">' + slot.time + '</button>';
            });
            html += '</div>';
            
            $('#timeSlotsContainer').html(html);
            
            // Slot click handler
            $('.time-slot-btn').click(function() {
                $('.time-slot-btn').removeClass('selected');
                $(this).addClass('selected');
                $('#selectedSlot').val($(this).data('slot-id'));
                updateSummary();
            });
        });
    }
    
    function updateSummary() {
        var coachId = $('#selectedCoach').val();
        var date = $('#appointmentDate').val();
        var slotTime = $('.time-slot-btn.selected').data('time');
        var type = $('#selectedType').val();
        
        $('#summaryCoach').text(coachId ? coaches[coachId] : '-');
        $('#summaryDate').text(date ? moment(date).format('MMM DD, YYYY') : '-');
        $('#summaryTime').text(slotTime || '-');
        $('#summaryType').text(type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()));
        
        var price = 0;
        var packageOption = $('#packageSelect option:selected');
        var serviceOption = $('#serviceSelect option:selected');
        
        if (packageOption.val()) {
            price = parseFloat(packageOption.data('price')) || 0;
        } else if (serviceOption.val()) {
            price = parseFloat(serviceOption.data('price')) || 0;
        }
        
        $('#summaryTotal').text('$' + price.toFixed(2));
        
        // Enable/disable book button
        var canBook = coachId && date && $('#selectedSlot').val() && (packageOption.val() || serviceOption.val());
        $('#bookBtn').prop('disabled', !canBook);
    }
    
    // Initial load if coach is pre-selected
    if ($('#selectedCoach').val()) {
        $('.coach-card[data-coach-id="' + $('#selectedCoach').val() + '"]').addClass('selected border-primary');
        loadTimeSlots();
    }
});
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
@endpush