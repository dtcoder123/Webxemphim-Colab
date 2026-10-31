@extends('layouts.app')

@section('title', $movie->title . ' — FILM.SYS')

@section('content')

<script>
    window.movieCatalog = @json($movieCatalog);
</script>

<main>
    <section class="watch-hero container">

        {{-- PLAYER HUD --}}
        <div class="player-column">

            <div class="player-hud hud-frame">
                <div class="hud-corner hud-corner--tl"></div>
                <div class="hud-corner hud-corner--tr"></div>
                <div class="hud-corner hud-corner--bl"></div>
                <div class="hud-corner hud-corner--br"></div>

                <div class="player-hud__topbar">
                    <span class="hud-tag">
                        STREAM_ID // {{ str_pad($id, 4, '0', STR_PAD_LEFT) }}
                    </span>

                    <span class="hud-tag hud-tag--live">
                        <span class="live-dot"></span> ĐANG PHÁT
                    </span>

                    <span class="hud-tag">BUFFER: 100%</span>
                </div>

                <div class="player-hud__screen" id="playerScreen">

                    @if ($isYouTubeVideo && $youtubeEmbedUrl !== '')
                    <iframe
                        id="moviePlayer"
                        class="player-hud__video"
                        src="{{ $youtubeEmbedUrl }}"
                        title="{{ $movie->title }}"
                        allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen>
                    </iframe>

                    @elseif ($isGoogleDriveVideo && $googleDriveId !== '')
                    <iframe
                        id="moviePlayer"
                        class="player-hud__video"
                        src="https://drive.google.com/file/d/{{ $googleDriveId }}/preview"
                        title="{{ $movie->title }}"
                        allow="autoplay; encrypted-media; picture-in-picture"
                        allowfullscreen>
                    </iframe>

                    @else
                    <video
                        id="moviePlayer"
                        class="player-hud__video"
                        controls
                        playsinline
                        preload="metadata"
                        poster="{{ $movie->poster }}"
                        src="{{ $videoUrl }}">
                    </video>
                    @endif

                    <div class="player-hud__scan"></div>

                    <div class="player-hud__crosshair">
                        <span></span><span></span>
                        <span></span><span></span>
                    </div>

                    @if (!$isYouTubeVideo)
                    <button
                        class="player-hud__playbtn"
                        id="playBtn"
                        type="button"
                        aria-label="Phát video">▶</button>
                    @endif
                </div>
            </div>

            {{-- CHỌN SERVER --}}
            <div class="server-select">
                <h3 class="block-heading">
                    <span class="block-heading__index">A</span>
                    CHỌN SERVER PHÁT
                </h3>

                <div class="server-list">
                    @foreach ($servers as $i => $server)
                    <button
                        type="button"
                        class="server-chip {{ $i === 1 ? 'is-active' : '' }} {{ $server['status'] === 'standby' ? 'is-standby' : '' }}">
                        <span class="server-chip__dot"></span>

                        <span class="server-chip__text">
                            <strong>{{ $server['name'] }}</strong>
                            <small>{{ $server['sub'] }}</small>
                        </span>
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- THÔNG TIN PHIM --}}
            <div class="movie-info hud-panel" id="movieInfo">
                <div class="hud-corner hud-corner--tl"></div>
                <div class="hud-corner hud-corner--br"></div>

                <div class="movie-info__head">
                    <h1 class="movie-info__title">{{ $movie->title }}</h1>

                    <div class="hero__energy-bar hero__energy-bar--sm">
                        <span
                            class="hero__energy-fill"
                            data-rating="{{ (float) $movie->rating }}">
                        </span>
                    </div>
                </div>

                <div class="hero__meta">
                    <span class="meta-chip meta-chip--rating">
                        ★ {{ $movie->rating }}
                    </span>
                    <span class="meta-chip">{{ $movie->year }}</span>
                    <span class="meta-chip">{{ $movie->duration }}</span>
                    <span class="meta-chip">{{ $movie->genre }}</span>
                </div>

                <p class="movie-info__desc">{{ $movie->description }}</p>

                <div class="movie-info__crew">
                    <p>
                        <span class="mono-label">ĐẠO DIỄN:</span>
                        {{ $movie->director }}
                    </p>

                    <p>
                        <span class="mono-label">DIỄN VIÊN:</span>
                        {{ implode(' · ', $movie->cast) }}
                    </p>
                </div>

                <div class="hero__actions">
                    <button
                        type="button"
                        id="btnFavorite"
                        class="btn-hud {{ $isFavorited ? 'btn-hud--favorited' : 'btn-hud--primary' }}"
                        data-movie-id="{{ $movie->id }}"
                        data-url="{{ route('movies.favorite', $movie->id) }}"
                        data-csrf="{{ csrf_token() }}">

                        <span class="btn-fav-icon">
                            {{ $isFavorited ? '★' : '☆' }}
                        </span>

                        <span class="btn-fav-text">
                            {{ $isFavorited ? 'ĐÃ THÊM VÀO DANH SÁCH' : 'THÊM VÀO DANH SÁCH' }}
                        </span>
                    </button>

                    <a
                        class="btn-hud btn-hud--ghost"
                        href="{{ $videoUrl }}"
                        download>
                        ⬇ TẢI XUỐNG
                    </a>
                </div>
            </div>

            {{-- BÌNH LUẬN VÀ ĐÁNH GIÁ --}}
            <div
                class="movie-comments-section hud-panel"
                style="margin-top:24px;padding:24px;position:relative;">

                <div class="hud-corner hud-corner--tl"></div>
                <div class="hud-corner hud-corner--br"></div>

                <h3 class="block-heading" style="margin-bottom:16px;">
                    <span class="block-heading__index">C</span>
                    BÌNH LUẬN VÀ ĐÁNH GIÁ ({{ $commentsList->count() }})
                </h3>

                @if (session('error'))
                <div style="margin-bottom:16px;padding:10px 12px;background:rgba(255,77,0,.12);border:1px solid rgba(255,77,0,.35);color:#ffd7c7;">
                    {{ session('error') }}
                </div>
                @endif

                @if (session('success'))
                <div style="margin-bottom:16px;padding:10px 12px;background:rgba(0,240,255,.12);border:1px solid rgba(0,240,255,.35);color:#d7f4fb;">
                    {{ session('success') }}
                </div>
                @endif

                @if ($errors->any())
                <div style="margin-bottom:16px;padding:10px 12px;color:#ffd7c7;">
                    @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                    @endforeach
                </div>
                @endif

                @if (session('user_logged_in') && session('user_id'))
                <form
                    method="POST"
                    action="{{ route('movies.comments.store', $movie->id) }}"
                    style="background:rgba(0,240,255,.03);border:1px solid rgba(0,240,255,.15);padding:16px;margin-bottom:24px;">

                    @csrf

                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;flex-wrap:wrap;">
                        <label style="color:#7fa8b8;font-weight:600;">
                            MỨC ĐỘ ĐÁNH GIÁ:
                        </label>

                        <select
                            name="rating"
                            style="background:#0b0f19;border:1px solid rgba(0,240,255,.3);color:#00f0ff;padding:6px 10px;"
                            required>
                            <option value="5">★★★★★ (5/5 - Tuyệt vời)</option>
                            <option value="4">★★★★☆ (4/5 - Rất tốt)</option>
                            <option value="3">★★★☆☆ (3/5 - Ổn định)</option>
                            <option value="2">★★☆☆☆ (2/5 - Tạm được)</option>
                            <option value="1">★☆☆☆☆ (1/5 - Kém)</option>
                        </select>
                    </div>

                    <div style="margin-bottom:12px;">
                        <textarea
                            name="comment"
                            rows="3"
                            maxlength="3000"
                            placeholder="Nhập nội dung bình luận của bạn về phim này..."
                            style="width:100%;box-sizing:border-box;background:rgba(0,240,255,.05);border:1px solid rgba(0,240,255,.2);color:#d7f4fb;padding:10px;resize:vertical;"
                            required>{{ old('comment') }}</textarea>
                    </div>

                    <button
                        type="submit"
                        class="btn-hud btn-hud--primary"
                        style="font-size:14px;padding:8px 16px;">
                        <span class="btn-hud__icon">⚡</span>
                        GỬI BÌNH LUẬN
                    </button>
                </form>
                @else
                <div style="background:rgba(0,240,255,.03);border:1px solid rgba(0,240,255,.15);padding:14px;margin-bottom:24px;text-align:center;color:#7fa8b8;">
                    Vui lòng
                    <a href="{{ url('/login') }}" style="color:#00f0ff;text-decoration:underline;">
                        ĐĂNG NHẬP
                    </a>
                    để gửi đánh giá và bình luận.
                </div>
                @endif

                <div class="comments-list" style="display:grid;gap:12px;">
                    @forelse ($commentsList as $comment)
                    <div style="background:rgba(0,240,255,.02);border-left:2px solid #00f0ff;padding:12px 14px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:6px;flex-wrap:wrap;">
                            <span style="font-weight:bold;color:#00f0ff;font-size:15px;">
                                {{ $comment->username }}
                            </span>

                            <div style="display:flex;align-items:center;gap:12px;">
                                <span style="color:#f39c12;font-size:13px;">
                                    {{ str_repeat('★', (int) $comment->rating) }}{{ str_repeat('☆', 5 - (int) $comment->rating) }}

                                    <span style="color:#5a7b8c;margin-left:6px;">
                                        {{ $comment->created_at }}
                                    </span>
                                </span>

                                @if (
                                session('user_id') &&
                                (
                                (int) $comment->user_id === (int) session('user_id') ||
                                session('user_role') === 'admin'
                                )
                                )
                                <form
                                    method="POST"
                                    action="{{ route('movies.comments.delete', [$movie->id, $comment->id]) }}"
                                    onsubmit="return confirm('Xác nhận xóa bình luận này?');"
                                    style="margin:0;">

                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        style="background:transparent;border:1px solid rgba(255,77,0,.3);color:#ff4d00;padding:2px 6px;cursor:pointer;">
                                        [ XÓA ]
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>

                        <p style="color:#d7f4fb;font-size:14px;line-height:1.5;margin:0;white-space:pre-line;">{{ $comment->comment }}</p>
                    </div>
                    @empty
                    <p style="color:#5a7b8c;font-style:italic;">
                        Chưa có bình luận nào cho bộ phim này.
                    </p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- PHIM ĐỀ XUẤT --}}
        <aside class="suggested-column">
            <h3 class="block-heading">
                <span class="block-heading__index">B</span>
                DỮ LIỆU ĐỀ XUẤT
            </h3>

            <div class="suggested-list">
                @forelse ($suggested as $item)
                <a
                    href="{{ route('movies.show', $item->id) }}"
                    class="suggested-card"
                    data-id="{{ $item->id }}"
                    data-title="{{ $item->title }}"
                    data-genre="{{ $item->genre }}"
                    data-tagline="{{ $item->tagline }}"
                    data-poster="{{ $item->poster }}"
                    data-rating="{{ $item->rating }}"
                    data-year="{{ $item->year }}">

                    <div class="suggested-card__thumb">
                        <img
                            src="{{ $item->poster }}"
                            alt="{{ $item->title }}"
                            loading="lazy">

                        <span class="suggested-card__play">▶</span>
                    </div>

                    <div class="suggested-card__body">
                        <h4>{{ $item->title }}</h4>

                        <span class="suggested-card__meta">
                            {{ $item->genre }} · {{ $item->year }}
                        </span>


                        <div class="rating-bar rating-bar--sm">
                            <div class="rating-bar__track">
                                <div
                                    class="rating-bar__fill"
                                    style="width: {{ min(100, max(0, (float) $item->rating * 10)) }}%">
                                </div>
                            </div>

                            <span class="rating-bar__value">
                                {{ $item->rating }}
                            </span>
                        </div>


                        <span class="rating-bar__value">
                            {{ $item->rating }}
                        </span>
                    </div>
            </div>
            </a>
            @empty
            <p>Chưa có phim đề xuất.</p>
            @endforelse
            </div>
        </aside>
    </section>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Thanh đánh giá.
        document.querySelectorAll('.hero__energy-fill').forEach(function(bar) {
            const rating = parseFloat(bar.dataset.rating) || 0;
            bar.style.width = Math.min(100, Math.max(0, rating * 10)) + '%';
        });

        // Nút phát video đối với video HTML5.
        const playButton = document.getElementById('playBtn');
        const player = document.getElementById('moviePlayer');

        if (playButton && player instanceof HTMLVideoElement) {
            playButton.addEventListener('click', function() {
                player.play().catch(function() {});
            });
        }

        // Gửi yêu cầu thêm/xóa phim yêu thích đến Laravel.
        const favoriteButton = document.getElementById('btnFavorite');

        if (favoriteButton) {
            favoriteButton.addEventListener('click', async function() {
                const url = favoriteButton.dataset.url;
                const csrf = favoriteButton.dataset.csrf;

                if (!url) return;

                favoriteButton.disabled = true;

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (response.status === 401) {
                        alert(data.message || 'Vui lòng đăng nhập.');
                        window.location.href = '/login';
                        return;
                    }

                    if (!response.ok || !data.success) {
                        alert(data.message || 'Không thể cập nhật yêu thích.');
                        return;
                    }

                    const icon = favoriteButton.querySelector('.btn-fav-icon');
                    const text = favoriteButton.querySelector('.btn-fav-text');

                    favoriteButton.classList.toggle(
                        'btn-hud--favorited',
                        data.favorited
                    );
                    favoriteButton.classList.toggle(
                        'btn-hud--primary',
                        !data.favorited
                    );

                    if (icon) icon.textContent = data.favorited ? '★' : '☆';

                    if (text) {
                        text.textContent = data.favorited ?
                            'ĐÃ THÊM VÀO DANH SÁCH' :
                            'THÊM VÀO DANH SÁCH';
                    }

                    alert(data.message);
                } catch (error) {
                    alert('Lỗi kết nối máy chủ FILM-SYS.');
                } finally {
                    favoriteButton.disabled = false;
                }
            });
        }
    });
</script>

@endsection