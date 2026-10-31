
@extends('layouts.app')

@section('title', 'Đặt lại mật khẩu — FILM.SYS')

@section('content')
<main class="container" style="padding:80px 0;">
    <div class="hud-panel" style="max-width:480px;margin:0 auto;padding:32px;">
        <div class="hud-corner hud-corner--tl"></div>
        <div class="hud-corner hud-corner--br"></div>

        <h1 style="font-size:28px;margin-bottom:12px;">
            ĐẶT LẠI MẬT KHẨU
        </h1>

        <p style="color:#7fa8b8;margin-bottom:24px;line-height:1.6;">
            Mã OTP đã được xác thực. Hãy nhập mật khẩu mới cho tài khoản.
        </p>

        @if ($errors->any())
            <div style="margin-bottom:18px;color:#ffd7c7;">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('password.reset.submit') }}"
              style="display:grid;gap:18px;">
            @csrf

            <label style="display:grid;gap:8px;color:#7fa8b8;">
                Mật khẩu mới (ít nhất 6 ký tự)
                <input type="password" name="password"
                       minlength="6" autocomplete="new-password"
                       required
                       style="background:rgba(0,240,255,.05);border:1px solid rgba(0,240,255,.25);color:#d7f4fb;padding:12px 14px;font-size:16px;">
            </label>

            <label style="display:grid;gap:8px;color:#7fa8b8;">
                Xác nhận mật khẩu mới
                <input type="password" name="confirm_password"
                       minlength="6" autocomplete="new-password"
                       required
                       style="background:rgba(0,240,255,.05);border:1px solid rgba(0,240,255,.25);color:#d7f4fb;padding:12px 14px;font-size:16px;">
            </label>

            <button type="submit" class="btn-hud btn-hud--primary"
                    style="justify-content:center;width:100%;">
                CẬP NHẬT MẬT KHẨU
            </button>
        </form>
    </div>
</main>
@endsection
