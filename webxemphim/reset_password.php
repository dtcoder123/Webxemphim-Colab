<?php
session_start();

// Kiểm tra quyền: phải có user_id và đã vượt qua bước xác thực OTP (otp_verified = true)
if (
    empty($_SESSION['reset_user_id']) ||
    empty($_SESSION['otp_verified']) ||
    $_SESSION['otp_verified'] !== true
) {
    header('Location: forgot_password.php');
    exit;
}

require_once __DIR__ . '/includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Kiểm tra độ dài và tính hợp lệ của mật khẩu
    if (strlen($password) < 6) {
        $error = 'Mật khẩu mới phải có ít nhất 6 ký tự.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Mật khẩu xác nhận không khớp. Vui lòng kiểm tra lại.';
    } else {
        $userId = (int) $_SESSION['reset_user_id'];

        // Băm mật khẩu mới bằng thuật toán an toàn chuẩn PHP (BCRYPT qua PASSWORD_DEFAULT)
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Cập nhật mật khẩu vào bảng users
        $updateStmt = $pdo->prepare(
            'UPDATE users
             SET password = :password
             WHERE id = :id'
        );
        $updateStmt->execute([
            ':password' => $passwordHash,
            ':id'       => $userId
        ]);

        // Dọn dẹp sạch toàn bộ bản ghi reset của user trong bảng password_resets
        $deleteStmt = $pdo->prepare(
            'DELETE FROM password_resets
             WHERE user_id = :user_id'
        );
        $deleteStmt->execute([':user_id' => $userId]);

        // Hủy toàn bộ các biến session liên quan đến tiến trình reset
        unset($_SESSION['reset_user_id']);
        unset($_SESSION['reset_email']);
        unset($_SESSION['otp_verified']);
        unset($_SESSION['otp_attempts']);

        // Gán thông báo flash message theo đúng yêu cầu
        $_SESSION['flash_message'] = 'Đặt lại mật khẩu thành công. Vui lòng đăng nhập bằng mật khẩu mới.';

        // Điều hướng trực tiếp về trang đăng nhập login.php
        header('Location: login.php');
        exit;
    }
}

$pageTitle = 'Đặt lại mật khẩu';
include 'includes/header.php';
?>

<main class="container" style="padding: 80px 0;">
    <div class="hud-panel" style="max-width: 480px; margin: 0 auto; padding: 32px;">
        <div class="hud-corner hud-corner--tl"></div>
        <div class="hud-corner hud-corner--br"></div>

        <h1 style="font-size: 28px; margin-bottom: 12px; text-transform: uppercase;">
            Đặt lại mật khẩu
        </h1>

        <p style="color: #7fa8b8; margin-bottom: 24px; font-size: 15px; line-height: 1.5;">
            Mã OTP đã được xác thực thành công. Vui lòng thiết lập mật khẩu mới cho tài khoản của bạn.
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
                Mật khẩu mới (tối thiểu 6 ký tự)
                <input
                    type="password"
                    name="password"
                    placeholder="Nhập mật khẩu mới"
                    required
                    minlength="6"
                    autofocus
                    style="
                        background: rgba(0,240,255,0.05);
                        border: 1px solid rgba(0,240,255,0.25);
                        color: #d7f4fb;
                        padding: 12px 14px;
                        font-size: 16px;
                        outline: none;
                        transition: border-color 0.2s ease, box-shadow 0.2s ease;
                    "
                    onfocus="this.style.borderColor='#00f0ff'; this.style.boxShadow='0 0 10px rgba(0,240,255,0.3)';"
                    onblur="this.style.borderColor='rgba(0,240,255,0.25)'; this.style.boxShadow='none';"
                >
            </label>

            <label style="display: grid; gap: 8px; color: #7fa8b8; font-size: 14px; font-weight: 500;">
                Xác nhận mật khẩu mới
                <input
                    type="password"
                    name="confirm_password"
                    placeholder="Nhập lại mật khẩu mới"
                    required
                    minlength="6"
                    style="
                        background: rgba(0,240,255,0.05);
                        border: 1px solid rgba(0,240,255,0.25);
                        color: #d7f4fb;
                        padding: 12px 14px;
                        font-size: 16px;
                        outline: none;
                        transition: border-color 0.2s ease, box-shadow 0.2s ease;
                    "
                    onfocus="this.style.borderColor='#00f0ff'; this.style.boxShadow='0 0 10px rgba(0,240,255,0.3)';"
                    onblur="this.style.borderColor='rgba(0,240,255,0.25)'; this.style.boxShadow='none';"
                >
            </label>

            <button
                type="submit"
                class="btn-hud btn-hud--primary"
                style="justify-content: center; width: 100%; font-size: 15px; letter-spacing: 0.08em;"
            >
                CẬP NHẬT MẬT KHẨU
            </button>
        </form>

        <div style="margin-top: 24px; text-align: center;">
            <a href="login.php" style="color: #00f0ff; text-decoration: none; font-size: 14px;">
                ← Quay lại đăng nhập
            </a>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
