@extends('layouts.app')

@php
use Carbon\Carbon;
@endphp

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/list.css')}}">
@endsection

@section('content')
<div class="attendance">
    <div class="attendance__content">
        <h2 class="attendance__heading">{{ $currentDate->format('Y年n月j日') }}の勤怠</h2>

        {{-- 日付選択ナビゲーション --}}
        <div class="attendance__date-nav">
            <a href="{{ route('admin.attendance.list', ['date' => $currentDate->copy()->subDay()->format('Y-m-d')]) }}" class="btn btn-secondary">← 前日</a>
            <span class="current-date">
                <i class="fas fa-calendar-alt"></i>
                {{ $currentDate->format('Y/m/d') }}
            </span>
            <a href="{{ route('admin.attendance.list', ['date' => $currentDate->copy()->addDay()->format('Y-m-d')]) }}" class="btn btn-secondary">翌日 →</a>
        </div>

        <div class="attendance__list">
            <div class="attendance__item attendance__item--header">
                <div class="attendance__item-header">名前</div>
                <div class="attendance__item-header">出勤</div>
                <div class="attendance__item-header">退勤</div>
                <div class="attendance__item-header">休憩</div>
                <div class="attendance__item-header">合計</div>
                <div class="attendance__item-header">詳細</div>
            </div>
            @foreach($attendances as $attendance)
                <div class="attendance__item">
                    <div class="attendance__user-name">{{ $attendance->user->name }}</div>
                    <div class="attendance__time">{{ $attendance->clock_in_time ? Carbon::parse($attendance->clock_in_time)->format('H:i') : '---' }}</div>
                    <div class="attendance__time">{{ $attendance->clock_out_time ? Carbon::parse($attendance->clock_out_time)->format('H:i') : '---' }}</div>
                    <div class="attendance__time">{{ $attendance->total_break_time ?? '---' }}</div>
                    <div class="attendance__time">{{ $attendance->total_work_time ?? '---' }}</div>
                    <div class="attendance__actions">
                        <a href="{{ route('admin.attendance.show', ['id' => $attendance->id]) }}" class="btn btn-primary">詳細</a>
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
    flatpickr('.current-date', {
        locale: 'ja',
        dateFormat: 'Y/m/d',
        defaultDate: '{{ $currentDate->format("Y/m/d") }}',
        enableTime: false,
        onChange: function(selectedDates, dateStr) {
            const date = selectedDates[0];
            const formattedDate = date.getFullYear() + '-' +
                String(date.getMonth() + 1).padStart(2, '0') + '-' +
                String(date.getDate()).padStart(2, '0');
            window.location.href = '{{ route("admin.attendance.list") }}?date=' + formattedDate;
        }
    });
});
</script>
@endsection

