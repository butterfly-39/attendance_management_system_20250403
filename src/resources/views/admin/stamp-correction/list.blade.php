@extends('layouts.app')

@php
use Carbon\Carbon;
use Illuminate\Support\Str;
@endphp

@section('css')
<link rel="stylesheet" href="{{ asset('css/stamp-correction/list.css')}}">
@endsection

@section('content')
<div class="stamp-correction">
    <div class="stamp-correction__content">
        <h2 class="stamp-correction__heading">申請一覧</h2>

        <div class="stamp-correction__status">
            <a href="{{ route('admin.stamp_correction_request.list', ['status' => '承認待ち']) }}"
            class="stamp-correction__status-tab stamp-correction__status-tab--pending {{ request()->get('status') === '承認待ち' ? 'active' : '' }}">
                承認待ち
            </a>
            <a href="{{ route('admin.stamp_correction_request.list', ['status' => '承認済み']) }}"
            class="stamp-correction__status-tab stamp-correction__status-tab--approved {{ request()->get('status') === '承認済み' ? 'active' : '' }}">
                承認済み
            </a>
            <div class="stamp-correction__status-border"></div>
        </div>

        <div class="stamp-correction__list">
            <div class="stamp-correction__item stamp-correction__item--header">
                <div class="stamp-correction__item-header">状態</div>
                <div class="stamp-correction__item-header">名前</div>
                <div class="stamp-correction__item-header">対象日時</div>
                <div class="stamp-correction__item-header">申請理由</div>
                <div class="stamp-correction__item-header">申請日時</div>
                <div class="stamp-correction__item-header">詳細</div>
            </div>
            @foreach($stampCorrections as $stampCorrection)
            <div class="stamp-correction__item">
                <div class="stamp-correction__item-status">{{ $stampCorrection->status }}</div>
                <div class="stamp-correction__item-name">{{ $stampCorrection->user->name }}</div>
                <div class="stamp-correction__item-date">{{ Carbon::parse($stampCorrection->clock_in_time)->format('Y/m/d') }}</div>
                <div class="stamp-correction__item-note">{{ Str::limit($stampCorrection->note, 5, '...') }}</div>
                <div class="stamp-correction__item-created-at">{{ Carbon::parse($stampCorrection->created_at)->format('Y/m/d') }}</div>
                <div class="stamp-correction__item-actions">
                    <a href="{{ route('admin.attendance.show', ['id' => $stampCorrection->attendance_id]) }}">詳細</a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
