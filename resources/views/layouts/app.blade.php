<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'FILM-SYS // CINEMA NETWORK')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&family=Chakra+Petch:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=Rajdhani:wght@400;500;600;700&display=swap"
          rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v=laravel2">
    <link rel="stylesheet" href="{{ asset('assets/css/jarvis-hud.css') }}?v=laravel2">

    @if (in_array(trim($__env->yieldContent('page-type')), ['login', 'register']))
        <link rel="stylesheet" href="{{ asset('assets/css/auth-drift-wall.css') }}">
    @endif
</head>
<body>
    @include('partials.header')

    @yield('content')

    @include('partials.footer')

    <script src="{{ asset('assets/js/main.js') }}?v=laravel2"></script>
    <script src="{{ asset('assets/js/glow-cursor.js') }}?v=laravel2"></script>
    <script src="{{ asset('assets/js/warp-text.js') }}?v=laravel2"></script>

    @stack('scripts')
</body>
</html>
