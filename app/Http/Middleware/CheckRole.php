<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;  // <-- TAMBAHKAN INI

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Cek apakah user sudah login (gunakan Auth::check() bukan auth()->check())
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Silakan login terlebih dahulu.'
            ], 401);
        }

        // Ambil role user dari Spatie
        $userRole = Auth::user()->getRoleNames()->first();

        // Cek apakah role user termasuk dalam role yang diizinkan
        if (!in_array($userRole, $roles)) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Anda tidak memiliki izin untuk mengakses resource ini.'
            ], 403);
        }

        return $next($request);
    }
}
