@extends('layouts.app')

@php
use Carbon\Carbon;
@endphp

@section('css')
<link rel="stylesheet" href="{{ asset('css/stamp-correction/list.css')}}">
@endsection

@section('content')
<div class="stamp-correction">
    <div class="stamp-correction__content">
        <h2 class="stamp-correction__heading">申請一覧</h2>

        <div class="stamp-correction__status">
            <p>承認待ち</p>
            <p>承認済み</p>
        </div>

        <div class="stamp-correction__list">
            <p>状態</p>
            <p>名前</p>
            <p>対象日時</p>
            <p>申請理由</p>
            <p>申請日時</p>
            <p>詳細</p>
        </div>
    </div>
</div>
@endsection
