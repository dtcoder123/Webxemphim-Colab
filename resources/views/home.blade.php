@extends('layouts.app')

@section('title', 'Trang chủ — FILM-SYS')

@section('content')
<script>
    window.movieCatalog = @json($movieCatalog);
</script>

<main>
    {{-- HERO / HOLOGRAM BANNER --}}
    <section class="hero" id="featured">
        <div class="hero__bg"></div>
        <canvas class="hero__aero-shards" aria-hidden="true"></canvas>
        <div class="hero__scrim"></div>
        <div class="hero__grid-overlay" aria-hidden="true"></div>
        <canvas class="hero__glow-cursor" aria-hidden="true"></canvas>

        @if ($featured)
            <div class="container hero__inner">
                <div class="hero__content">
                    <p class="eyebrow">
                        <span class="eyebrow__dot"></span>
                        ĐANG PHÁT SÓNG TRỰC TIẾP TỪ TRUNG TÂM DỮ LIỆU
                    </p>

                    <h1 class="hero__title glitch"
                        data-text="{{ $featured->title }}">
                        {{ $featured->title }}
                    </h1>

                    <p class="hero__tagline">{{ $featured->tagline }}</p>

                    <div class="hero__meta">
                        <span class="meta-chip meta-chip--rating">
                            ★ {{ $featured->rating }}
                        </span>
                        <span class="meta-chip">{{ $featured->year }}</span>
                        <span class="meta-chip">{{ $featured->duration }}</span>
                        <span class="meta-chip">{{ $featured->genre }}</span>
                    </div>

                    <div class="hero__energy-bar">
                        <span class="hero__energy-fill"
                              data-rating="{{ (float) $featured->rating }}">
                        </span>
                    </div>

                    <div class="hero__actions">
                        <a href="{{ route('movies.show', $featured->id) }}"
                           class="btn-hud btn-hud--primary">
                            <span class="btn-hud__icon">▶</span>
                            XEM NGAY
                        </a>

                        <a href="{{ route('movies.show', $featured->id) }}#movieInfo"
                           class="btn-hud btn-hud--ghost btn-hud--wide">
                            <span class="btn-hud__icon">ⓘ</span>
                            THÔNG TIN SYSTEM
                        </a>
                    </div>
                </div>

                <div class="hero__visual">
                    <div class="hero__poster-wrap">
                        <img src="{{ $featured->poster }}"
                             alt="{{ $featured->title }}"
                             class="hero__poster-img">
                        <div class="hologram-flicker"></div>
                        <div class="hologram-lines"></div>
                    </div>

                    <div class="hero__rail" aria-label="Phim đề xuất">
                        @foreach ($featuredRailMovies as $mini)
                            <button type="button"
                                    class="hero__mini-card {{ $mini->id == $featured->id ? 'is-selected' : '' }}"
                                    title="{{ $mini->title }}"
                                    data-id="{{ $mini->id }}"
                                    data-title="{{ $mini->title }}"
                                    data-tagline="{{ $mini->tagline }}"
                                    data-genre="{{ $mini->genre }}"
                                    data-poster="{{ $mini->poster }}"
                                    data-year="{{ $mini->year }}"
                                    data-duration="{{ $mini->duration }}"
                                    data-rating="{{ $mini->rating }}">
                                <img src="{{ $mini->poster }}"
                                     alt="{{ $mini->title }}"
                                     loading="lazy"
                                     onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1536440136628-849c177e76a1?q=80&w=800&auto=format&fit=crop';">
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <div class="container hero__inner">
                <div class="hero__content">
                    <p class="eyebrow">FILM.SYS // CINEMA NETWORK</p>
                    <h1 class="hero__title">KHO DỮ LIỆU PHIM</h1>
                    <p class="hero__tagline">Hiện chưa có phim đang hoạt động.</p>
                </div>
            </div>
        @endif
    </section>

    {{-- CATEGORY / FILTER TABS --}}
    <section class="filters container">
        <div class="filters__label">
            <span class="filters__label-dash"></span>
            BỘ LỌC DỮ LIỆU
        </div>

        <div class="filter-tabs" id="filterTabs">
            @foreach ($genres as $genre)
                <button type="button"
                        class="filter-tab {{ mb_strtolower($genre) === mb_strtolower($activeGenre) ? 'is-active' : '' }}"
                        data-genre="{{ $genre }}">
                    {{ $genre }}
                </button>
            @endforeach
        </div>
    </section>

    {{-- MOVIE GRID / MOVIE SHELVES --}}
    <section class="movie-grid-section container" id="grid">
        <div class="section-heading">
            <h2>
                <span class="section-heading__index">02</span>
                KHO DỮ LIỆU PHIM
            </h2>

            <span class="section-heading__count">
                {{ $movies->count() }} ENTRIES FOUND
            </span>
        </div>

        <div class="movie-shelves" id="movieGrid">
            @forelse ($movieShelves as $shelfGenre => $shelfMovies)
                <section class="movie-shelf">
                    <div class="movie-shelf__intro">
                        <h3 class="movie-shelf__title">{{ $shelfGenre }}</h3>
                        <a href="#grid" class="movie-shelf__link">
                            Xem toàn bộ <span aria-hidden="true">›</span>
                        </a>
                    </div>

                    <div class="movie-shelf__track">
                        @foreach ($shelfMovies as $movie)
                            @php
                                $cast = $movie->cast;

                                if (is_string($cast)) {
                                    $cast = json_decode($cast, true) ?: [];
                                }

                                if (!is_array($cast)) {
                                    $cast = [];
                                }

                                $keywords = mb_strtolower(
                                    $movie->title . ' ' .
                                    $movie->genre . ' ' .
                                    $movie->tagline . ' ' .
                                    implode(' ', $cast)
                                );
                            @endphp

                            <a href="{{ route('movies.show', $movie->id) }}"
                               class="movie-card"
                               data-id="{{ $movie->id }}"
                               data-title="{{ $movie->title }}"
                               data-genre="{{ $movie->genre }}"
                               data-tagline="{{ $movie->tagline }}"
                               data-keywords="{{ $keywords }}">
                                <div class="movie-card__frame">
                                    <div class="hud-corner hud-corner--tl"></div>
                                    <div class="hud-corner hud-corner--br"></div>

                                    @if ($movie->featured)
                                        <span class="movie-card__badge">HOT</span>
                                    @endif

                                    <img src="{{ $movie->poster }}"
                                         alt="{{ $movie->title }}"
                                         class="movie-card__img"
                                         loading="lazy">

                                    <div class="movie-card__overlay">
                                        <span class="movie-card__play">▶</span>
                                    </div>
                                </div>

                                <div class="movie-card__body">
                                    <h3 class="movie-card__title">
                                        {{ $movie->title }}
                                    </h3>

                                    <div class="movie-card__meta">
                                        <span>{{ $movie->tagline }}</span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    <button class="movie-shelf__next"
                            type="button"
                            aria-label="Xem thêm phim">›</button>
                </section>
            @empty
                <p>Chưa có phim trong kho dữ liệu.</p>
            @endforelse
        </div>
    </section>

    <div class="container"
         style="text-align:center; margin-top:90px; margin-bottom:50px; clear:both; width:100%;">
        <button class="btn-hud btn-hud--ghost btn-hud--wide"
                id="loadMoreBtn"
                type="button"
                style="display:inline-block; margin:0 auto;">
            TẢI THÊM DỮ LIỆU ⌄
        </button>
    </div>
</main>
@endsection
