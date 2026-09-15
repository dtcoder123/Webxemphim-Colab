<?php
session_start();

// Kiểm tra phiên làm việc: phải có user_id và email đang trong tiến trình reset
if (
    empty($_SESSION['reset_user_id']) ||
    empty($_SESSION['reset_email'])
) {
    header('Location: forgot_password.php');
    exit;
}

require_once __DIR__ . '/includes/db.php';

$error = '';
$userId = (int) $_SESSION['reset_user_id'];
$maxAttempts = 5;

// Khởi tạo bộ đếm số lần nhập sai nếu chưa có
if (!isset($_SESSION['otp_attempts'])) {
    $_SESSION['otp_attempts'] = 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Chặn nếu đã vượt quá số lần nhập sai cho phép
    if ($_SESSION['otp_attempts'] >= $maxAttempts) {
        $error = 'Bạn đã nhập sai mã OTP quá ' . $maxAttempts . ' lần. Vui lòng quay lại để yêu cầu mã xác nhận mới.';
    } else {
        $otp = trim($_POST['otp'] ?? '');

        // Kiểm tra định dạng 6 chữ số
        if (!preg_match('/^[0-9]{6}$/', $otp)) {
            $error = 'Mã xác nhận phải gồm đúng 6 chữ số.';
        } else {
            // Lấy bản ghi OTP mới nhất của user còn thời hạn (expires_at > NOW())
            $stmt = $pdo->prepare(
                'SELECT id, otp_hash, expires_at
                 FROM password_resets
                 WHERE user_id = :user_id
                 AND expires_at > NOW()
                 ORDER BY id DESC
                 LIMIT 1'
            );
            $stmt->execute([':user_id' => $userId]);
            $reset = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$reset) {
                $error = 'Mã xác nhận đã hết hạn hoặc không tồn tại. Vui lòng bấm "Gửi lại mã mới" bên dưới.';
            } else {
                // Băm mã OTP người dùng vừa nhập bằng SHA-256
                $inputOtpHash = hash('sha256', $otp);

                // So sánh hash an toàn bằng hash_equals (chống timing attack)
                if (hash_equals($reset['otp_hash'], $inputOtpHash)) {
                    // Xác thực thành công: cấp quyền đặt lại mật khẩu trong session
                    $_SESSION['otp_verified'] = true;
                    unset($_SESSION['otp_attempts']);

                    // Xóa ngay bản ghi OTP đã sử dụng để không thể dùng lại
                    $deleteStmt = $pdo->prepare(
                        'DELETE FROM password_resets
                         WHERE id = :id'
                    );
                    $deleteStmt->execute([':id' => $reset['id']]);

                    // Chuyển hướng sang trang đặt lại mật khẩu mới
                    header('Location: reset_password.php');
                    exit;
                } else {
                    // OTP không khớp: tăng số lần thất bại
                    $_SESSION['otp_attempts']++;
                    $remaining = $maxAttempts - $_SESSION['otp_attempts'];

                    if ($remaining > 0) {
                        $error = 'Mã xác nhận không chính xác. Bạn còn ' . $remaining . ' lần thử.';
                    } else {
                        $error = 'Bạn đã nhập sai quá số lần quy định. Vui lòng yêu cầu mã xác nhận mới.';
                    }
                }
            }
        }
    }
}

$pageTitle = 'Xác nhận mã OTP';
include 'includes/header.php';
?>

<main class="container" style="padding: 80px 0;">
    <div class="hud-panel" style="max-width: 480px; margin: 0 auto; padding: 32px;">
        <div class="hud-corner hud-corner--tl"></div>
        <div class="hud-corner hud-corner--br"></div>

        <h1 style="font-size: 28px; margin-bottom: 12px; text-transform: uppercase;">
            Xác nhận mã OTP
        </h1>

        <p style="color: #7fa8b8; margin-bottom: 20px; font-size: 15px; line-height: 1.5;">
            Mã xác thực 6 chữ số đã được gửi đến:<br>
            <strong style="color: #00f0ff; word-break: break-all;">
                <?php echo htmlspecialchars($_SESSION['reset_email']); ?>
            </strong>
        </p>

        <?php if ($error): ?>
            <div style="
                margin-bottom: 20px;
                padding: 12px 14px;
                color: #ffd7c7;
                background: rgba(255,77,0,0.12);
                border: 1px solid rgba(255,77,0,0.35);
                font-size: 14px;
                line-height: 1.5;
            ">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="post" style="display: grid; gap: 18px;">
            <label style="display: grid; gap: 8px; color: #7fa8b8; font-size: 14px; font-weight: 500;">
                Mã xác nhận 6 số
                <input
                    type="text"
                    name="otp"
                    maxlength="6"
                    pattern="[0-9]{6}"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    placeholder="------"
                    required
                    autofocus
                    style="
                        background: rgba(0,240,255,0.05);
                        border: 1px solid rgba(0,240,255,0.25);
                        color: #d7f4fb;
                        padding: 14px;
                        font-size: 24px;
                        text-align: center;
                        letter-spacing: 8px;
                        font-weight: bold;
                        outline: none;
                        transition: border-color 0.2s ease, box-shadow 0.2s ease;
                    "
                    onfocus="this.style.borderColor='#00f0ff'; this.style.boxShadow='0 0 12px rgba(0,240,255,0.35)';"
                    onblur="this.style.borderColor='rgba(0,240,255,0.25)'; this.style.boxShadow='none';"
                >
            </label>

            <button
                type="submit"
                class="btn-hud btn-hud--primary"
                style="justify-content: center; width: 100%; font-size: 15px; letter-spacing: 0.08em;"
            >
                XÁC NHẬN MÃ OTP
            </button>
        </form>

        <div style="margin-top: 24px; display: flex; justify-content: space-between; font-size: 14px;">
            <a href="forgot_password.php" style="color: #00f0ff; text-decoration: none;">
                ← Gửi lại mã mới
            </a>
            <a href="login.php" style="color: #7fa8b8; text-decoration: none;">
                Hủy & Đăng nhập
            </a>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
