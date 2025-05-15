@extends('layouts.app')

@php
use Carbon\Carbon;
@endphp

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff/list.css')}}">
@endsection

@section('content')
<div class="staff">
    <div class="staff__content">
        <h2 class="staff__heading">スタッフ一覧</h2>

        <div class="staff__list">
            <div class="staff__item staff__item--header">
                <div class="staff__item-header">名前</div>
                <div class="staff__item-header">メールアドレス</div>
                <div class="staff__item-header">月次勤怠</div>
            </div>
            @foreach($staff as $user)
                <div class="staff__item">
                    <div class="staff__name">{{ $user->name }}</div>
                    <div class="staff__email">{{ $user->email }}</div>
                    <div class="staff__attendance">
                        <a href="{{ route('admin.staff.monthly', ['id' => $user->id]) }}" class="btn btn-primary">詳細</a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection


