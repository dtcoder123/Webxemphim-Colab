<?php
/**
 * api/toggle_favorite.php — Thêm hoặc xóa phim khỏi danh sách yêu thích
 */
session_start();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Phương thức không được hỗ trợ.'
    ]);
    exit;
}

if (empty($_SESSION['user_logged_in']) || empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'logged_in' => false,
        'message' => 'Vui lòng đăng nhập để lưu phim vào danh sách yêu thích.'
    ]);
    exit;
}

require_once __DIR__ . '/../includes/db.php';

$userId = (int)$_SESSION['user_id'];
$movieId = 0;

$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (stripos($contentType, 'application/json') !== false) {
    $rawBody = file_get_contents('php://input');
    $payload = json_decode($rawBody, true);
    $movieId = (int)($payload['movie_id'] ?? 0);
} else {
    $movieId = (int)($_POST['movie_id'] ?? 0);
}

if ($movieId <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Mã phim không hợp lệ.'
    ]);
    exit;
}

// Kiểm tra xem phim có tồn tại trong hệ thống không
$movieCheck = $pdo->prepare('SELECT id, title FROM movies WHERE id = :id AND status = 1 LIMIT 1');
$movieCheck->execute([':id' => $movieId]);
$movie = $movieCheck->fetch(PDO::FETCH_ASSOC);

if (!$movie) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'Không tìm thấy dữ liệu phim trong kho lưu trữ.'
    ]);
    exit;
}

// Kiểm tra bản ghi trong bảng user_favorites
$favCheck = $pdo->prepare('SELECT id FROM user_favorites WHERE user_id = :user_id AND movie_id = :movie_id LIMIT 1');
$favCheck->execute([
    ':user_id' => $userId,
    ':movie_id' => $movieId
]);
$existingFav = $favCheck->fetch(PDO::FETCH_ASSOC);

if ($existingFav) {
    // Nếu đã có thì xóa khỏi yêu thích
    $delStmt = $pdo->prepare('DELETE FROM user_favorites WHERE user_id = :user_id AND movie_id = :movie_id');
    $delStmt->execute([
        ':user_id' => $userId,
        ':movie_id' => $movieId
    ]);

    echo json_encode([
        'success' => true,
        'favorited' => false,
        'message' => 'Đã gỡ bỏ "' . $movie['title'] . '" khỏi danh sách yêu thích.',
        'movie_id' => $movieId
    ]);
} else {
    // Nếu chưa có thì thêm vào yêu thích
    $addStmt = $pdo->prepare('INSERT INTO user_favorites (user_id, movie_id, created_at) VALUES (:user_id, :movie_id, NOW()) ON DUPLICATE KEY UPDATE created_at = NOW()');
    $addStmt->execute([
        ':user_id' => $userId,
        ':movie_id' => $movieId
    ]);

    echo json_encode([
        'success' => true,
        'favorited' => true,
        'message' => 'Đã thêm "' . $movie['title'] . '" vào danh sách yêu thích thành công!',
        'movie_id' => $movieId
    ]);
}
exit;
