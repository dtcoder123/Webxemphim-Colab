
@extends('layouts.app')

@section('title', 'Thêm phim mới')

@section('content')
<div style="max-width: 900px; margin: 35px auto; padding: 0 20px; color: #eaf4ff;">
    <a href="{{ route('admin.movies.index') }}" style="color: #00e5ff; text-decoration: none;">
        ← Quay lại quản lý phim
    </a>

    <h1 style="margin: 22px 0;">THÊM PHIM MỚI</h1>

    @if ($errors->any())
        <div style="padding: 15px; margin-bottom: 20px; background: #492222; border-radius: 6px;">
            <strong>Vui lòng kiểm tra lại thông tin:</strong>
            <ul style="padding-left: 22px; margin-top: 8px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.movies.store') }}"
          style="display: grid; gap: 16px; padding: 24px; background: #111a27; border: 1px solid #26384b; border-radius: 10px;">
        @csrf

        @include('admin.movies.form', ['movie' => null, 'buttonText' => 'THÊM PHIM'])
    </form>
</div>
@endsection
