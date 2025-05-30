@extends('layouts.app')

@php
use Carbon\Carbon;
@endphp

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/show.css')}}">
@endsection

@section('content')
<div class="attendance">
    <div class="attendance__content">
        <h2 class="attendance__heading">勤怠詳細</h2>

        <form action="{{ route('attendance.update', ['id' => $attendance->id]) }}" method="POST" class="{{ $pendingRequest || $attendance->status !== '退勤済' ? 'disabled-form' : '' }}">
            @csrf
            @method('PUT')
            <div class="attendance__detail">
                <div class="attendance__row">
                    <div class="attendance__label">名前</div>
                    <div class="attendance__value">{{ $attendance->user->name }}</div>
                </div>
                <div class="attendance__row">
                    <div class="attendance__label">日付</div>
                    <div class="attendance__value">
                        <span class="attendance__year">{{ Carbon::parse($attendance->date)->format('Y年') }}</span>
                        <span class="attendance__month-day">{{ Carbon::parse($attendance->date)->format('n月j日') }}</span>
                    </div>
                </div>
                <div class="attendance__row">
                    <div class="attendance__label">出勤・退勤</div>
                    <div class="attendance__value">
                        @if($pendingRequest)
                            <span class="attendance__time--in-pending">{{ Carbon::parse($stampCorrection->clock_in_time)->format('H:i') }}</span>
                            <span class="attendance__separator">〜</span>
                            <span class="attendance__time--out-pending">{{ $stampCorrection->clock_out_time ? Carbon::parse($stampCorrection->clock_out_time)->format('H:i') : '' }}</span>
                        @else
                            <input type="time" class="attendance__time attendance__time--in" value="{{ old('clock_in_time', Carbon::parse($attendance->clock_in_time)->format('H:i')) }}" name="clock_in_time">
                            <span class="attendance__separator">〜</span>
                            <input type="time" class="attendance__time attendance__time--out" value="{{ old('clock_out_time', $attendance->clock_out_time ? Carbon::parse($attendance->clock_out_time)->format('H:i') : '') }}" name="clock_out_time">
                        @endif
                    </div>
                </div>
                @if($pendingRequest)
                    @foreach($breakCorrections as $index => $break)
                        <div class="attendance__row">
                            <div class="attendance__label">{{ $index === 0 ? '休憩' : '休憩'.($index + 1) }}</div>
                            <div class="attendance__value">
                                <span class="attendance__time--in-pending">{{ Carbon::parse($break->break_start_time)->format('H:i') }}</span>
                                <span class="attendance__separator">〜</span>
                                <span class="attendance__time--out-pending">{{ $break->break_end_time ? Carbon::parse($break->break_end_time)->format('H:i') : '' }}</span>
                            </div>
                        </div>
                    @endforeach
                @else
                    @foreach($attendance->breakTimes as $index => $break)
                        <div class="attendance__row">
                            <div class="attendance__label">{{ $index === 0 ? '休憩' : '休憩'.($index + 1) }}</div>
                            <div class="attendance__value">
                                <input type="time" class="attendance__time attendance__time--in" value="{{ old('break_start_time.'.$index, Carbon::parse($break->break_start_time)->format('H:i')) }}" name="break_start_time[]">
                                <span class="attendance__separator">〜</span>
                                <input type="time" class="attendance__time attendance__time--out" value="{{ old('break_end_time.'.$index, $break->break_end_time ? Carbon::parse($break->break_end_time)->format('H:i') : '') }}" name="break_end_time[]">
                            </div>
                        </div>
                    @endforeach
                    <div class="attendance__row">
                        <div class="attendance__label">{{ count($attendance->breakTimes) === 0 ? '休憩' : '休憩'.(count($attendance->breakTimes) + 1) }}</div>
                        <div class="attendance__value">
                            <input type="time" class="attendance__time attendance__time--in" name="break_start_time[]">
                            <span class="attendance__separator">〜</span>
                            <input type="time" class="attendance__time attendance__time--out" name="break_end_time[]">
                        </div>
                    </div>
                @endif
                <div class="attendance__row">
                    <div class="attendance__label">備考</div>
                    <div class="attendance__value">
                        @if($pendingRequest)
                            {{ $stampCorrection->note }}
                        @else
                            <textarea class="attendance__textarea" name="note">{{ old('note', $attendance->note) }}</textarea>
                        @endif
                    </div>
                </div>
            </div>
            @if(!$pendingRequest && $attendance->status === '退勤済')
                <div class="attendance__button-container">
                    <button type="submit" class="attendance__button">修正</button>
                </div>
            @endif
        </form>

        @if($pendingRequest)
            <div class="pending-message">
                *承認待ちのため修正はできません。
            </div>
        @elseif($attendance->status !== '退勤済')
            <div class="pending-message">
                *退勤後に修正が可能になります。
            </div>
        @endif
    </div>
</div>
@endsection

