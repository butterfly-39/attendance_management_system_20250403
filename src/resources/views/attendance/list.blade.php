@extends('layouts.app')

@php
use Carbon\Carbon;
@endphp

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/list.css')}}">
@endsection

@section('content')
<div class="attendance">
    <div class="attendance__content">
        <h2 class="attendance__heading">勤怠一覧</h2>
        
        {{-- 月選択ナビゲーション --}}
        <div class="attendance__month-nav">
            <a href="{{ route('attendance.list', ['date' => $currentDate->copy()->subMonth()->format('Y-m')]) }}" class="btn btn-secondary">← 前月</a>
            <span class="current-month">
                <i class="fas fa-calendar-alt"></i>
                {{ $currentDate->format('Y/m') }}
            </span>
            <a href="{{ route('attendance.list', ['date' => $currentDate->copy()->addMonth()->format('Y-m')]) }}" class="btn btn-secondary">翌月 →</a>
        </div>

        <div class="attendance__list">
            <div class="attendance__item">
                <div>日付</div>
                <div>出勤</div>
                <div>退勤</div>
                <div>休憩</div>
                <div>合計</div>
                <div>詳細</div>
            </div>
            @foreach($attendances as $attendance)
                <div class="attendance__item">
                    <div class="attendance__date-group">
                        <span class="attendance__date">{{ Carbon::parse($attendance->date)->format('m/d') }}</span>
                        <span class="attendance__day">({{ Carbon::parse($attendance->date)->isoFormat('ddd') }})</span>
                    </div>
                    <div>{{ $attendance->clock_in_time ? Carbon::parse($attendance->clock_in_time)->format('H:i') : '---' }}</div>
                    <div>{{ $attendance->clock_out_time ? Carbon::parse($attendance->clock_out_time)->format('H:i') : '---' }}</div>
                    <div>{{ $attendance->total_break_time ?? '---' }}</div>
                    <div>{{ $attendance->total_work_time ?? '---' }}</div>
                    <div class="attendance__actions">
                        <a href="{{ route('attendance.show', ['id' => $attendance->id]) }}" class="btn btn-primary">詳細</a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    flatpickr('.current-month', {
        locale: 'ja',
        dateFormat: 'Y/m',
        defaultDate: '{{ $currentDate->format("Y/m") }}',
        enableTime: false,
        onChange: function(selectedDates, dateStr) {
            const date = selectedDates[0];
            const formattedDate = date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0');
            window.location.href = '/attendance/list?date=' + formattedDate;
        }
    });
});
</script>
@endsection
