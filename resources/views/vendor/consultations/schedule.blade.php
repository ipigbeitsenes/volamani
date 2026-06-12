@extends('layouts.vendor')

@section('title', 'Set Availability')

@section('content')
<div class="container py-4" style="max-width:600px">
    <h4 class="mb-1">Set Your Availability</h4>
    <p class="text-muted mb-4">Define which days and hours you're available for consultations.</p>

    <form method="POST" action="{{ route('vendor.consultations.schedule.update', $profile) }}">
        @csrf @method('PUT')

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                @php
                    $days = [1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday',6=>'Saturday',7=>'Sunday'];
                @endphp
                @foreach ($days as $num => $dayName)
                    @php $slot = $availability[$num]['slot'] ?? null; @endphp
                    <div class="border rounded p-3 mb-2">
                        <div class="d-flex align-items-center mb-2">
                            <div class="form-check me-3 mb-0">
                                <input class="form-check-input day-toggle" type="checkbox"
                                    name="availability[{{ $num }}][enabled]"
                                    id="day_{{ $num }}" value="1"
                                    data-day="{{ $num }}"
                                    @checked(old("availability.$num.enabled", $slot?->is_active))>
                                <label class="form-check-label fw-semibold" for="day_{{ $num }}">{{ $dayName }}</label>
                            </div>
                        </div>
                        <div id="times_{{ $num }}" class="{{ !old("availability.$num.enabled", $slot?->is_active) ? 'd-none' : '' }} row g-2">
                            <div class="col-6">
                                <label class="form-label small mb-1">From</label>
                                <input type="time" name="availability[{{ $num }}][start_time]"
                                    value="{{ old("availability.$num.start_time", $slot?->start_time ? substr($slot->start_time, 0, 5) : '09:00') }}"
                                    class="form-control form-control-sm">
                            </div>
                            <div class="col-6">
                                <label class="form-label small mb-1">To</label>
                                <input type="time" name="availability[{{ $num }}][end_time]"
                                    value="{{ old("availability.$num.end_time", $slot?->end_time ? substr($slot->end_time, 0, 5) : '17:00') }}"
                                    class="form-control form-control-sm">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Save Availability</button>
        <a href="{{ route('vendor.consultations.index') }}" class="btn btn-link">Cancel</a>
    </form>
</div>

<script>
    document.querySelectorAll('.day-toggle').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            var timesDiv = document.getElementById('times_' + this.dataset.day);
            timesDiv.classList.toggle('d-none', !this.checked);
        });
    });
</script>
@endsection
