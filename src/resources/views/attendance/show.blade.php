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
                    <input type="time" class="attendance__time attendance__time--in" value="{{ Carbon::parse($attendance->clock_in_time)->format('H:i') }}" name="clock_in_time">
                    <span class="attendance__separator">〜</span>
                    <input type="time" class="attendance__time attendance__time--out" value="{{ Carbon::parse($attendance->clock_out_time)->format('H:i') }}" name="clock_out_time">
                </div>
            </div>
            <div class="attendance__row break-time">
                <div class="attendance__label">休憩</div>
                <div class="attendance__value">
                    @foreach($attendance->breakTimes as $break)
                    <div class="break-time-row">
                        <input type="time" class="attendance__time attendance__time--in" value="{{ Carbon::parse($break->break_start_time)->format('H:i') }}" name="break_start_time[]">
                        <span class="attendance__separator">〜</span>
                        <input type="time" class="attendance__time attendance__time--out" value="{{ Carbon::parse($break->break_end_time)->format('H:i') }}" name="break_end_time[]">
                    </div>
                    @endforeach
                    <div class="break-time-row">
                        <input type="time" class="attendance__time attendance__time--in" name="break_start_time[]">
                        <span class="attendance__separator">〜</span>
                        <input type="time" class="attendance__time attendance__time--out" name="break_end_time[]">
                    </div>
                </div>
            </div>
            <div class="attendance__row">
                <div class="attendance__label">備考</div>
                <div class="attendance__value">
                    <textarea class="attendance__textarea" name="note">{{ $attendance->note }}</textarea>
                </div>
            </div>
        </div>
        <div class="attendance__button-container">
            <button class="attendance__button">修正</button>
        </div>
    </div>
</div>
@endsection

