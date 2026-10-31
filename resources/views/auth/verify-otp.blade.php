
@extends('layouts.app')

@section('title', 'Xác nhận OTP — FILM.SYS')

@section('content')
<main class="container" style="padding:80px 0;">
    <div class="hud-panel" style="max-width:480px;margin:0 auto;padding:32px;">
        <div class="hud-corner hud-corner--tl"></div>
        <div class="hud-corner hud-corner--br"></div>

        <h1 style="font-size:28px;margin-bottom:12px;">
            XÁC NHẬN MÃ OTP
        </h1>

        <p style="color:#7fa8b8;margin-bottom:24px;line-height:1.6;">
            Mã xác thực đã được gửi đến:
            <strong style="color:#00f0ff;word-break:break-all;">
                {{ session('reset_email') }}
            </strong>
            <br>Mã có hiệu lực trong 5 phút.
        </p>

        @if (session('error'))
            <div style="margin-bottom:18px;color:#ffd7c7;">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div style="margin-bottom:18px;color:#ffd7c7;">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.otp.submit') }}"
              style="display:grid;gap:18px;">
            @csrf

            <label style="display:grid;gap:8px;color:#7fa8b8;">
                Mã xác nhận 6 số
                <input type="text" name="otp" maxlength="6"
                       pattern="[0-9]{6}" inputmode="numeric"
                       autocomplete="one-time-code" placeholder="------"
                       required autofocus
                       style="background:rgba(0,240,255,.05);border:1px solid rgba(0,240,255,.25);color:#d7f4fb;padding:14px;font-size:24px;text-align:center;letter-spacing:8px;">
            </label>

            <button type="submit" class="btn-hud btn-hud--primary"
                    style="justify-content:center;width:100%;">
                XÁC NHẬN MÃ OTP
            </button>
        </form>

        <p style="margin-top:24px;display:flex;justify-content:space-between;gap:12px;">
            <a href="{{ route('password.forgot') }}" style="color:#00f0ff;">
                ← Gửi lại mã mới
            </a>
            <a href="{{ route('login') }}" style="color:#7fa8b8;">
                Hủy và đăng nhập
            </a>
        </p>
    </div>
</main>
@endsection
