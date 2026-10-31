<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // Hiển thị trang đăng nhập.
    public function showLogin()
    {
        if (session('user_logged_in')) {
            return redirect()->route('home');
        }

        return view('auth.login');
    }

    // Xử lý đăng nhập bằng username hoặc email.
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'Vui lòng nhập tên đăng nhập hoặc email.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        $login = trim($request->input('username'));

        $user = DB::table('users')
            ->where(function ($query) use ($login) {
                $query->where('username', $login)
                    ->orWhere('email', $login);
            })
            ->first();

        if (
            !$user ||
            !Hash::check($request->input('password'), $user->password) ||
            (int) $user->status !== 1
        ) {
            return back()
                ->withInput($request->only('username'))
                ->with('error', 'Tên đăng nhập hoặc mật khẩu không đúng.');
        }

        // Tạo session mới sau khi đăng nhập thành công.
        $request->session()->regenerate();

        $request->session()->put([
            'user_logged_in' => true,
            'user_id' => (int) $user->id,
            'user_name' => $user->username,
            'user_role' => $user->role,
        ]);

        return redirect()->route('home');
    }

    // Hiển thị trang đăng ký.
    public function showRegister()
    {
        if (session('user_logged_in')) {
            return redirect()->route('home');
        }

        return view('auth.register');
    }

    // Xử lý đăng ký.
    public function register(Request $request)
    {
        $validated = $request->validate([
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],
            'username' => [
                'required',
                'string',
                'min:3',
                'max:50',
                Rule::unique('users', 'username'),
            ],
            'password' => 'required|string|min:6',
            'confirm_password' => 'required|same:password',
        ], [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
            'email.unique' => 'Email đã được sử dụng.',
            'username.required' => 'Vui lòng nhập tên đăng nhập.',
            'username.min' => 'Tên đăng nhập phải có ít nhất 3 ký tự.',
            'username.max' => 'Tên đăng nhập không được vượt quá 50 ký tự.',
            'username.unique' => 'Tên đăng nhập đã tồn tại.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'confirm_password.required' => 'Vui lòng xác nhận mật khẩu.',
            'confirm_password.same' => 'Mật khẩu xác nhận không khớp.',
        ]);

        DB::table('users')->insert([
            'email' => $validated['email'],
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'role' => 'member',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('login')
            ->with(
                'flash_message',
                'Đăng ký thành công! Bạn có thể đăng nhập ngay.'
            );
    }

    // Đăng xuất.
    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    // Hiển thị trang quên mật khẩu.
    public function showForgotPassword()
    {
        if (session('user_logged_in')) {
            return redirect()->route('home');
        }

        return view('auth.forgot-password');
    }

    // Gửi OTP qua email.
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
        ], [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
        ]);

        $email = trim($request->input('email'));

        // Xóa trạng thái của lần đặt lại mật khẩu trước.
        $request->session()->forget([
            'reset_user_id',
            'reset_email',
            'otp_verified',
            'otp_attempts',
        ]);

        $user = DB::table('users')
            ->where('email', $email)
            ->where('status', 1)
            ->first();

        // Không tiết lộ email có tồn tại hay không.
        if (!$user) {
            return back()->with(
                'status',
                'Nếu email tồn tại trong hệ thống, mã xác nhận sẽ được gửi đến email đó.'
            );
        }

        $otp = (string) random_int(100000, 999999);

        // Lưu SHA-256, không lưu OTP dạng chữ thường.
        $otpHash = hash('sha256', $otp);

        DB::table('password_resets')
            ->where('user_id', $user->id)
            ->delete();

        $resetId = DB::table('password_resets')->insertGetId([
            'user_id' => $user->id,
            'otp_hash' => $otpHash,
            'expires_at' => now()->addMinutes(5),
            'created_at' => now(),
        ]);

        try {
            $html = '
                <div style="font-family:Arial,sans-serif;max-width:600px;margin:auto;color:#17212b">
                    <h2>WEB XEM PHIM — XÁC NHẬN OTP</h2>
                    <p>Bạn đã yêu cầu đặt lại mật khẩu.</p>
                    <p>Mã xác nhận của bạn là:</p>
                    <div style="font-size:32px;font-weight:bold;letter-spacing:8px;color:#00a9bd">'
                . e($otp) .
                '</div>
                    <p>Mã chỉ có hiệu lực trong vòng 5 phút.</p>
                    <p>Không chia sẻ mã này với bất kỳ ai.</p>
                    <p>Nếu bạn không yêu cầu đặt lại mật khẩu, hãy bỏ qua email này.</p>
                </div>';

            Mail::html($html, function ($message) use ($email) {
                $message->to($email)
                    ->subject('[Web Xem Phim] Mã OTP đặt lại mật khẩu');
            });
        } catch (\Throwable $e) {
            DB::table('password_resets')
                ->where('id', $resetId)
                ->delete();

            report($e);

            return back()->with(
                'error',
                'Không gửi được email xác thực. Vui lòng kiểm tra cấu hình SMTP rồi thử lại.'
            );
        }

        $request->session()->put([
            'reset_user_id' => (int) $user->id,
            'reset_email' => $user->email,
            'otp_attempts' => 0,
        ]);

        return redirect()->route('password.otp');
    }

    // Kiểm tra mã OTP.
    public function verifyOtp(Request $request)
    {
        if (
            !session('reset_user_id') ||
            !session('reset_email')
        ) {
            return redirect()->route('password.forgot');
        }

        if (session('otp_verified') === true) {
            return redirect()->route('password.reset');
        }

        $attempts = (int) session('otp_attempts', 0);

        if ($attempts >= 5) {
            return back()->with(
                'error',
                'Bạn đã nhập sai OTP quá 5 lần. Hãy yêu cầu mã mới.'
            );
        }

        $request->validate([
            'otp' => ['required', 'regex:/^[0-9]{6}$/'],
        ], [
            'otp.required' => 'Vui lòng nhập mã OTP.',
            'otp.regex' => 'Mã OTP phải gồm đúng 6 chữ số.',
        ]);

        $reset = DB::table('password_resets')
            ->where('user_id', session('reset_user_id'))
            ->where('expires_at', '>', now())
            ->orderByDesc('id')
            ->first();

        if (!$reset) {
            return back()->with(
                'error',
                'Mã OTP đã hết hạn hoặc không tồn tại. Hãy yêu cầu mã mới.'
            );
        }

        $inputHash = hash('sha256', $request->input('otp'));

        if (!hash_equals($reset->otp_hash, $inputHash)) {
            $attempts++;
            $request->session()->put('otp_attempts', $attempts);

            $remaining = 5 - $attempts;

            return back()->with(
                'error',
                $remaining > 0
                    ? "Mã OTP không chính xác. Bạn còn {$remaining} lần thử."
                    : 'Bạn đã nhập sai quá số lần quy định. Hãy yêu cầu mã mới.'
            );
        }

        // OTP chỉ được sử dụng một lần.
        DB::table('password_resets')
            ->where('id', $reset->id)
            ->delete();

        $request->session()->put('otp_verified', true);
        $request->session()->forget('otp_attempts');

        return redirect()->route('password.reset');
    }

    // Đặt lại mật khẩu sau khi xác thực OTP.
    public function showResetPassword()
    {
        if (
            !session('reset_user_id') ||
            session('otp_verified') !== true
        ) {
            return redirect()->route('password.forgot');
        }

        return view('auth.reset-password');
    }

    public function resetPassword(Request $request)
    {
        if (
            !session('reset_user_id') ||
            session('otp_verified') !== true
        ) {
            return redirect()->route('password.forgot');
        }

        $validated = $request->validate([
            'password' => 'required|string|min:6',
            'confirm_password' => 'required|same:password',
        ], [
            'password.min' => 'Mật khẩu mới phải có ít nhất 6 ký tự.',
            'confirm_password.same' => 'Mật khẩu xác nhận không khớp.',
        ]);

        $userId = (int) session('reset_user_id');

        $updated = DB::table('users')
            ->where('id', $userId)
            ->where('status', 1)
            ->update([
                'password' => Hash::make($validated['password']),
                'updated_at' => now(),
            ]);

        if (!$updated) {
            $request->session()->forget([
                'reset_user_id',
                'reset_email',
                'otp_verified',
                'otp_attempts',
            ]);

            return redirect()->route('password.forgot')
                ->with('error', 'Không thể cập nhật mật khẩu. Vui lòng thử lại.');
        }

        DB::table('password_resets')
            ->where('user_id', $userId)
            ->delete();

        $request->session()->forget([
            'reset_user_id',
            'reset_email',
            'otp_verified',
            'otp_attempts',
        ]);

        return redirect()->route('login')
            ->with(
                'flash_message',
                'Đặt lại mật khẩu thành công. Vui lòng đăng nhập bằng mật khẩu mới.'
            );
    }
}
