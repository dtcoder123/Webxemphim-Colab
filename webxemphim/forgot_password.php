<?php
session_start();

// Nếu người dùng đã đăng nhập thì điều hướng về trang chủ
if (!empty($_SESSION['user_logged_in'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/mailer.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    // Kiểm tra định dạng email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Vui lòng nhập địa chỉ email hợp lệ.';
    } else {
        // Tìm user theo email và trạng thái đang hoạt động (status = 1)
        $stmt = $pdo->prepare(
            'SELECT id, email, username
             FROM users
             WHERE email = :email
             AND status = 1
             LIMIT 1'
        );
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Tạo mã OTP ngẫu nhiên 6 chữ số
            $otp = (string) random_int(100000, 999999);

            // Băm mã OTP bằng SHA-256 trước khi lưu vào cơ sở dữ liệu
            $otpHash = hash('sha256', $otp);

            // Xóa các mã OTP cũ chưa sử dụng của user này
            $deleteStmt = $pdo->prepare(
                'DELETE FROM password_resets
                 WHERE user_id = :user_id'
            );
            $deleteStmt->execute([':user_id' => (int) $user['id']]);

            // Lưu bản ghi OTP mới vào bảng password_resets (hiệu lực 5 phút tính theo đồng hồ MySQL)
            $insertStmt = $pdo->prepare(
                'INSERT INTO password_resets (user_id, otp_hash, expires_at)
                 VALUES (:user_id, :otp_hash, DATE_ADD(NOW(), INTERVAL 5 MINUTE))'
            );
            $insertStmt->execute([
                ':user_id'  => (int) $user['id'],
                ':otp_hash' => $otpHash
            ]);

            // Gửi OTP trực tiếp qua Gmail bằng PHPMailer
            $mailError = '';
            $mailSuccess = sendOtpEmail($user['email'], $otp, $mailError);

            if ($mailSuccess) {
                // Lưu session reset (chỉ lưu id và email, TUYỆT ĐỐI không lưu plain OTP)
                $_SESSION['reset_user_id']  = (int) $user['id'];
                $_SESSION['reset_email']    = $user['email'];
                $_SESSION['otp_attempts']   = 0;

                // Chuyển hướng trực tiếp tới trang xác nhận mã OTP
                header('Location: verify_otp.php');
                exit;
            } else {
                $error = 'Gửi email xác thực thất bại. Lỗi: ' . $mailError;
            }
        } else {
            // Thông báo chung để tránh rò rỉ danh sách email hệ thống
            $error = 'Nếu email tồn tại trong hệ thống, mã xác nhận sẽ được gửi đến email đó.';
        }
    }
}

$pageTitle = 'Quên mật khẩu';
include 'includes/header.php';
?>

<main class="container" style="padding: 80px 0;">
    <div class="hud-panel" style="max-width: 480px; margin: 0 auto; padding: 32px;">
        <div class="hud-corner hud-corner--tl"></div>
        <div class="hud-corner hud-corner--br"></div>

        <h1 style="font-size: 28px; margin-bottom: 12px; text-transform: uppercase;">
            Quên mật khẩu
        </h1>

        <p style="color: #7fa8b8; margin-bottom: 24px; font-size: 15px; line-height: 1.5;">
            Nhập email đã đăng ký tài khoản. Hệ thống sẽ gửi một mã OTP 6 chữ số gồm bảo mật vào Gmail của bạn.
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
                Email đăng ký
                <input
                    type="email"
                    name="email"
                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                    placeholder="name@example.com"
                    required
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
                GỬI MÃ XÁC NHẬN
            </button>
        </form>

        <div style="margin-top: 24px; text-align: center;">
            <a href="login.php" style="color: #00f0ff; text-decoration: none; font-size: 14px; letter-spacing: 0.04em;">
                ← Quay lại đăng nhập
            </a>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
