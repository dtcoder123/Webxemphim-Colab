@extends('layouts.app')

@section('title', 'Lịch sử xem phim')

@section('content')
<main style="max-width: 1200px; margin: 40px auto; padding: 0 20px; color: #fff;">
    <h1 style="margin-bottom: 10px;">LỊCH SỬ XEM PHIM</h1>

    <p style="color: #aaa; margin-bottom: 30px;">
        Những bộ phim bạn đã xem gần đây.
    </p>

    @if (session('success'))
        <div style="padding: 12px; margin-bottom: 20px; background: #173b2c; border-radius: 6px;">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div style="padding: 12px; margin-bottom: 20px; background: #492222; border-radius: 6px;">
            {{ session('error') }}
        </div>
    @endif

    @if ($movies->isEmpty())
        <div style="padding: 30px; border: 1px solid #333; border-radius: 8px;">
            <p>Bạn chưa có lịch sử xem phim.</p>
            <a href="{{ route('movies.index') }}"
               style="display: inline-block; margin-top: 15px; color: #61d8ff;">
                Khám phá phim
            </a>
        </div>
    @else
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

                            <p style="color: #aaa; font-size: 13px; margin-top: 8px;">
                                Xem gần nhất:
                                {{ $movie->watched_at
                                    ? \Illuminate\Support\Carbon::parse($movie->watched_at)->format('d/m/Y H:i')
                                    : 'Không rõ thời gian' }}
                            </p>
                        </div>
                    </a>

                    <div style="padding: 0 12px 14px;">
                        <form method="POST"
                              action="{{ route('history.delete', $movie->id) }}"
                              onsubmit="return confirm('Bạn có chắc muốn xóa phim này khỏi lịch sử?');">
                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                    style="width: 100%; padding: 10px; border: 1px solid #ff6262; border-radius: 5px; background: transparent; color: #ff7777; cursor: pointer;">
                                Xóa khỏi lịch sử
                            </button>
                        </form>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</main>
@endsection