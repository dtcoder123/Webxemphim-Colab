@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div style="max-width: 1250px; margin: 40px auto; padding: 0 20px; color: #eaf4ff;">

    <div style="display: flex; justify-content: space-between; align-items: center; gap: 20px; flex-wrap: wrap; margin-bottom: 30px;">
        <div>
            <p style="color: #00e5ff; letter-spacing: 3px; font-size: 12px;">
                FILM.SYS // ADMINISTRATION
            </p>
            <h1 style="margin: 8px 0;">TRUNG TÂM QUẢN TRỊ</h1>
            <p style="color: #9baabd;">Xin chào, {{ session('user_name', 'Admin') }}</p>
        </div>

        <a href="{{ route('admin.movies.index') }}"
           style="padding: 12px 18px; border: 1px solid #00d9ff; border-radius: 6px; color: #00e5ff; text-decoration: none;">
            QUẢN LÝ PHIM →
        </a>
    </div>

    @if (session('success'))
        <div style="padding: 14px; margin-bottom: 20px; background: #153b31; border: 1px solid #26795e; border-radius: 8px;">
            {{ session('success') }}
        </div>
    @endif

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 18px; margin-bottom: 35px;">

        @foreach ([
            ['label' => 'TỔNG SỐ PHIM', 'value' => $totalMovies],
            ['label' => 'PHIM ĐANG HIỂN THỊ', 'value' => $activeMovies],
            ['label' => 'PHIM ĐANG ẨN', 'value' => $hiddenMovies],
            ['label' => 'TÀI KHOẢN', 'value' => $totalUsers],
            ['label' => 'BÌNH LUẬN', 'value' => $totalComments],
        ] as $stat)
            <div style="padding: 22px; background: #111a27; border: 1px solid #26384b; border-radius: 10px;">
                <p style="font-size: 12px; color: #9baabd; letter-spacing: 1px;">
                    {{ $stat['label'] }}
                </p>
                <p style="font-size: 32px; font-weight: bold; color: #00e5ff; margin-top: 12px;">
                    {{ number_format($stat['value']) }}
                </p>
            </div>
        @endforeach

    </div>

    <section style="background: #111a27; border: 1px solid #26384b; border-radius: 10px; overflow: hidden;">
        <div style="padding: 20px; border-bottom: 1px solid #26384b; display: flex; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
            <h2 style="font-size: 18px;">PHIM MỚI NHẤT</h2>
            <a href="{{ route('admin.movies.index') }}" style="color: #00e5ff; text-decoration: none;">
                Xem tất cả
            </a>
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; min-width: 600px;">
                <thead>
                    <tr style="text-align: left; color: #9baabd;">
                        <th style="padding: 14px 20px;">ID</th>
                        <th style="padding: 14px 20px;">TÊN PHIM</th>
                        <th style="padding: 14px 20px;">THỂ LOẠI</th>
                        <th style="padding: 14px 20px;">TRẠNG THÁI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentMovies as $movie)
                        <tr style="border-top: 1px solid #26384b;">
                            <td style="padding: 14px 20px;">{{ $movie->id }}</td>
                            <td style="padding: 14px 20px;">{{ $movie->title }}</td>
                            <td style="padding: 14px 20px;">{{ $movie->genre ?: 'Chưa phân loại' }}</td>
                            <td style="padding: 14px 20px;">
                                @if ($movie->status == 1)
                                    <span style="color: #61e6a5;">Đang hiển thị</span>
                                @else
                                    <span style="color: #ff8888;">Đang ẩn</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="padding: 20px; color: #9baabd;">
                                Chưa có dữ liệu phim.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
