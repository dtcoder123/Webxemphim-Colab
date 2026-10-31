@extends('layouts.app')

@section('title', 'Phim yêu thích')

@section('content')
<main class="favorites-page" style="max-width: 1200px; margin: 40px auto; padding: 0 20px; color: #fff;">
    <h1 style="margin-bottom: 10px;">PHIM YÊU THÍCH</h1>

    <p style="color: #aaa; margin-bottom: 30px;">
        Danh sách phim bạn đã lưu.
    </p>

    @if (session('error'))
        <p style="color: #ff7070; margin-bottom: 20px;">
            {{ session('error') }}
        </p>
    @endif

    @if ($movies->isEmpty())
        <div style="padding: 30px; border: 1px solid #333; border-radius: 8px;">
            <p>Bạn chưa có phim yêu thích nào.</p>
            <a href="{{ route('movies.index') }}"
               style="display: inline-block; margin-top: 15px; color: #61d8ff;">
                Khám phá danh sách phim
            </a>
        </div>
    @else
        <p style="color: #aaa; margin-bottom: 20px;">
            Có {{ $movies->count() }} phim yêu thích.
        </p>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 22px;">
            @foreach ($movies as $movie)
                <article style="background: #151922; border: 1px solid #292f3a; border-radius: 8px; overflow: hidden;">
                    <a href="{{ route('movies.show', $movie->id) }}"
                       style="display: block; color: inherit; text-decoration: none;">
                        @if (!empty($movie->poster))
                            <img
                                src="{{ preg_match('/^https?:\/\//i', $movie->poster) ? $movie->poster : asset('uploads/posters/' . ltrim($movie->poster, '/')) }}"
                                alt="{{ $movie->title }}"
                                loading="lazy"
                                style="display: block; width: 100%; aspect-ratio: 2/3; object-fit: cover;"
                            >
                        @else
                            <div style="aspect-ratio: 2/3; display: grid; place-items: center; background: #252b35; color: #aaa;">
                                Chưa có poster
                            </div>
                        @endif

                        <div style="padding: 12px;">
                            <h2 style="font-size: 17px; margin-bottom: 8px;">
                                {{ $movie->title }}
                            </h2>

                            <p style="color: #aaa; font-size: 13px;">
                                {{ $movie->genre ?: 'Chưa phân loại' }}
                            </p>

                            @if (isset($movie->rating))
                                <p style="margin-top: 8px; color: #ffd166;">
                                    ★ {{ $movie->rating }}
                                </p>
                            @endif
                        </div>
                    </a>

                    <div style="padding: 0 12px 14px;">
                        <form method="POST"
                              action="{{ route('movies.favorite', $movie->id) }}"
                              onsubmit="return confirm('Bạn có muốn xóa phim này khỏi danh sách yêu thích?');">
                            @csrf
                            <button type="submit"
                                    style="width: 100%; padding: 10px; border: 1px solid #ff6262; border-radius: 5px; background: transparent; color: #ff7777; cursor: pointer;">
                                ♥ Bỏ yêu thích
                            </button>
                        </form>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</main>
@endsection