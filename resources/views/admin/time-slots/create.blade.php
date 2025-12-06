{{-- resources/views/admin/time-slots/create.blade.php --}}

@extends('layouts.admin')

@section('title', 'Generate Time Slots')

@section('content-header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">Generate Time Slots</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.time-slots.index') }}">Time Slots</a></li>
            <li class="breadcrumb-item active">Generate</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Slot Generation Settings</h3>
            </div>
            <form action="{{ route('admin.time-slots.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <!-- Coach Selection -->
                    <div class="form-group">
                        <label>Select Coach <span class="text-danger">*</span></label>
                        <select name="coach_id" class="form-control" required>
                            <option value="">-- Select Coach --</option>
                            @foreach($coaches as $coach)
                                <option value="{{ $coach->id }}" {{ old('coach_id') == $coach->id ? 'selected' : '' }}>
                                    {{ $coach->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('coach_id')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="row">
                        <!-- Date Range -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Start Date <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" class="form-control" 
                                       min="{{ date('Y-m-d') }}" value="{{ old('start_date', date('Y-m-d')) }}" required>
                                @error('start_date')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>End Date <span class="text-danger">*</span></label>
                                <input type="date" name="end_date" class="form-control" 
                                       min="{{ date('Y-m-d') }}" value="{{ old('end_date', date('Y-m-d', strtotime('+7 days'))) }}" required>
                                @error('end_date')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Time Range -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Start Time <span class="text-danger">*</span></label>
                                <input type="time" name="start_time" class="form-control" 
                                       value="{{ old('start_time', '09:00') }}" required>
                                @error('start_time')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>End Time <span class="text-danger">*</span></label>
                                <input type="time" name="end_time" class="form-control" 
                                       value="{{ old('end_time', '17:00') }}" required>
                                @error('end_time')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Slot Duration (minutes) <span class="text-danger">*</span></label>
                                <select name="duration" class="form-control" required>
                                    <option value="30" {{ old('duration') == 30 ? 'selected' : '' }}>30 minutes</option>
                                    <option value="45" {{ old('duration') == 45 ? 'selected' : '' }}>45 minutes</option>
                                    <option value="60" {{ old('duration', 60) == 60 ? 'selected' : '' }}>60 minutes</option>
                                    <option value="90" {{ old('duration') == 90 ? 'selected' : '' }}>90 minutes</option>
                                    <option value="120" {{ old('duration') == 120 ? 'selected' : '' }}>120 minutes</option>
                                </select>
                                @error('duration')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Days of Week -->
                    <div class="form-group">
                        <label>Days of Week <span class="text-danger">*</span></label>
                        <div class="row">
                            @php
                                $days = [
                                    0 => 'Sunday',
                                    1 => 'Monday',
                                    2 => 'Tuesday',
                                    3 => 'Wednesday',
                                    4 => 'Thursday',
                                    5 => 'Friday',
                                    6 => 'Saturday'
                                ];
                                $oldDays = old('days', [1, 2, 3, 4, 5]); // Default: Mon-Fri
                            @endphp
                            @foreach($days as $key => $day)
                                <div class="col-md-3 col-6">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input day-checkbox" 
                                               id="day{{ $key }}" name="days[]" value="{{ $key }}"
                                               {{ in_array($key, $oldDays) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="day{{ $key }}">{{ $day }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('days')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-cog mr-2"></i> Generate Time Slots
                    </button>
                    <a href="{{ route('admin.time-slots.index') }}" class="btn btn-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Preview Card -->
        <div class="card">
            <div class="card-header bg-info">
                <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Preview</h3>
            </div>
            <div class="card-body">
                <div id="slotPreview">
                    <p class="text-muted text-center">Configure settings to see preview</p>
                </div>
            </div>
        </div>

        <!-- Help Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-question-circle mr-2"></i>Help</h3>
            </div>
            <div class="card-body">
                <p><strong>How it works:</strong></p>
                <ul class="mb-0">
                    <li>Select the coach for whom slots will be created</li>
                    <li>Choose date range (slots will be created for each day)</li>
                    <li>Set working hours (start and end time)</li>
                    <li>Choose slot duration</li>
                    <li>Select which days of the week to include</li>
                    <li>Existing slots will not be duplicated</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    function updatePreview() {
        var startDate = $('input[name="start_date"]').val();
        var endDate = $('input[name="end_date"]').val();
        var startTime = $('input[name="start_time"]').val();
        var endTime = $('input[name="end_time"]').val();
        var duration = parseInt($('select[name="duration"]').val());
        var days = [];
        
        $('.day-checkbox:checked').each(function() {
            days.push($(this).val());
        });
        
        if (!startDate || !endDate || !startTime || !endTime || !duration || days.length === 0) {
            $('#slotPreview').html('<p class="text-muted text-center">Configure settings to see preview</p>');
            return;
        }
        
        // Calculate slots per day
        var start = moment(startTime, 'HH:mm');
        var end = moment(endTime, 'HH:mm');
        var slotsPerDay = Math.floor(end.diff(start, 'minutes') / duration);
        
        // Calculate days
        var dayStart = moment(startDate);
        var dayEnd = moment(endDate);
        var totalDays = 0;
        
        while (dayStart.isSameOrBefore(dayEnd)) {
            if (days.includes(dayStart.day().toString())) {
                totalDays++;
            }
            dayStart.add(1, 'days');
        }
        
        var totalSlots = slotsPerDay * totalDays;
        
        var html = `
            <table class="table table-sm">
                <tr>
                    <td>Slots per day:</td>
                    <td class="text-right"><strong>${slotsPerDay}</strong></td>
                </tr>
                <tr>
                    <td>Number of days:</td>
                    <td class="text-right"><strong>${totalDays}</strong></td>
                </tr>
                <tr class="table-primary">
                    <td>Total slots:</td>
                    <td class="text-right"><strong>${totalSlots}</strong></td>
                </tr>
            </table>
        `;
        
        $('#slotPreview').html(html);
    }
    
    // Update preview on change
    $('input, select').on('change keyup', updatePreview);
    $('.day-checkbox').on('change', updatePreview);
    
    // Initial preview
    updatePreview();
});
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script>
$(document).ready(function() {
    function updatePreview() {
        var startDate = $('input[name="start_date"]').val();
        var endDate = $('input[name="end_date"]').val();
        var startTime = $('input[name="start_time"]').val();
        var endTime = $('input[name="end_time"]').val();
        var duration = parseInt($('select[name="duration"]').val());
        var days = [];
        
        $('.day-checkbox:checked').each(function() {
            days.push($(this).val());
        });
        
        if (!startDate || !endDate || !startTime || !endTime || !duration || days.length === 0) {
            $('#slotPreview').html('<p class="text-muted text-center">Configure settings to see preview</p>');
            return;
        }
        
        var start = moment(startTime, 'HH:mm');
        var end = moment(endTime, 'HH:mm');
        var slotsPerDay = Math.floor(end.diff(start, 'minutes') / duration);
        
        var dayStart = moment(startDate);
        var dayEnd = moment(endDate);
        var totalDays = 0;
        
        while (dayStart.isSameOrBefore(dayEnd)) {
            if (days.includes(dayStart.day().toString())) {
                totalDays++;
            }
            dayStart.add(1, 'days');
        }
        
        var totalSlots = slotsPerDay * totalDays;
        
        var html = '<table class="table table-sm">' +
            '<tr><td>Slots per day:</td><td class="text-right"><strong>' + slotsPerDay + '</strong></td></tr>' +
            '<tr><td>Number of days:</td><td class="text-right"><strong>' + totalDays + '</strong></td></tr>' +
            '<tr class="table-primary"><td>Total slots:</td><td class="text-right"><strong>' + totalSlots + '</strong></td></tr>' +
            '</table>';
        
        $('#slotPreview').html(html);
    }
    
    $('input, select').on('change keyup', updatePreview);
    $('.day-checkbox').on('change', updatePreview);
    updatePreview();
});
</script>
@endpush