@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/index.css')}}">
@endsection

@section('content')
<div class="attendance">
    <div class="attendance__content">
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
    </div>
</div>
@endsection