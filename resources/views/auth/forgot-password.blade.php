
@extends('layouts.app')

@section('title', 'Quên mật khẩu — FILM.SYS')

@section('content')
<main class="container" style="padding:80px 0;">
    <div class="hud-panel" style="max-width:480px;margin:0 auto;padding:32px;">
        <div class="hud-corner hud-corner--tl"></div>
        <div class="hud-corner hud-corner--br"></div>

        <h1 style="font-size:28px;margin-bottom:12px;text-transform:uppercase;">
            Quên mật khẩu
        </h1>

        <p style="color:#7fa8b8;margin-bottom:24px;line-height:1.6;">
            Nhập email đã đăng ký. Hệ thống sẽ gửi mã OTP 6 chữ số,
            có hiệu lực trong 5 phút.
        </p>

        @foreach (['status', 'error'] as $key)
            @if (session($key))
                <div style="margin-bottom:18px;padding:12px;color:#ffd7c7;background:rgba(0,240,255,.08);border:1px solid rgba(0,240,255,.25);">
                    {{ session($key) }}
                </div>
            @endif
        @endforeach

        @if ($errors->any())
            <div style="margin-bottom:18px;color:#ffd7c7;">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('password.send-otp') }}"
              style="display:grid;gap:18px;">
            @csrf

            <label style="display:grid;gap:8px;color:#7fa8b8;">
                Email đăng ký
                <input type="email" name="email"
                       value="{{ old('email') }}"
                       placeholder="name@example.com" required
                       style="background:rgba(0,240,255,.05);border:1px solid rgba(0,240,255,.25);color:#d7f4fb;padding:12px 14px;font-size:16px;">
            </label>

            <button type="submit" class="btn-hud btn-hud--primary"
                    style="justify-content:center;width:100%;">
                GỬI MÃ XÁC NHẬN
            </button>
        </form>

        <p style="margin-top:24px;text-align:center;">
            <a href="{{ route('login') }}" style="color:#00f0ff;">
                ← Quay lại đăng nhập
            </a>
        </p>
    </div>
</main>
@endsection
