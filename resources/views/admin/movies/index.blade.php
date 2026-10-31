@extends('layouts.app')

@section('title', 'Quản lý phim')

@section('content')
<div style="max-width: 1250px; margin: 40px auto; padding: 0 20px; color: #eaf4ff;">

    <div style="margin-bottom: 25px;">
        <a href="{{ route('admin.dashboard') }}"
            style="color: #00e5ff; text-decoration: none;">
            ← Về tổng quan
        </a>

        <h1 style="margin-top: 18px;">QUẢN LÝ PHIM</h1>

        <p style="color: #9baabd; margin-top: 8px;">
            Tìm kiếm và theo dõi danh sách phim trong hệ thống.
        </p>

        {{-- Nút thêm phim --}}
        <a href="{{ route('admin.movies.create') }}"
            style="display: inline-block; margin-top: 15px; padding: 12px 18px; background: #06394a; border: 1px solid #00d9ff; color: #00e5ff; border-radius: 6px; text-decoration: none;">
            + THÊM PHIM
        </a>
    </div>

    {{-- Thông báo thành công --}}
    @if (session('success'))
    <div style="padding: 14px; margin: 15px 0; background: #153b31; border: 1px solid #26795e; border-radius: 6px; color: #b9ffda;">
        {{ session('success') }}
    </div>
    @endif

    {{-- Thông báo lỗi --}}
    @if ($errors->any())
    <div style="padding: 14px; margin: 15px 0; background: #492222; border: 1px solid #7a3535; border-radius: 6px; color: #ffd0d0;">
        Có lỗi xảy ra. Vui lòng kiểm tra lại thông tin phim.
    </div>
    @endif

    {{-- Tìm kiếm và lọc phim --}}
    <form method="GET" action="{{ route('admin.movies.index') }}"
        style="display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 25px;">

        <input type="text"
            name="q"
            value="{{ request('q') }}"
            placeholder="Nhập tên phim..."
            style="flex: 1; min-width: 200px; padding: 12px; color: white; background: #111a27; border: 1px solid #34465a; border-radius: 6px;">

        <select name="status"
            style="padding: 12px; color: white; background: #111a27; border: 1px solid #34465a; border-radius: 6px;">
            <option value="">Tất cả trạng thái</option>
            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>
                Đang hiển thị
            </option>
            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>
                Đang ẩn
            </option>
        </select>

        <button type="submit"
            style="padding: 12px 20px; background: #06394a; border: 1px solid #00d9ff; color: #00e5ff; border-radius: 6px; cursor: pointer;">
            TÌM KIẾM
        </button>
    </form>

    {{-- Bảng danh sách phim --}}
    <div style="background: #111a27; border: 1px solid #26384b; border-radius: 10px; overflow: hidden;">
        <div style="overflow-x: auto;">
            <table style="width: 100%; min-width: 850px; border-collapse: collapse;">
                <thead>
                    <tr style="text-align: left; color: #9baabd;">
                        <th style="padding: 15px;">ID</th>
                        <th style="padding: 15px;">POSTER</th>
                        <th style="padding: 15px;">TÊN PHIM</th>
                        <th style="padding: 15px;">THỂ LOẠI</th>
                        <th style="padding: 15px;">NĂM</th>
                        <th style="padding: 15px;">TRẠNG THÁI</th>
                        <th style="padding: 15px;">THAO TÁC</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($movies as $movie)
                    <tr style="border-top: 1px solid #26384b;">

                        <td style="padding: 12px 15px;">
                            {{ $movie->id }}
                        </td>


                        <td style="padding: 12px 15px;">
                            @if ($movie->poster)
                            @php
                            $poster = trim($movie->poster);

                            if (
                            preg_match('/^data:image\//i', $poster) ||
                            preg_match('/^https?:\/\//i', $poster)
                            ) {
                            $posterUrl = $poster;
                            } else {
                            $poster = ltrim($poster, '/');

                            if (str_starts_with($poster, 'uploads/')) {
                            $posterUrl = asset($poster);
                            } else {
                            $posterUrl = asset('uploads/posters/' . $poster);
                            }
                            }
                            @endphp

                            <img
                                src="{{ $posterUrl }}"
                                alt="{{ $movie->title }}"
                                style="width: 48px; height: 68px; object-fit: cover; border-radius: 4px;"
                                onerror="this.style.display='none';">
                            @else
                            <span style="color: #9baabd;">
                                Không có ảnh
                            </span>
                            @endif
                        </td>


                        <td style="padding: 12px 15px;">
                            {{ $movie->title }}
                        </td>

                        <td style="padding: 12px 15px;">
                            {{ $movie->genre ?: '—' }}
                        </td>

                        <td style="padding: 12px 15px;">
                            {{ $movie->year ?: '—' }}
                        </td>

                        <td style="padding: 12px 15px;">
                            @if ($movie->status == 1)
                            <span style="color: #61e6a5;">
                                Đang hiển thị
                            </span>
                            @else
                            <span style="color: #ff8888;">
                                Đang ẩn
                            </span>
                            @endif
                        </td>

                        {{-- Nút sửa phim --}}
                        <td style="padding: 12px 15px;">
                            <a href="{{ route('admin.movies.edit', $movie->id) }}"
                                style="display: inline-block; padding: 8px 12px; border: 1px solid #00d9ff; border-radius: 5px; color: #00e5ff; text-decoration: none; white-space: nowrap;">
                                SỬA PHIM
                            </a>
                            @if ($movie->status == 1)
                            <form
                                method="POST"
                                action="{{ route('admin.movies.hide', $movie->id) }}"
                                onsubmit="return confirm('Bạn có chắc muốn ẩn phim này không?');"
                                style="display: inline-block; margin-left: 6px;">
                                @csrf
                                @method('PATCH')

                                <button
                                    type="submit"
                                    style="padding: 8px 12px; border: 1px solid #ff8888; border-radius: 5px; color: #ff8888; background: transparent; cursor: pointer;">
                                    ẨN PHIM
                                </button>
                            </form>
                            @endif
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="7"
                            style="padding: 24px; color: #9baabd; text-align: center;">
                            Không tìm thấy phim phù hợp.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Phân trang --}}
        <div style="padding: 18px;">
            {{ $movies->links() }}
        </div>
    </div>

</div>
@endsection