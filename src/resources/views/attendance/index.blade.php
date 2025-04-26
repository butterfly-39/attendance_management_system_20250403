@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/index.css')}}">
@endsection

@section('content')
<div class="attendance">
    <div class="attendance__content">
        <div class="attendance__status">
            <p class="attendance__status-text">{{ $attendance->status ?? '勤務外' }}</p>
        </div>
        <div class="attendance__info">
            @php
                $now = \Carbon\Carbon::now();
                $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
            @endphp
            <div class="attendance__date-group">
                <p class="attendance__date">{{ $now->format('Y年n月j日') }}</p>
                <p class="attendance__day">({{ $weekdays[$now->dayOfWeek] }})</p>
            </div>
            <p class="attendance__time">{{ $now->format('H:i') }}</p>
        </div>
        <div class="attendance__buttons">
            @if(!$attendance || $attendance->status === '勤務外')
                <form class="attendance__button-form" action="/attendance" method="post">
                    @csrf
                    <input type="hidden" name="action" value="clock_in">
                    <button class="attendance__button attendance__button--in" type="submit">出勤</button>
                </form>
            @elseif($attendance->status === '出勤中')
                <form class="attendance__button-form" action="/attendance" method="post">
                    @csrf
                    <input type="hidden" name="action" value="clock_out">
                    <button class="attendance__button attendance__button--break-in" type="submit">退勤</button>
                </form>
                <form class="attendance__button-form" action="/attendance" method="post">
                    @csrf
                    <input type="hidden" name="action" value="break_start">
                    <button class="attendance__button attendance__button--out" type="submit">休憩入</button>
                </form>
            @elseif($attendance->status === '休憩中')
                <form class="attendance__button-form" action="/attendance" method="post">
                    @csrf
                    <input type="hidden" name="action" value="break_end">
                    <button class="attendance__button attendance__button--break-out" type="submit">休憩戻</button>
                </form>
            @elseif($attendance->status === '退勤済')
                <p class="attendance__complete-message">お疲れ様でした。</p>
            @endif
        </div>
    </div>
</div>
@endsection