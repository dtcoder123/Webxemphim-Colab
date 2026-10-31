<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/movies', [MovieController::class, 'index'])
    ->name('movies.index');

// Gợi ý phim khi gõ ô tìm kiếm (JSON).
Route::get('/movies/suggest', [MovieController::class, 'suggest'])
    ->name('movies.suggest');

Route::get('/movies/{id}', [MovieController::class, 'show'])
    ->whereNumber('id')
    ->name('movies.show');

// Gửi bình luận và đánh giá.
Route::post('/movies/{id}/comments', [
    MovieController::class, 'storeComment'
])->whereNumber('id')->name('movies.comments.store');

// Xóa bình luận.
Route::delete('/movies/{id}/comments/{commentId}', [
    MovieController::class, 'deleteComment'
])->whereNumber('id')->whereNumber('commentId')
  ->name('movies.comments.delete');

// Thêm hoặc xóa phim yêu thích.
Route::post('/movies/{id}/favorite', [
    MovieController::class, 'toggleFavorite'
])->whereNumber('id')->name('movies.favorite');

Route::get('/favorites', [
    MovieController::class, 'favorites'
])->name('favorites.index');

// lịch sử
Route::get('/history', [
    MovieController::class, 'history'
])->name('history.index');

// Xóa một phim khỏi lịch sử xem.
Route::delete('/history/{id}', [
    MovieController::class, 'deleteHistory'
])->whereNumber('id')->name('history.delete');

// Đăng nhập.
Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login.submit');

// Đăng ký.
Route::get('/register', [AuthController::class, 'showRegister'])
    ->name('register');

Route::post('/register', [AuthController::class, 'register'])
    ->name('register.submit');

// Đăng xuất.
Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout');


// Quên mật khẩu.
Route::get('/forgot-password', [
    AuthController::class, 'showForgotPassword'
])->name('password.forgot');

Route::post('/forgot-password', [
    AuthController::class, 'sendOtp'
])->middleware('throttle:5,1')->name('password.send-otp');

// Xác nhận OTP.
Route::get('/verify-otp', function () {
    if (!session('reset_user_id') || !session('reset_email')) {
        return redirect()->route('password.forgot');
    }

    return view('auth.verify-otp');
})->name('password.otp');

Route::post('/verify-otp', [
    AuthController::class, 'verifyOtp'
])->name('password.otp.submit');

// Đặt lại mật khẩu.
Route::get('/reset-password', [
    AuthController::class, 'showResetPassword'
])->name('password.reset');

Route::post('/reset-password', [
    AuthController::class, 'resetPassword'
])->name('password.reset.submit');

// Khu vực quản trị Admin.
Route::get('/admin', [
    AdminController::class, 'dashboard'
])->name('admin.dashboard');

Route::get('/admin/movies', [
    AdminController::class, 'movies'
])->name('admin.movies.index');

// Thêm phim.
Route::get('/admin/movies/create', [
    AdminController::class, 'createMovie'
])->name('admin.movies.create');

Route::post('/admin/movies', [
    AdminController::class, 'storeMovie'
])->name('admin.movies.store');

// Sửa phim.
Route::get('/admin/movies/{id}/edit', [
    AdminController::class, 'editMovie'
])->whereNumber('id')->name('admin.movies.edit');

Route::put('/admin/movies/{id}', [
    AdminController::class, 'updateMovie'
])->whereNumber('id')->name('admin.movies.update');

// Ẩn phim trong trang quản trị.
Route::patch('/admin/movies/{id}/hide', [
    AdminController::class, 'hideMovie'
])->whereNumber('id')->name('admin.movies.hide');