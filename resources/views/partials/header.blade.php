{{-- JARVIS BOOT SEQUENCE --}}
<div class="boot-sequence" id="bootSequence" aria-hidden="true">
    <div class="boot-sequence__grid"></div>
    <div class="boot-sequence__ring">
        <span class="boot-sequence__ring-inner"></span>
        <span class="boot-sequence__core"></span>
    </div>
    <div class="boot-sequence__title">FILM<span>.SYS</span></div>
    <div class="boot-sequence__subtitle">J.A.R.V.I.S CORE INITIALIZATION</div>
    <div class="boot-sequence__log" id="bootLog"></div>
    <div class="boot-sequence__bar-wrap">
        <div class="boot-sequence__bar">
            <div class="boot-sequence__bar-fill" id="bootBarFill"></div>
        </div>
        <span class="boot-sequence__pct" id="bootPct">0%</span>
    </div>
</div>

<div class="jarvis-scanlines" aria-hidden="true"></div>
<div class="jarvis-vignette" aria-hidden="true"></div>

<div class="boot-strip">
    <span class="boot-strip__item">FILM.SYS // ONLINE</span>
    <span class="boot-strip__item boot-strip__item--sep">::</span>
    <span class="boot-strip__item">UPLINK STABLE</span>
    <span class="boot-strip__item boot-strip__item--sep">::</span>
    <span class="boot-strip__item" id="clockReadout">00:00:00</span>
</div>

@php
$navGenres = isset($genres)
? collect($genres)->reject(fn ($genre) => $genre === 'Tất cả')->values()
: \App\Models\Genre::orderBy('name')->pluck('name');

$isLoggedIn = session('user_logged_in', false);
$currentUserName = session('user_name', 'User');
$currentUserRole = session('user_role', 'member');
@endphp

<header class="site-header hud-panel" data-hud="true">
    <div class="hud-corner hud-corner--tl"></div>
    <div class="hud-corner hud-corner--tr"></div>
    <div class="hud-corner hud-corner--bl"></div>
    <div class="hud-corner hud-corner--br"></div>

    <div class="site-header__inner container">
        <a href="{{ route('home') }}" class="brand">
            <span class="brand__ring">
                <span class="brand__core"></span>
            </span>
            <span class="brand__text">
                FILM<span class="brand__text-accent">.SYS</span>
                <small>ONLINE CINEMA NETWORK</small>
            </span>
        </a>

        <nav class="main-nav" id="mainNav">
            <a href="{{ route('home') }}"
                class="main-nav__link {{ request()->routeIs('home') ? 'is-active' : '' }}">
                TRANG CHỦ
            </a>

            <a href="{{ route('home') }}#featured" class="main-nav__link">
                PHIM ĐỀ CỬ
            </a>

            <a href="{{ route('home') }}#grid" class="main-nav__link">
                KHO DỮ LIỆU
            </a>

            <div class="nav-dropdown" id="genreNavDropdown">
                <a href="javascript:void(0)"
                    class="main-nav__link"
                    id="genreNavBtn"
                    role="button"
                    aria-expanded="false"
                    aria-haspopup="true">
                    THỂ LOẠI
                </a>

                <div class="genre-dropdown-menu" id="genreDropdownMenu">
                    <div class="genre-dropdown-grid">
                        <a href="{{ route('home', ['genre' => 'Tất cả']) }}#grid"
                            class="genre-item genre-item--all"
                            data-genre="Tất cả">
                            Tất cả thể loại
                        </a>

                        @foreach ($navGenres as $genre)
                        <a href="{{ route('home', ['genre' => $genre]) }}#grid"
                            class="genre-item"
                            data-genre="{{ $genre }}">
                            {{ $genre }}
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <a href="{{ url('/history') }}"
                class="main-nav__link {{ request()->is('history*') ? 'is-active' : '' }}">
                LỊCH SỬ
            </a>

            <a href="{{ url('/favorites') }}"
                class="main-nav__link {{ request()->is('favorites*') ? 'is-active' : '' }}">
                YÊU THÍCH
            </a>
        </nav>

        <div class="header-actions">
            <form class="hud-search" action="{{ route('home') }}" method="get"
                autocomplete="off">
                <span class="hud-search__icon">◎</span>
                <input type="text"
                    id="movieSearchInput"
                    name="q"
                    value="{{ request('q') }}"
                    class="hud-search__input"
                    placeholder="QUÉT DỮ LIỆU PHIM…">
                <button type="button" class="hud-search__clear" id="movieSearchClear"
                    aria-label="Xóa tìm kiếm" hidden>✕</button>
                <span class="hud-search__sweep"><span class="hud-search__scan"></span></span>
                <div id="movieSuggestions"
                    class="movie-suggestions"
                    aria-live="polite"></div>
            </form>

            @if ($isLoggedIn)
            <div class="auth-actions">
                @if ($currentUserRole === 'admin')
                <a href="{{ url('/admin') }}"
                    class="btn-hud btn-hud--primary auth-btn">
                    ADMIN
                </a>
                @endif

                <span class="auth-user">
                    Xin chào, {{ $currentUserName }}
                </span>


                <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn-hud btn-hud--ghost auth-btn">
                        <span class="btn-hud__icon">▶</span> ĐĂNG XUẤT
                    </button>
                </form>

            </div>
            @else

            <div class="auth-actions">
                @if (session('user_logged_in'))
                <span class="auth-btn" style="color:#00f0ff;">
                    XIN CHÀO, {{ session('user_name') }}
                </span>

                @if (session('user_role') === 'admin')
                <span class="auth-btn" style="color:#f5d06f;">
                    QUẢN TRỊ VIÊN
                </span>
                @endif

                
                @else
                <a href="{{ route('login') }}" class="btn-hud btn-hud--ghost auth-btn">
                    ĐĂNG NHẬP
                </a>

                <a href="{{ route('register') }}" class="btn-hud btn-hud--primary auth-btn">
                    ĐĂNG KÝ
                </a>
                @endif
            </div>

            @endif

            <button class="btn-hud btn-hud--ghost"
                id="navToggle"
                type="button"
                aria-label="Mở menu">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
</header>