@extends('layouts.auth')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/verify-email.css')}}">
@endsection

@section('content')
<div class="verify-email">
    <div class="verify-email__inner">
        <div class="verify-email__message">
            <p>登録していただいたメールアドレスに認証メールを送付しました。</p>
            <p>メール認証を完了してください。</p>
        </div>

        @if (session('resent'))
            <div class="verify-email__success">
                <p>{{ session('resent') }}</p>
            </div>
        @endif

        <div class="verify-email__actions">
            <div class="verify-email__verify-btn-container">
                <button type="button" class="verify-email__verify-btn" disabled>認証はこちらから</button>
            </div>

            <form method="POST" action="{{ route('verification.resend') }}" class="verify-email__resend-form">
                @csrf
                <button type="submit" class="verify-email__resend-link">
                    認証メールを再送する
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
