<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MovieController extends Controller
{


    public function index(Request $request)
    {
        $query = DB::table('movies');

        // Lấy từ khóa tìm kiếm, chỉ khai báo một lần.
        $keyword = trim((string) $request->query('search', ''));

        // Tìm theo tên phim, thể loại, đạo diễn hoặc tagline.
        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', '%' . $keyword . '%')
                    ->orWhere('genre', 'like', '%' . $keyword . '%')
                    ->orWhere('director', 'like', '%' . $keyword . '%')
                    ->orWhere('tagline', 'like', '%' . $keyword . '%');
            });
        }

        // Lọc thể loại nếu người dùng chọn.
        $genre = (string) $request->query('genre', '');

        if ($genre !== '') {
            $query->where('genre', $genre);
        }

        // Sắp xếp.
        $sort = (string) $request->query('sort', 'newest');

        switch ($sort) {
            case 'rating':
                $query->orderByDesc('rating')->orderByDesc('id');
                break;

            case 'year':
                $query->orderByDesc('year')->orderByDesc('id');
                break;

            case 'title':
                $query->orderBy('title');
                break;

            default:
                $query->orderByDesc('id');
                break;
        }

        // Phân trang và giữ lại các tham số tìm kiếm.
        $movies = $query->paginate(12)->withQueryString();

        // Danh sách thể loại.
        $genres = DB::table('movies')
            ->whereNotNull('genre')
            ->where('genre', '<>', '')
            ->distinct()
            ->orderBy('genre')
            ->pluck('genre');

        return view('movies.index', compact(
            'movies',
            'genres',
            'keyword',
            'genre',
            'sort'
        ));
    }


    public function suggest(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        if ($q === '') {
            return response()->json([]);
        }

        // Escape ký tự đặc biệt của LIKE để "%" hoặc "_" không khớp mọi phim.
        $like = '%' . addcslashes($q, '\\%_') . '%';

        $movies = DB::table('movies')
            ->where('status', 1)
            ->where('title', 'like', $like)
            ->orderBy('title')
            ->limit(6)
            ->get(['id', 'title', 'genre', 'year', 'rating', 'duration', 'poster']);

        return response()->json($movies->map(fn ($m) => [
            'id'       => $m->id,
            'title'    => $m->title,
            'genre'    => $m->genre,
            'year'     => $m->year,
            'rating'   => $m->rating,
            'duration' => $m->duration,
            'poster'   => $m->poster,
            'url'      => route('movies.show', $m->id),
        ]));
    }

    public function show($id)
    {
        $movie = DB::table('movies')
            ->where('id', $id)
            ->where('status', 1)
            ->first();

        if (!$movie) {
            $movie = DB::table('movies')
                ->where('status', 1)
                ->orderBy('id')
                ->first();
        }

        abort_if(!$movie, 404, 'Không tìm thấy phim.');

        $id = $movie->id;

        $cast = json_decode($movie->cast ?? '[]', true);
        if (!is_array($cast)) {
            $cast = [];
        }
        $movie->cast = $cast;

        // Ghi lịch sử xem nếu người dùng đã đăng nhập.
        $userId = session('user_id');

        if (session('user_logged_in') && $userId) {
            DB::table('watch_history')->updateOrInsert(
                ['user_id' => $userId, 'movie_id' => $id],
                ['watched_at' => now()]
            );
        }

        $isFavorited = false;

        if ($userId) {
            $isFavorited = DB::table('user_favorites')
                ->where('user_id', $userId)
                ->where('movie_id', $id)
                ->exists();
        }

        // Chuẩn hóa đường dẫn video.
        $videoUrl = trim($movie->video_url ?: $movie->trailer_url ?: '');

        if ($videoUrl === '') {
            $videoUrl = 'https://www.w3schools.com/html/mov_bbb.mp4';
        }

        $googleDriveId = '';

        if (preg_match(
            '/drive\.google\.com\/file\/d\/([A-Za-z0-9_-]+)/i',
            $videoUrl,
            $matches
        )) {
            $googleDriveId = $matches[1];
        } elseif (preg_match(
            '/drive\.google\.com\/uc\?[^\s]*id=([A-Za-z0-9_-]+)/i',
            $videoUrl,
            $matches
        )) {
            $googleDriveId = $matches[1];
        }

        $isGoogleDriveVideo = $googleDriveId !== '';

        if ($isGoogleDriveVideo) {
            $videoUrl = 'https://drive.google.com/file/d/'
                . $googleDriveId . '/preview';
        } elseif (!preg_match('/^(https?:\/\/|\/)/i', $videoUrl)) {
            $videoUrl = asset(
                'uploads/movies/' . ltrim($videoUrl, '/')
            );
        }

        $isYouTubeVideo = preg_match(
            '/(?:youtube\.com|youtu\.be)/i',
            $videoUrl
        ) === 1;

        $youtubeEmbedUrl = '';

        if ($isYouTubeVideo && preg_match(
            '/(?:v=|be\/|embed\/)([A-Za-z0-9_-]{11})/',
            $videoUrl,
            $matches
        )) {
            $youtubeEmbedUrl = 'https://www.youtube.com/embed/'
                . $matches[1]
                . '?autoplay=0&controls=1&rel=0&enablejsapi=1';
        }

        $commentsList = DB::table('movie_comments as c')
            ->join('users as u', 'c.user_id', '=', 'u.id')
            ->where('c.movie_id', $id)
            ->select('c.*', 'u.username')
            ->orderByDesc('c.id')
            ->get();

        $suggested = DB::table('movies')
            ->where('status', 1)
            ->where('id', '!=', $id)
            ->orderByDesc('id')
            ->limit(4)
            ->get();

        $movieCatalog = DB::table('movies')
            ->where('status', 1)
            ->orderByDesc('id')
            ->get()
            ->map(function ($item) {
                $cast = json_decode($item->cast ?? '[]', true);
                $cast = is_array($cast) ? $cast : [];

                return [
                    'id' => (int) $item->id,
                    'title' => $item->title,
                    'genre' => $item->genre,
                    'tagline' => $item->tagline,
                    'poster' => $item->poster,
                    'keywords' => mb_strtolower(
                        $item->title . ' ' .
                            $item->genre . ' ' .
                            $item->tagline . ' ' .
                            implode(' ', $cast)
                    ),
                    'watch_url' => route('movies.show', $item->id),
                ];
            });

        $servers = [
            [
                'name' => 'SERVER ALPHA',
                'sub' => 'Ổn định · Full HD',
                'status' => 'online',
            ],
            [
                'name' => 'SERVER BETA — J.A.R.V.I.S',
                'sub' => 'Tốc độ cao · 4K HDR',
                'status' => 'online',
            ],
            [
                'name' => 'SERVER GAMMA',
                'sub' => 'Dự phòng · HD',
                'status' => 'standby',
            ],
        ];

        return view('movies.show', compact(
            'movie',
            'id',
            'videoUrl',
            'isYouTubeVideo',
            'isGoogleDriveVideo',
            'googleDriveId',
            'youtubeEmbedUrl',
            'commentsList',
            'suggested',
            'movieCatalog',
            'servers',
            'isFavorited'
        ));
    }

    public function storeComment(Request $request, $id)
    {
        if (!session('user_logged_in') || !session('user_id')) {
            return redirect('/login')
                ->with('error', 'Vui lòng đăng nhập để bình luận.');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:3000',
        ], [
            'rating.required' => 'Vui lòng chọn điểm đánh giá.',
            'rating.min' => 'Điểm đánh giá tối thiểu là 1.',
            'rating.max' => 'Điểm đánh giá tối đa là 5.',
            'comment.required' => 'Nội dung bình luận không được để trống.',
        ]);

        $movieExists = DB::table('movies')
            ->where('id', $id)
            ->where('status', 1)
            ->exists();

        abort_unless($movieExists, 404);

        DB::table('movie_comments')->insert([
            'movie_id' => $id,
            'user_id' => session('user_id'),
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
            'created_at' => now(),
        ]);

        return redirect()->route('movies.show', $id)
            ->with('success', 'Đã gửi bình luận và đánh giá thành công.');
    }

    public function deleteComment(Request $request, $id, $commentId)
    {
        if (!session('user_logged_in') || !session('user_id')) {
            return redirect('/login')
                ->with('error', 'Vui lòng đăng nhập để thực hiện thao tác.');
        }

        $comment = DB::table('movie_comments')
            ->where('id', $commentId)
            ->where('movie_id', $id)
            ->first();

        if (!$comment) {
            return redirect()->route('movies.show', $id)
                ->with('error', 'Không tìm thấy bình luận.');
        }

        $isOwner = (int) $comment->user_id === (int) session('user_id');
        $isAdmin = session('user_role') === 'admin';

        if (!$isOwner && !$isAdmin) {
            return redirect()->route('movies.show', $id)
                ->with('error', 'Bạn không có quyền xóa bình luận này.');
        }

        DB::table('movie_comments')
            ->where('id', $commentId)
            ->delete();

        return redirect()->route('movies.show', $id)
            ->with('success', 'Đã xóa bình luận.');
    }

    public function toggleFavorite(Request $request, $id)
    {
        if (!session('user_logged_in') || !session('user_id')) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để lưu phim yêu thích.',
            ], 401);
        }

        $exists = DB::table('movies')
            ->where('id', $id)
            ->where('status', 1)
            ->exists();

        if (!$exists) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy phim.',
            ], 404);
        }

        $userId = session('user_id');

        $favorite = DB::table('user_favorites')
            ->where('user_id', $userId)
            ->where('movie_id', $id)
            ->exists();

        if ($favorite) {
            DB::table('user_favorites')
                ->where('user_id', $userId)
                ->where('movie_id', $id)
                ->delete();

            return response()->json([
                'success' => true,
                'favorited' => false,
                'message' => 'Đã xóa khỏi danh sách yêu thích.',
            ]);
        }

        DB::table('user_favorites')->insert([
            'user_id' => $userId,
            'movie_id' => $id,
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'favorited' => true,
            'message' => 'Đã thêm vào danh sách yêu thích!',
        ]);
    }
    public function favorites()
    {
        // Chỉ người đã đăng nhập mới xem được phim yêu thích.
        if (!session('user_logged_in') || !session('user_id')) {
            return redirect('/login')
                ->with('error', 'Vui lòng đăng nhập để xem phim yêu thích.');
        }

        $userId = session('user_id');

        // Lấy phim yêu thích của người dùng hiện tại.
        // Chỉ hiển thị phim đang hoạt động, mới nhất ở đầu danh sách.
        $movies = DB::table('user_favorites as f')
            ->join('movies as m', 'f.movie_id', '=', 'm.id')
            ->where('f.user_id', $userId)
            ->where('m.status', 1)
            ->select('m.*', 'f.created_at as favorited_at')
            ->orderByDesc('f.created_at')
            ->get();

        return view('favorites.index', compact('movies'));
    }
    public function history()
    {
        if (!session('user_logged_in') || !session('user_id')) {
            return redirect()->route('login')
                ->with('error', 'Vui lòng đăng nhập để xem lịch sử phim.');
        }

        $userId = session('user_id');

        $movies = DB::table('watch_history as h')
            ->join('movies as m', 'h.movie_id', '=', 'm.id')
            ->where('h.user_id', $userId)
            ->where('m.status', 1)
            ->select('m.*', 'h.watched_at')
            ->orderByDesc('h.watched_at')
            ->get();

        return view('history.index', compact('movies'));
    }

    // Xóa một phim khỏi lịch sử của người dùng hiện tại.
    public function deleteHistory($id)
    {
        if (!session('user_logged_in') || !session('user_id')) {
            return redirect()->route('login')
                ->with('error', 'Vui lòng đăng nhập để quản lý lịch sử.');
        }

        DB::table('watch_history')
            ->where('user_id', session('user_id'))
            ->where('movie_id', $id)
            ->delete();

        return redirect()->route('history.index')
            ->with('success', 'Đã xóa phim khỏi lịch sử xem.');
    }
}
