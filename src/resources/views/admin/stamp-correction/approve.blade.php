@extends('layouts.app')

@php
use Carbon\Carbon;
@endphp

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/stamp-correction/approve.css')}}">
@endsection

@section('content')
<div class="stamp-correction">
    <div class="stamp-correction__content">
        <h2 class="stamp-correction__heading">勤怠詳細</h2>

        <form action="{{ route('admin.stamp_correction_request.approve', ['attendance_correction_request' => $stampCorrection->id]) }}" method="POST">
            @csrf
            @method('POST')
            <div class="stamp-correction__detail">
                <div class="stamp-correction__row">
                    <div class="stamp-correction__label">名前</div>
                    <div class="stamp-correction__value">{{ $attendance->user->name }}</div>
                </div>
                <div class="stamp-correction__row">
                    <div class="stamp-correction__label">日付</div>
                    <div class="stamp-correction__value">
                        <span class="stamp-correction__year">{{ Carbon::parse($attendance->date)->format('Y年') }}</span>
                        <span class="stamp-correction__month-day">{{ Carbon::parse($attendance->date)->format('n月j日') }}</span>
                    </div>
                </div>
                <div class="stamp-correction__row">
                    <div class="stamp-correction__label">出勤・退勤</div>
                    <div class="stamp-correction__value">
                        <span class="stamp-correction__time--in">{{ Carbon::parse($stampCorrection->clock_in_time)->format('H:i') }}</span>
                        <span class="stamp-correction__separator">〜</span>
                        <span class="stamp-correction__time--out">{{ $stampCorrection->clock_out_time ? Carbon::parse($stampCorrection->clock_out_time)->format('H:i') : '' }}</span>
                    </div>
                </div>
                <div class="stamp-correction__row break-time">
                    <div class="stamp-correction__label">休憩</div>
                    <div class="stamp-correction__value">
                        @foreach($breakCorrections as $break)
                            <div class="break-time-row">
                                <span class="stamp-correction__time--in">{{ Carbon::parse($break->break_start_time)->format('H:i') }}</span>
                                <span class="stamp-correction__separator">〜</span>
                                <span class="stamp-correction__time--out">{{ $break->break_end_time ? Carbon::parse($break->break_end_time)->format('H:i') : '' }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="stamp-correction__row">
                    <div class="stamp-correction__label">備考</div>
                    <div class="stamp-correction__value">
                        {{ $stampCorrection->note }}
                    </div>
                </div>
            </div>

            <div class="stamp-correction__button-container">
                @if($stampCorrection->status === '承認済み')
                    <button type="button" class="stamp-correction__button stamp-correction__button--approved" disabled>承認済み</button>
                @else
                    <button type="submit" class="stamp-correction__button">承認</button>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection

