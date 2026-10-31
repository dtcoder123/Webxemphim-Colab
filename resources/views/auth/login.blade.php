@extends('layouts.app')

@section('title', 'Đăng nhập — FILM.SYS')

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
            Đăng nhập
        </h1>

        @if (session('flash_message'))
            <div style="margin-bottom:18px;padding:12px 14px;background:rgba(0,240,255,.12);border:1px solid rgba(0,240,255,.35);color:#d7f4fb;">
                {{ session('flash_message') }}
            </div>
        @endif

        @if (session('error'))
            <div style="margin-bottom:18px;padding:12px 14px;background:rgba(255,77,0,.12);border:1px solid rgba(255,77,0,.35);color:#ffd7c7;">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div style="margin-bottom:18px;color:#ffd7c7;">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login.submit') }}"
              style="display:grid;gap:18px;">
            @csrf

            <label style="display:grid;gap:8px;color:#7fa8b8;">
                Tên đăng nhập / Email
                <input type="text"
                       name="username"
                       value="{{ old('username') }}"
                       autocomplete="username"
                       required
                       style="background:rgba(0,240,255,.05);border:1px solid rgba(0,240,255,.2);color:#d7f4fb;padding:12px 14px;font-size:16px;">
            </label>

            <label style="display:grid;gap:8px;color:#7fa8b8;">
                Mật khẩu
                <input type="password"
                       name="password"
                       autocomplete="current-password"
                       required
                       style="background:rgba(0,240,255,.05);border:1px solid rgba(0,240,255,.2);color:#d7f4fb;padding:12px 14px;font-size:16px;">
            </label>

            <button type="submit"
                    class="btn-hud btn-hud--primary"
                    style="justify-content:center;width:100%;">
                <span class="btn-hud__icon">▶</span> XÁC NHẬN
            </button>

            <a href="{{ route('password.forgot') }}"
               style="text-align:center;color:#7fa8b8;text-decoration:none;">
                QUÊN MẬT KHẨU?
            </a>

            <p style="text-align:center;color:#7fa8b8;">
                Chưa có tài khoản?
                <a href="{{ route('register') }}" style="color:#00f0ff;">
                    ĐĂNG KÝ
                </a>
            </p>
        </form>
    </div>
</main>
@endsection
