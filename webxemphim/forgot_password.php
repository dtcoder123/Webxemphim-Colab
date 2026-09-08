<?php
session_start();

if (!empty($_SESSION['user_logged_in'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/includes/db.php';

$error = '';
$success = '';
$resetLink = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Vui lòng nhập email hợp lệ.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email AND status = 1 LIMIT 1');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);
            $expiresAt = date('Y-m-d H:i:s', time() + 3600);

            $pdo->prepare('DELETE FROM password_resets WHERE user_id = :user_id OR expires_at < NOW()')->execute([
                ':user_id' => (int)$user['id'],
            ]);
            $insert = $pdo->prepare('INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (:user_id, :token_hash, :expires_at)');
            $insert->execute([
                ':user_id' => (int)$user['id'],
                ':token_hash' => $tokenHash,
                ':expires_at' => $expiresAt,
            ]);

            $resetLink = 'reset_password.php?token=' . urlencode($token);
            $success = 'Liên kết đặt lại mật khẩu đã được tạo và có hiệu lực trong 60 phút.';
        } else {
            $success = 'Nếu email tồn tại trong hệ thống, bạn sẽ nhận được hướng dẫn đặt lại mật khẩu.';
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
    <h1 style="font-size: 28px; margin-bottom: 24px; text-transform: uppercase;">Quên mật khẩu</h1>

    <?php if ($error): ?><div style="margin-bottom: 18px; padding: 12px 14px; color: #ffd7c7; background: rgba(255,77,0,0.12); border: 1px solid rgba(255,77,0,0.35);"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <?php if ($success): ?><div style="margin-bottom: 18px; padding: 12px 14px; color: #d7f4fb; background: rgba(0,240,255,0.12); border: 1px solid rgba(0,240,255,0.35);"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

    <form method="post" style="display: grid; gap: 18px;">
      <label style="display: grid; gap: 8px; color: #7fa8b8;">Email đăng ký
        <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required style="background: rgba(0,240,255,0.05); border: 1px solid rgba(0,240,255,0.2); color: #d7f4fb; padding: 12px 14px; font-size: 16px;">
      </label>
      <button type="submit" class="btn-hud btn-hud--primary" style="justify-content: center; width: 100%;">TẠO LIÊN KẾT RESET</button>
    </form>

    <?php if ($resetLink): ?>
      <p style="margin: 20px 0 8px; color: #7fa8b8;">Môi trường local chưa cấu hình email. Mở liên kết này để đặt lại:</p>
      <a href="<?php echo htmlspecialchars($resetLink); ?>" style="overflow-wrap: anywhere; color: #00f0ff;"><?php echo htmlspecialchars($resetLink); ?></a>
    <?php endif; ?>
  </div>
</main>

<?php include 'includes/footer.php'; ?>