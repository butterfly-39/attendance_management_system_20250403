<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COACHTECH</title>
    <link rel="stylesheet" href="{{ asset('css/base/common.css') }}">
    @yield('css')
</head>
<body>
    <header>
        <h1 class="header__heading">
            <a href="/">
                <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH" class="header__logo">
            </a>
        </h1>
        <nav class="header__nav">
            <ul class="header__nav-list">
                <li class="header__nav-item">
                    <a href="/attendance" class="header__nav-link">勤怠</a>
                </li>
                <li class="header__nav-item">
                    <a href="/attendance/list" class="header__nav-link">勤怠一覧</a>
                </li>
                <li class="header__nav-item">
                    <a href="/stamp_correction_request/list" class="header__nav-link">申請</a>
                </li>
                <li class="header__nav-item">
                    <form action="/logout" method="POST" class="header__logout-form">
                        @csrf
                        <button type="submit" class="header__nav-link header__logout-button">ログアウト</button>
                    </form>
                </li>
            </ul>
        </nav>
    </header>
    <main>
        @yield('content')
    </main>
</body>
</html>