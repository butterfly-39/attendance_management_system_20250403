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
    <h1 class="header__heading">
        <a href="/">
            <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH" class="header__logo">
        </a>
    </h1>
    <main>
        @yield('content')
    </main>
</body>
</html>