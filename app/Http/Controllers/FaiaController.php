<?php

namespace App\Http\Controllers;

use App\Services\Faia;
use Illuminate\Http\Request;

class FaiaController extends Controller
{
    // Chat với FAIA.
    public function chat(Request $request)
    {
        // Chỉ thành viên đã đăng nhập mới được dùng FAIA.
        if (!session('user_logged_in') || !session('user_id')) {
            return response()->json([
                'message' => 'Vui lòng đăng nhập để trò chuyện với FAIA.',
                'login_url' => route('login'),
            ], 401);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:500',
        ]);

        return response()->json(Faia::reply($validated['message'], (int) session('user_id')));
    }
}
