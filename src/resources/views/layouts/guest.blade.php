<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Atte')</title>

    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layout.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/forms.css') }}">

    <link rel="stylesheet" href="{{ asset('css/guest.css') }}">
    @yield('css')
</head>
<body>
    <div class="l-page-wrapper">
        <header class="header-minimal">
            <a href="/" class="header-main__logo-link">
                <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH" class="header-main__logo-img">
            </a>
        </header>

        <main class="l-main-content l-main-content--center">
            @yield('content')
        </main>
    </div>
</body>
</html>