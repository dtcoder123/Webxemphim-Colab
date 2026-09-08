<?php
session_start();

if (!empty($_SESSION['user_logged_in'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/includes/db.php';

$token = trim($_GET['token'] ?? $_POST['token'] ?? '');
$error = '';
$success = '';
$reset = null;

if ($token !== '') {
    $stmt = $pdo->prepare('SELECT id, user_id FROM password_resets WHERE token_hash = :token_hash AND expires_at > NOW() LIMIT 1');
    $stmt->execute([':token_hash' => hash('sha256', $token)]);
    $reset = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $reset) {
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Mật khẩu xác nhận không khớp.';
    } else {
        $update = $pdo->prepare('UPDATE users SET password = :password WHERE id = :user_id');
        $update->execute([
            ':password' => password_hash($password, PASSWORD_DEFAULT),
            ':user_id' => (int)$reset['user_id'],
        ]);
        $pdo->prepare('DELETE FROM password_resets WHERE user_id = :user_id')->execute([':user_id' => (int)$reset['user_id']]);
        $_SESSION['flash_message'] = 'Đổi mật khẩu thành công. Bạn có thể đăng nhập lại.';
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
    <h1 style="font-size: 28px; margin-bottom: 24px; text-transform: uppercase;">Đặt lại mật khẩu</h1>

    <?php if (!$reset): ?>
      <div style="padding: 12px 14px; color: #ffd7c7; background: rgba(255,77,0,0.12); border: 1px solid rgba(255,77,0,0.35);">Liên kết không hợp lệ hoặc đã hết hạn.</div>
    <?php else: ?>
      <?php if ($error): ?><div style="margin-bottom: 18px; padding: 12px 14px; color: #ffd7c7; background: rgba(255,77,0,0.12); border: 1px solid rgba(255,77,0,0.35);"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
      <form method="post" style="display: grid; gap: 18px;">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        <label style="display: grid; gap: 8px; color: #7fa8b8;">Mật khẩu mới
          <input type="password" name="password" required minlength="6" style="background: rgba(0,240,255,0.05); border: 1px solid rgba(0,240,255,0.2); color: #d7f4fb; padding: 12px 14px; font-size: 16px;">
        </label>
        <label style="display: grid; gap: 8px; color: #7fa8b8;">Xác nhận mật khẩu mới
          <input type="password" name="confirm_password" required minlength="6" style="background: rgba(0,240,255,0.05); border: 1px solid rgba(0,240,255,0.2); color: #d7f4fb; padding: 12px 14px; font-size: 16px;">
        </label>
        <button type="submit" class="btn-hud btn-hud--primary" style="justify-content: center; width: 100%;">LƯU MẬT KHẨU MỚI</button>
      </form>
    <?php endif; ?>
  </div>
</main>

<?php include 'includes/footer.php'; ?>