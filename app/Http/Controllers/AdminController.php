<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Kiểm tra quyền quản trị.
     * Không chỉ ẩn nút ADMIN ở giao diện.
     */
    private function checkAdmin()
    {
        if (
            !session('user_logged_in') ||
            session('user_role') !== 'admin'
        ) {
            abort(403, 'Bạn không có quyền truy cập trang quản trị.');
        }
    }

    /**
     * Trang tổng quan Admin.
     */
    public function dashboard()
    {
        $this->checkAdmin();

        $totalMovies = DB::table('movies')->count();
        $activeMovies = DB::table('movies')
            ->where('status', 1)
            ->count();

        $hiddenMovies = DB::table('movies')
            ->where('status', 0)
            ->count();

        $totalUsers = DB::table('users')->count();
        $totalComments = DB::table('movie_comments')->count();

        $recentMovies = DB::table('movies')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalMovies',
            'activeMovies',
            'hiddenMovies',
            'totalUsers',
            'totalComments',
            'recentMovies'
        ));
    }

    /**
     * Danh sách phim trong trang quản trị.
     */
    public function movies(Request $request)
    {
        $this->checkAdmin();

        $query = DB::table('movies');

        // Tìm phim theo tên.
        if ($request->filled('q')) {
            $query->where(
                'title',
                'like',
                '%' . $request->input('q') . '%'
            );
        }

        // Lọc trạng thái phim nếu người dùng chọn.
        if ($request->input('status') === '1') {
            $query->where('status', 1);
        } elseif ($request->input('status') === '0') {
            $query->where('status', 0);
        }

        $movies = $query
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.movies.index', compact('movies'));
    }
    // Hiển thị form thêm phim.
    public function createMovie()
    {
        $this->checkAdmin();

        return view('admin.movies.create');
    }

    // Lưu phim mới.
    public function storeMovie(Request $request)
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'genre' => 'nullable|string|max:255',
            'rating' => 'nullable|numeric|min:0|max:10',
            'year' => 'nullable|integer|min:1888|max:2100',
            'duration' => 'nullable|integer|min:1|max:1000',
            'poster' => 'nullable|string|max:2048',
            'tagline' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'director' => 'nullable|string|max:255',
            'cast' => 'nullable|string',
            'video_url' => 'nullable|string|max:2048',
            'trailer_url' => 'nullable|string|max:2048',
            'featured' => 'nullable|boolean',
            'status' => 'required|in:0,1',
        ]);

        $cast = collect(explode(',', $validated['cast'] ?? ''))
            ->map(fn($name) => trim($name))
            ->filter()
            ->values()
            ->all();

        DB::table('movies')->insert([
            'title' => $validated['title'],
            'genre' => $validated['genre'] ?? null,
            'rating' => $validated['rating'] ?? 0,
            'year' => $validated['year'] ?? null,
            'duration' => $validated['duration'] ?? null,
            'poster' => $validated['poster'] ?? null,
            'tagline' => $validated['tagline'] ?? null,
            'description' => $validated['description'] ?? null,
            'director' => $validated['director'] ?? null,
            'cast' => json_encode($cast, JSON_UNESCAPED_UNICODE),
            'video_url' => $validated['video_url'] ?? null,
            'trailer_url' => $validated['trailer_url'] ?? null,
            'featured' => $request->boolean('featured') ? 1 : 0,
            'status' => (int) $validated['status'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.movies.index')
            ->with('success', 'Thêm phim thành công.');
    }

    // Hiển thị form sửa phim.
    public function editMovie($id)
    {
        $this->checkAdmin();

        $movie = DB::table('movies')->where('id', $id)->first();

        abort_if(!$movie, 404, 'Không tìm thấy phim.');

        return view('admin.movies.edit', compact('movie'));
    }

    // Cập nhật phim.
    public function updateMovie(Request $request, $id)
    {
        $this->checkAdmin();

        $movieExists = DB::table('movies')->where('id', $id)->exists();

        abort_if(!$movieExists, 404, 'Không tìm thấy phim.');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'genre' => 'nullable|string|max:255',
            'rating' => 'nullable|numeric|min:0|max:10',
            'year' => 'nullable|integer|min:1888|max:2100',
            'duration' => 'nullable|integer|min:1|max:1000',
            'poster' => 'nullable|string',
            'tagline' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'director' => 'nullable|string|max:255',
            'cast' => 'nullable|string',
            'video_url' => 'nullable|string|max:2048',
            'trailer_url' => 'nullable|string|max:2048',
            'featured' => 'nullable|boolean',
            'status' => 'required|in:0,1',
        ]);

        $cast = collect(explode(',', $validated['cast'] ?? ''))
            ->map(fn($name) => trim($name))
            ->filter()
            ->values()
            ->all();

        DB::table('movies')->where('id', $id)->update([
            'title' => $validated['title'],
            'genre' => $validated['genre'] ?? null,
            'rating' => $validated['rating'] ?? 0,
            'year' => $validated['year'] ?? null,

            'duration' => ($validated['duration'] ?? null) !== null
                ? $validated['duration']
                : DB::table('movies')->where('id', $id)->value('duration'),

            'poster' => $request->filled('poster')
                ? $validated['poster']
                : DB::table('movies')->where('id', $id)->value('poster'),
            'tagline' => $validated['tagline'] ?? null,
            'description' => $validated['description'] ?? null,
            'director' => $validated['director'] ?? null,
            'cast' => json_encode($cast, JSON_UNESCAPED_UNICODE),
            'video_url' => $validated['video_url'] ?? null,
            'trailer_url' => $validated['trailer_url'] ?? null,
            'featured' => $request->boolean('featured') ? 1 : 0,
            'status' => (int) $validated['status'],
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.movies.index')
            ->with('success', 'Cập nhật phim thành công.');
    }
    // Ẩn phim, không xóa dữ liệu khỏi database.
    public function hideMovie($id)
    {
        $this->checkAdmin();

        $movie = DB::table('movies')
            ->where('id', $id)
            ->first();

        abort_if(!$movie, 404, 'Không tìm thấy phim.');

        if ((int) $movie->status === 0) {
            return redirect()
                ->route('admin.movies.index')
                ->with('success', 'Phim này đã được ẩn trước đó.');
        }

        DB::table('movies')
            ->where('id', $id)
            ->update([
                'status' => 0,
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('admin.movies.index')
            ->with('success', 'Đã ẩn phim thành công.');
    }
}
