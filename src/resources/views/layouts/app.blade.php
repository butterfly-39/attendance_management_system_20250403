<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COACHTECH</title>
    <link rel="stylesheet" href="{{ asset('css/base/common.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ja.js"></script>
    @yield('css')
</head>
<body>
    <header class="header">
        <h1 class="header__heading">
            <a href="/login">
                <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH" class="header__logo">
            </a>
        </h1>
        <nav class="header__nav">
            <ul class="header__nav-list">
                @if(auth()->user()->is_admin)
                    <li class="header__nav-item">
                        <a href="/admin/attendance" class="header__nav-link header__nav-link--bold">勤怠一覧</a>
                    </li>
                    <li class="header__nav-item">
                        <a href="/staff/list" class="header__nav-link header__nav-link--bold">スタッフ一覧</a>
                    </li>
                    <li class="header__nav-item">
                        <a href="/stamp_correction_request/list" class="header__nav-link header__nav-link--bold">申請一覧</a>
                    </li>
                @else
                    @if(auth()->user()->attendance && auth()->user()->attendance->status === '退勤済')
                        <li class="header__nav-item">
                            <a href="/attendance/list" class="header__nav-link">今月の出勤一覧</a>
                        </li>
                        <li class="header__nav-item">
                            <a href="/stamp_correction_request/list" class="header__nav-link">申請一覧</a>
                        </li>
                    @else
                        <li class="header__nav-item">
                            <a href="/attendance" class="header__nav-link header__nav-link--bold">勤怠</a>
                        </li>
                        <li class="header__nav-item">
                            <a href="/attendance/list" class="header__nav-link header__nav-link--bold">勤怠一覧</a>
                        </li>
                        <li class="header__nav-item">
                            <a href="/stamp_correction_request/list" class="header__nav-link header__nav-link--bold">申請</a>
                        </li>
                    @endif
                @endif
                <li class="header__nav-item">
                    <form action="{{ route(auth()->user()->is_admin ? 'admin.logout' : 'logout') }}" method="POST" class="header__logout-form">
                        @csrf
                        <button type="submit" class="header__nav-link header__logout-button header__nav-link--bold">ログアウト</button>
                    </form>
                </li>
            </ul>
        </nav>
    </header>

    <main>
        @yield('content')
    </main>
    @yield('scripts')
</body>
</html>