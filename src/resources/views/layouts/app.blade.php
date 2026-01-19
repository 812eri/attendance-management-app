<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Atte')</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layout.css') }}">
    @yield('css')
</head>
<body>
    <div class="l-page-wrapper">

        <header class="header-minimal">
            <div class="header__logo">
                <a href="{{ route('attendance.index') }}" class="header-main__logo-link">
                    <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH" class="header-main__logo-img">
                </a>
            </div>

            <nav class="header__nav">
                <ul>
                    <li><a href="{{ route('attendance.index') }}">勤怠</a></li>
                    <li><a href="{{ route('attendance.list') }}">勤怠一覧</a></li>
                    <li><a href="{{ route('stamp_correction_request.index') }}">申請</a></li>
                    <li>
                        <form action="{{ route('logout') }}" method="post">
                            @csrf
                            <button type="submit">ログアウト</button>
                        </form>
                    </li>
                </ul>
            </nav>
        </header>

        <main class="l-main-content">
            @yield('content')
        </main>
    </div>
</body>
</html>