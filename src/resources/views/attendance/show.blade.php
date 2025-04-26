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
        <h2 class="attendance__heading">勤怠詳細</h2>
        <div class="attendance__detail">
            <p>名前 {{ $attendance->user->name }}</p>
            <p>日付 {{ Carbon::parse($attendance->date)->format('Y年m月d日') }}</p>
            <p>出勤・退勤 {{ Carbon::parse($attendance->clock_in_time)->format('H:i') }} 〜 {{ Carbon::parse($attendance->clock_out_time)->format('H:i') }}</p>
            <p>休憩 {{ Carbon::parse($breakTimes->break_start_time)->format('H:i') }} 〜 {{ Carbon::parse($breakTimes->break_end_time)->format('H:i') }}</p>
            <p>備考</p>
            <textarea class="attendance__detail-text">{{ $attendance->note }}</textarea>
        </div>
        <div class="attendance__button-container">
            <button class="attendance__button">修正</button>
        </div>
    </div>
</div>
@endsection

