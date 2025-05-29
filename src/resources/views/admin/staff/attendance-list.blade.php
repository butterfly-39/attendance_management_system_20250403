@extends('layouts.app')

@php
use Carbon\Carbon;
@endphp

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff/attendance-list.css')}}">
@endsection

@section('content')
<div class="attendance">
    <div class="attendance__content">
        <h2 class="attendance__heading">{{ $staff->name }}さんの勤怠</h2>

        {{-- 月選択ナビゲーション --}}
        <div class="attendance__month-nav">
            <a href="{{ route('admin.staff.attendance-list', ['id' => $staff->id]) }}?date={{ $currentDate->copy()->subMonth()->format('Y-m') }}" class="btn btn-secondary">← 前月</a>
            <span class="current-month">
                <i class="fas fa-calendar-alt"></i>
                {{ $currentDate->format('Y/m') }}
            </span>
            <a href="{{ route('admin.staff.attendance-list', ['id' => $staff->id]) }}?date={{ $currentDate->copy()->addMonth()->format('Y-m') }}" class="btn btn-secondary">翌月 →</a>
        </div>

        <div class="attendance__list">
            <div class="attendance__item">
                <div class="attendance__item-header">日付</div>
                <div class="attendance__item-header">出勤</div>
                <div class="attendance__item-header">退勤</div>
                <div class="attendance__item-header">休憩</div>
                <div class="attendance__item-header">合計</div>
                <div class="attendance__item-header">詳細</div>
            </div>
            @foreach($attendances as $attendance)
                <div class="attendance__item">
                    <div class="attendance__date-group">
                        <span class="attendance__date">{{ Carbon::parse($attendance->date)->format('m/d') }}</span>
                        <span class="attendance__day">({{ Carbon::parse($attendance->date)->isoFormat('ddd') }})</span>
                    </div>
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

        <div class="attendance__export mt-4 text-right">
            <a href="{{ route('admin.staff.attendance.export', ['id' => $staff->id, 'date' => $currentDate->format('Y-m')]) }}" class="btn btn-dark">
                <i class="fas fa-file-download"></i>CSV出力
            </a>
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
            window.location.href = '{{ route("admin.staff.attendance-list", ["id" => $staff->id]) }}' + '?date=' + formattedDate;
        }
    });
});
</script>
@endsection
