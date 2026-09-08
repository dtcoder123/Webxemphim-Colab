<?php
session_start();

if (empty($_SESSION['user_logged_in'])) {
    $_SESSION['flash_message'] = 'Vui lòng đăng nhập để xem danh sách yêu thích.';
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/includes/db.php';

$userId = (int)$_SESSION['user_id'];

$favStmt = $pdo->prepare('
    SELECT m.*, f.created_at AS favorited_at
    FROM user_favorites f
    INNER JOIN movies m ON m.id = f.movie_id
    WHERE f.user_id = :user_id AND m.status = 1
    ORDER BY f.created_at DESC
');
$favStmt->execute([':user_id' => $userId]);
$favorites = $favStmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Danh sách yêu thích';
include 'includes/header.php';
?>

<main class="container history-page favorites-page">
  <div class="section-heading">
    <h1><span class="section-heading__index">★</span> DANH SÁCH YÊU THÍCH</h1>
    <span class="section-heading__count" id="favCountText"><?php echo count($favorites); ?> PHIM ĐÃ LƯU</span>
  </div>

  <div id="favEmptyState" class="history-empty hud-panel" style="<?php echo empty($favorites) ? '' : 'display: none;'; ?>">
    <h2>Chưa có phim trong danh sách yêu thích</h2>
    <p>Bạn có thể nhấn "☆ THÊM VÀO DANH SÁCH" khi xem phim để lưu lại các tác phẩm yêu thích của mình.</p>
    <a href="index.php#grid" class="btn-hud btn-hud--primary">KHÁM PHÁ KHO PHIM</a>
  </div>

  <?php if (!empty($favorites)): ?>
    <div class="history-grid" id="favoritesGrid">
      <?php foreach ($favorites as $movie): ?>
        <div class="movie-card favorite-card-item" id="fav-card-<?php echo (int)$movie['id']; ?>" style="position: relative;">
          <div class="movie-card__frame">
            <a href="watch.php?id=<?php echo (int)$movie['id']; ?>" style="display: block; width: 100%; height: 100%;">
              <img src="<?php echo htmlspecialchars($movie['poster']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" class="movie-card__img" loading="lazy">
              <div class="movie-card__overlay"><span class="movie-card__play">▶</span></div>
            </a>
            <button type="button" class="favorite-remove-btn" data-movie-id="<?php echo (int)$movie['id']; ?>" data-title="<?php echo htmlspecialchars($movie['title'], ENT_QUOTES); ?>" title="Xóa khỏi danh sách yêu thích" aria-label="Xóa khỏi yêu thích">✕</button>
          </div>
          <div class="movie-card__body">
            <h2 class="movie-card__title">
              <a href="watch.php?id=<?php echo (int)$movie['id']; ?>" style="color: inherit; text-decoration: none;">
                <?php echo htmlspecialchars($movie['title']); ?>
              </a>
            </h2>
            <div class="movie-card__meta">
              <span><?php echo htmlspecialchars($movie['genre']); ?></span>
              <span style="color: #00f0ff; margin-left: auto; font-weight: 700;">★ <?php echo $movie['rating']; ?></span>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const favoritesGrid = document.getElementById('favoritesGrid');
  const favEmptyState = document.getElementById('favEmptyState');
  const favCountText = document.getElementById('favCountText');

  const showToast = (message, isWarning = false) => {
    let container = document.querySelector('.hud-toast-container');
    if (!container) {
      container = document.createElement('div');
      container.className = 'hud-toast-container';
      document.body.appendChild(container);
    }
    const toast = document.createElement('div');
    toast.className = 'hud-toast' + (isWarning ? ' hud-toast--warning' : '');
    toast.innerHTML = `
      <span style="font-size: 16px; color: ${isWarning ? '#ff4d00' : '#00f0ff'};">✦</span>
      <span>${message}</span>
    `;
    container.appendChild(toast);

    setTimeout(() => {
      toast.classList.add('is-hiding');
      setTimeout(() => toast.remove(), 300);
    }, 2500);
  };

  document.querySelectorAll('.favorite-remove-btn').forEach((btn) => {
    btn.addEventListener('click', async (e) => {
      e.preventDefault();
      e.stopPropagation();

      const movieId = btn.getAttribute('data-movie-id');
      const movieTitle = btn.getAttribute('data-title') || 'Phim';
      if (!movieId) return;

      btn.disabled = true;
      try {
        const res = await fetch('api/toggle_favorite.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ movie_id: parseInt(movieId, 10) })
        });

        const data = await res.json();
        if (data.success && !data.favorited) {
          const card = document.getElementById(`fav-card-${movieId}`);
          if (card) {
            card.style.transition = 'all 0.35s ease';
            card.style.opacity = '0';
            card.style.transform = 'scale(0.85)';
            setTimeout(() => {
              card.remove();
              const remainingCards = document.querySelectorAll('.favorite-card-item');
              const count = remainingCards.length;
              if (favCountText) {
                favCountText.textContent = `${count} PHIM ĐÃ LƯU`;
              }
              if (count === 0) {
                if (favEmptyState) favEmptyState.style.display = 'grid';
                if (favoritesGrid) favoritesGrid.style.display = 'none';
              }
            }, 350);
          }
          showToast(data.message || `Đã gỡ "${movieTitle}" khỏi danh sách yêu thích.`, true);
        } else {
          showToast(data.message || 'Không thể xóa phim lúc này.', true);
          btn.disabled = false;
        }
      } catch (err) {
        showToast('Lỗi kết nối máy chủ STARK-SYS.', true);
        btn.disabled = false;
      }
    });
  });
});
</script>

<?php include 'includes/footer.php'; ?>
