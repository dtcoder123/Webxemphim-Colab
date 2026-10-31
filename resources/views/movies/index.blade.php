@extends('layouts.app')

@section('title', 'Kho dữ liệu phim — FILM.SYS')

@section('content')
<main class="container" style="padding-top: 50px; padding-bottom: 80px;">

    {{-- TIÊU ĐỀ TRANG --}}
    <section class="movie-list-hero">
        <p class="eyebrow">
            <span class="eyebrow__dot"></span>
            FILM.SYS // MOVIE DATABASE
        </p>

        <h1 class="hero__title" style="font-size: clamp(28px, 5vw, 48px);">
            KHO DỮ LIỆU PHIM
        </h1>

        <p class="hero__tagline">
            Khám phá, tìm kiếm và lựa chọn bộ phim bạn yêu thích.
        </p>
    </section>

    {{-- FORM TÌM KIẾM VÀ LỌC --}}
    <form action="{{ route('movies.index') }}"
        method="GET"
        class="movie-filter-panel"
        style="display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 35px;">

        <input
            type="text"
            name="search"
            value="{{ request('search', '') }}"
            placeholder="Nhập tên phim, thể loại, đạo diễn..."
            aria-label="Tìm kiếm phim"
            style="flex: 1 1 240px; min-width: 0; padding: 12px 15px;
           color: #fff; background: #101a27;
           border: 1px solid #294052; border-radius: 6px;">

        <select name="genre"
            aria-label="Lọc theo thể loại"
            style="flex: 1 1 160px; padding: 12px;
                       color: #fff; background: #101a27;
                       border: 1px solid #294052; border-radius: 6px;">
            <option value="">Tất cả thể loại</option>

            @foreach ($genres as $itemGenre)
            <option value="{{ $itemGenre }}"
                {{ $genre === $itemGenre ? 'selected' : '' }}>
                {{ $itemGenre }}
            </option>
            @endforeach
        </select>

        <select name="sort"
            aria-label="Sắp xếp phim"
            style="flex: 1 1 160px; padding: 12px;
                       color: #fff; background: #101a27;
                       border: 1px solid #294052; border-radius: 6px;">
            <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>
                Mới nhất
            </option>
            <option value="rating" {{ $sort === 'rating' ? 'selected' : '' }}>
                Đánh giá cao
            </option>
            <option value="year" {{ $sort === 'year' ? 'selected' : '' }}>
                Năm phát hành
            </option>
            <option value="title" {{ $sort === 'title' ? 'selected' : '' }}>
                Tên A - Z
            </option>
        </select>

        <button type="submit" class="btn-hud btn-hud--primary">
            TÌM PHIM
        </button>

        <a href="{{ route('movies.index') }}"
            class="btn-hud btn-hud--ghost">
            XÓA LỌC
        </a>
    </form>

    {{-- DANH SÁCH PHIM --}}
    <section class="movie-grid-section">
        <div class="section-heading">
            <h2>
                <span class="section-heading__index">01</span>
                DANH SÁCH PHIM
            </h2>

            <span class="section-heading__count">
                {{ $movies->total() }} PHIM
            </span>
        </div>

        <div class="movie-list-grid">

            @forelse ($movies as $movie)
            <a href="{{ route('movies.show', $movie->id) }}"
                class="movie-card"
                style="min-width: 0;">

                <div class="movie-card__frame">
                    <div class="hud-corner hud-corner--tl"></div>
                    <div class="hud-corner hud-corner--br"></div>

                    <img src="{{ $movie->poster }}"
                        alt="{{ $movie->title }}"
                        class="movie-card__img"
                        loading="lazy"
                        onerror="this.onerror=null;this.style.opacity='0.2';">

                    <div class="movie-card__overlay">
                        <span class="movie-card__play">▶</span>
                    </div>
                </div>

                <div class="movie-card__body">
                    <h3 class="movie-card__title">
                        {{ $movie->title }}
                    </h3>

                    <div class="movie-card__meta">
                        <span>{{ $movie->genre ?: 'Chưa phân loại' }}</span>
                    </div>

                    <div style="font-size: 12px; opacity: .75; margin-top: 6px;">
                        @if (!empty($movie->year))
                        {{ $movie->year }}
                        @endif

                        @if (!empty($movie->duration))
                        · {{ $movie->duration }}
                        @endif
                    </div>

                    @if (isset($movie->rating) && $movie->rating !== null)
                    <div style="font-size: 12px; margin-top: 6px;">
                        ★ {{ $movie->rating }}
                    </div>
                    @endif
                </div>
            </a>
            @empty
            <div style="grid-column: 1 / -1; padding: 40px 15px;
                            text-align: center; border: 1px solid #294052;
                            border-radius: 8px;">
                <h3>KHÔNG TÌM THẤY PHIM</h3>
                <p>Thử thay đổi từ khóa hoặc bộ lọc của bạn.</p>
                <a href="{{ route('movies.index') }}"
                    class="btn-hud btn-hud--primary">
                    XEM TẤT CẢ PHIM
                </a>
            </div>
            @endforelse
        </div>

        {{-- PHÂN TRANG --}}
        <div class="movie-pagination">
            {{ $movies->links() }}
        </div>
    </section>

</main>
@endsection

<style>
    /* ===== TRANG DANH SÁCH PHIM ===== */
    .movie-list-page {
        color: #e8f0f8;
    }

    .movie-list-hero {
        position: relative;
        padding: 28px;
        margin-bottom: 32px;
        overflow: hidden;
        background: linear-gradient(120deg, #101d2c, #0a111c);
        border: 1px solid #20374a;
        border-radius: 12px;
    }

    .movie-list-hero::after {
        content: "";
        position: absolute;
        width: 220px;
        height: 220px;
        top: -100px;
        right: -60px;
        border: 1px solid rgba(77, 190, 255, .16);
        border-radius: 50%;
        box-shadow: 0 0 0 24px rgba(77, 190, 255, .025),
            0 0 0 48px rgba(77, 190, 255, .025);
        pointer-events: none;
    }

    .movie-list-hero .hero__title {
        letter-spacing: .04em;
        line-height: 1.2;
    }

    .movie-list-hero .hero__tagline {
        max-width: 600px;
        color: #9aafc3;
        line-height: 1.8;
    }

    /* Form tìm kiếm và bộ lọc */
    .movie-filter-panel {
        padding: 18px;
        margin-bottom: 32px;
        background: #0c1521;
        border: 1px solid #20374a;
        border-radius: 10px;
    }

    .movie-filter-panel input,
    .movie-filter-panel select {
        min-height: 46px;
        font: inherit;
        font-size: 14px;
        outline: none;
        transition: border-color .2s, box-shadow .2s;
    }

    .movie-filter-panel input:focus,
    .movie-filter-panel select:focus {
        border-color: #54c8ff !important;
        box-shadow: 0 0 0 3px rgba(84, 200, 255, .1);
    }

    .movie-filter-panel input::placeholder {
        color: #71859a;
    }

    .movie-filter-panel button,
    .movie-filter-panel a {
        min-height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        cursor: pointer;
    }

    /* Lưới phim */
    .movie-list-grid {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 26px 18px;
    }

    .movie-list-grid .movie-card {
        display: block;
        min-width: 0;
        color: inherit;
        text-decoration: none;
        border-radius: 8px;
        transition: transform .25s ease;
    }

    .movie-list-grid .movie-card:hover {
        transform: translateY(-5px);
    }

    .movie-list-grid .movie-card__frame {
        position: relative;
        aspect-ratio: 2 / 3;
        overflow: hidden;
        background: #111e2b;
        border: 1px solid #263d50;
        border-radius: 8px;
        box-shadow: 0 5px 18px rgba(0, 0, 0, .18);
    }

    .movie-list-grid .movie-card__img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform .4s ease, filter .3s ease;
    }

    .movie-list-grid .movie-card:hover .movie-card__img {
        transform: scale(1.06);
        filter: brightness(.78);
    }

    .movie-list-grid .movie-card__overlay {
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity .25s ease;
    }

    .movie-list-grid .movie-card:hover .movie-card__overlay {
        opacity: 1;
    }

    .movie-list-grid .movie-card__play {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 46px;
        height: 46px;
        color: #07121d;
        background: #63d4ff;
        border-radius: 50%;
        box-shadow: 0 0 24px rgba(99, 212, 255, .3);
    }

    .movie-list-grid .movie-card__body {
        padding: 12px 2px 0;
    }

    .movie-list-grid .movie-card__title {
        display: -webkit-box;
        overflow: hidden;
        min-height: 2.8em;
        margin: 0 0 7px;
        color: #e8f0f8;
        font-size: 14px;
        font-weight: 600;
        line-height: 1.4;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
    }

    .movie-list-grid .movie-card:hover .movie-card__title {
        color: #63d4ff;
    }

    .movie-list-grid .movie-card__meta {
        overflow: hidden;
        color: #9aafc3;
        font-size: 12px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }


    /* ===== PHÂN TRANG FILM.SYS ===== */
    .movie-pagination {
        display: flex;
        justify-content: center;
        width: 100%;
        margin-top: 40px;
        padding-top: 24px;
        border-top: 1px solid rgba(84, 200, 255, .12);
    }

    .movie-pagination nav {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 14px;
        max-width: 100%;
    }

    /* Dòng thông tin kết quả */
    .movie-pagination nav>div:first-child {
        color: #8ca3b8;
        font-size: 12px;
        text-align: center;
    }

    /* Khu vực các nút chuyển trang */
    .movie-pagination nav>div:last-child {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 7px;
    }

    /* Nút, số trang và mũi tên */
    .movie-pagination nav a,
    .movie-pagination nav span[aria-current="page"]>span,
    .movie-pagination nav span[aria-disabled="true"]>span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 40px;
        height: 40px;
        padding: 0 12px;
        color: #a9bfd2;
        background: #0c1521;
        border: 1px solid #263d50;
        border-radius: 5px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        transition: color .2s, background .2s, border-color .2s,
            box-shadow .2s;
    }

    /* Rê chuột lên nút */
    .movie-pagination nav a:hover {
        color: #63d4ff;
        background: #132638;
        border-color: #63d4ff;
        box-shadow: 0 0 12px rgba(99, 212, 255, .1);
    }

    /* Trang đang được chọn */
    .movie-pagination nav span[aria-current="page"]>span {
        color: #07121d;
        background: #63d4ff;
        border-color: #63d4ff;
        box-shadow: 0 0 16px rgba(99, 212, 255, .2);
    }

    /* Nút không thể bấm */
    .movie-pagination nav span[aria-disabled="true"]>span {
        color: #526477;
        background: #0a111a;
        border-color: #1b2b3b;
        cursor: not-allowed;
    }

    /* Kích thước biểu tượng mũi tên */
    .movie-pagination svg {
        width: 16px;
        height: 16px;
    }

    /* Điện thoại */
    @media (max-width: 480px) {
        .movie-pagination {
            margin-top: 28px;
            padding-top: 20px;
        }

        .movie-pagination nav>div:last-child {
            gap: 5px;
        }

        .movie-pagination nav a,
        .movie-pagination nav span[aria-current="page"]>span,
        .movie-pagination nav span[aria-disabled="true"]>span {
            min-width: 34px;
            height: 36px;
            padding: 0 8px;
            font-size: 12px;
        }
    }


    /* Máy tính bảng */
    @media (max-width: 1100px) {
        .movie-list-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 24px 16px;
        }
    }

    /* Điện thoại */
    @media (max-width: 650px) {
        .movie-list-hero {
            padding: 22px 18px;
        }

        .movie-filter-panel {
            padding: 12px;
        }

        .movie-list-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 22px 14px;
        }

        .movie-list-grid .movie-card__title {
            font-size: 13px;
        }

        .movie-list-grid .movie-card__body {
            padding-top: 9px;
        }
    }
</style>
</escape>