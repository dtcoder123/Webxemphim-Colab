<?php
/**
 * ============================================================================
 * CẤU HÌNH GMAIL SMTP & HÀM GỬI MÃ OTP (PHPMailer)
 * ============================================================================
 * 
 * HƯỚNG DẪN CẤU HÌNH:
 * 1. Mở Xác minh 2 bước (2-Step Verification) trên tài khoản Google:
 *    https://myaccount.google.com/security
 * 2. Tạo Mật khẩu ứng dụng (App Password):
 *    https://myaccount.google.com/apppasswords
 *    - Đặt tên: "Web Xem Phim OTP"
 *    - Copy chuỗi mật khẩu 16 chữ số (dạng: xxxx xxxx xxxx xxxx)
 * 3. Thay thế các hằng số bên dưới:
 *    - Thay 'GMAIL_CUA_TOI' bằng địa chỉ Gmail của bạn (vd: yourname@gmail.com)
 *    - Thay 'GOOGLE_APP_PASSWORD' bằng mật khẩu ứng dụng 16 ký tự vừa tạo
 * ============================================================================
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// --- THIẾT LẬP THÔNG SỐ SMTP GMAIL ---
if (!defined('SMTP_HOST')) {
    define('SMTP_HOST', 'smtp.gmail.com');
}
if (!defined('SMTP_PORT')) {
    define('SMTP_PORT', 587); // Cổng TLS mặc định của Gmail (hoặc 465 nếu dùng SSL)
}
if (!defined('SMTP_USER')) {
    // >>> ĐIỀN EMAIL GMAIL CỦA BẠN VÀO ĐÂY <<<
    define('SMTP_USER', 'thphong3004@gmail.com');
}
if (!defined('SMTP_PASS')) {
    // >>> ĐIỀN MẬT KHẨU ỨNG DỤNG 16 KÝ TỰ VÀO ĐÂY <<<
    define('SMTP_PASS', 'jqpi svua kbgl kptk');
}
if (!defined('SMTP_FROM_NAME')) {
    define('SMTP_FROM_NAME', 'Web Xem Phim');
}

/**
 * Gửi mã OTP xác nhận đặt lại mật khẩu đến địa chỉ email người dùng
 *
 * @param string $toEmail      Email người nhận
 * @param string $otp          Mã OTP 6 chữ số
 * @param string &$errorMessage Biến nhận thông báo lỗi chi tiết nếu gửi thất bại
 * @return bool                True nếu gửi thành công, False nếu thất bại
 */
function sendOtpEmail(string $toEmail, string $otp, string &$errorMessage = ''): bool
{
    // Kiểm tra cấu hình placeholder trước khi gửi
    if (SMTP_USER === 'GMAIL_CUA_TOI' || SMTP_PASS === 'GOOGLE_APP_PASSWORD') {
        $errorMessage = 'Hệ thống chưa được điền thông tin Gmail SMTP. Vui lòng mở file includes/mailer.php và thay thế GMAIL_CUA_TOI và GOOGLE_APP_PASSWORD.';
        return false;
    }

    $mail = new PHPMailer(true);

    try {
        // Cấu hình SMTP Server
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = str_replace(' ', '', SMTP_PASS); // Tự động loại bỏ dấu cách nếu copy từ Google
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';

        // Người gửi & Người nhận
        $mail->setFrom(SMTP_USER, SMTP_FROM_NAME);
        $mail->addAddress($toEmail);
        $mail->addReplyTo(SMTP_USER, SMTP_FROM_NAME);

        // Định dạng nội dung email HTML
        $mail->isHTML(true);
        $mail->Subject = '=?UTF-8?B?' . base64_encode('[' . SMTP_FROM_NAME . '] Mã OTP xác thực đặt lại mật khẩu') . '?=';

        $safeOtp = htmlspecialchars($otp, ENT_QUOTES, 'UTF-8');

        $mail->Body = <<<HTML
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <style>
    body {
      margin: 0;
      padding: 0;
      background-color: #050b14;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
      color: #d7f4fb;
    }
    .wrapper {
      width: 100%;
      background-color: #050b14;
      padding: 40px 10px;
    }
    .panel {
      max-width: 520px;
      margin: 0 auto;
      background: #0a1322;
      border: 1px solid #00f0ff;
      border-radius: 4px;
      padding: 32px 28px;
      box-shadow: 0 0 25px rgba(0, 240, 255, 0.2);
    }
    .header-title {
      font-size: 22px;
      font-weight: bold;
      letter-spacing: 2px;
      color: #00f0ff;
      text-transform: uppercase;
      margin-bottom: 20px;
      border-bottom: 1px solid rgba(0, 240, 255, 0.25);
      padding-bottom: 12px;
    }
    .text {
      font-size: 15px;
      line-height: 1.6;
      color: #a4c7d5;
      margin: 14px 0;
    }
    .otp-box {
      margin: 28px 0;
      padding: 20px;
      background: rgba(0, 240, 255, 0.06);
      border: 1px dashed #00f0ff;
      text-align: center;
      border-radius: 4px;
    }
    .otp-code {
      font-size: 38px;
      font-weight: 800;
      letter-spacing: 12px;
      color: #00f0ff;
      font-family: "Courier New", Courier, monospace;
      margin-left: 12px;
    }
    .badge {
      display: inline-block;
      margin-top: 10px;
      font-size: 13px;
      color: #ffd7c7;
      background: rgba(255, 77, 0, 0.15);
      border: 1px solid rgba(255, 77, 0, 0.35);
      padding: 4px 12px;
      border-radius: 3px;
    }
    .footer-note {
      margin-top: 28px;
      padding-top: 16px;
      border-top: 1px solid rgba(255, 255, 255, 0.08);
      font-size: 12px;
      color: #638292;
      line-height: 1.5;
    }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="panel">
      <div class="header-title">⚡ WEB XEM PHIM // XÁC THỰC BẢO MẬT</div>
      <p class="text">Xin chào,</p>
      <p class="text">Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản đăng ký bằng email này trên <strong>Web Xem Phim</strong>.</p>
      <p class="text">Vui lòng sử dụng mã OTP 6 chữ số bên dưới để tiếp tục:</p>
      
      <div class="otp-box">
        <div class="otp-code">{$safeOtp}</div>
        <div class="badge">⏱ Hiệu lực trong vòng 5 phút</div>
      </div>

      <p class="text"><strong>Lưu ý bảo mật:</strong> Không chia sẻ mã xác thực này với bất kỳ ai để đảm bảo an toàn cho tài khoản của bạn.</p>
      <p class="text">Nếu bạn không thực hiện yêu cầu này, hãy bỏ qua email. Mật khẩu hiện tại của bạn vẫn được giữ nguyên.</p>

      <div class="footer-note">
        Email này được gửi tự động từ hệ thống Web Xem Phim. Vui lòng không phản hồi lại email này.
      </div>
    </div>
  </div>
</body>
</html>
HTML;

        // Plain text fallback
        $mail->AltBody = "Mã OTP xác nhận đặt lại mật khẩu của bạn là: {$otp}\nMã có hiệu lực trong vòng 5 phút. Vui lòng không chia sẻ mã này cho bất kỳ ai.";

        return $mail->send();
    } catch (Exception $e) {
        $errorMessage = $mail->ErrorInfo ?: $e->getMessage();
        return false;
    }
}
