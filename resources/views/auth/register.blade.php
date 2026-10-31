@extends('layouts.app')

@section('title', 'Đăng ký — FILM.SYS')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/auth-drift-wall.css') }}">
@endpush

@section('content')
<main class="container auth-page">
    <div class="auth-drift-wall" data-auth-drift-wall aria-hidden="true"></div>

    <div class="hud-panel auth-form-panel"
         style="max-width:480px;margin:0 auto;padding:32px;">

        <div class="hud-corner hud-corner--tl"></div>
        <div class="hud-corner hud-corner--br"></div>

        <h1 style="font-size:28px;margin-bottom:24px;text-transform:uppercase;">
            Đăng ký
        </h1>

        @if ($errors->any())
            <div style="margin-bottom:18px;padding:12px 14px;background:rgba(255,77,0,.12);border:1px solid rgba(255,77,0,.35);color:#ffd7c7;">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('register.submit') }}"
              style="display:grid;gap:18px;">
            @csrf

            <label style="display:grid;gap:8px;color:#7fa8b8;">
                Email
                <input type="email"
                       name="email"
                       value="{{ old('email') }}"
                       autocomplete="email"
                       maxlength="255"
                       required
                       style="background:rgba(0,240,255,.05);border:1px solid rgba(0,240,255,.2);color:#d7f4fb;padding:12px 14px;font-size:16px;">
            </label>

            <label style="display:grid;gap:8px;color:#7fa8b8;">
                Tên đăng nhập
                <input type="text"
                       name="username"
                       value="{{ old('username') }}"
                       minlength="3"
                       maxlength="50"
                       autocomplete="username"
                       required
                       style="background:rgba(0,240,255,.05);border:1px solid rgba(0,240,255,.2);color:#d7f4fb;padding:12px 14px;font-size:16px;">
            </label>

            <label style="display:grid;gap:8px;color:#7fa8b8;">
                Mật khẩu
                <input type="password"
                       name="password"
                       minlength="6"
                       autocomplete="new-password"
                       required
                       style="background:rgba(0,240,255,.05);border:1px solid rgba(0,240,255,.2);color:#d7f4fb;padding:12px 14px;font-size:16px;">
            </label>

            <label style="display:grid;gap:8px;color:#7fa8b8;">
                Xác nhận mật khẩu
                <input type="password"
                       name="confirm_password"
                       minlength="6"
                       autocomplete="new-password"
                       required
                       style="background:rgba(0,240,255,.05);border:1px solid rgba(0,240,255,.2);color:#d7f4fb;padding:12px 14px;font-size:16px;">
            </label>

            <button type="submit"
                    class="btn-hud btn-hud--primary"
                    style="justify-content:center;width:100%;">
                <span class="btn-hud__icon">▶</span> TẠO TÀI KHOẢN
            </button>

            <p style="text-align:center;color:#7fa8b8;">
                Đã có tài khoản?
                <a href="{{ route('login') }}" style="color:#00f0ff;">
                    ĐĂNG NHẬP
                </a>
            </p>
        </form>
    </div>
</main>
@endsection
