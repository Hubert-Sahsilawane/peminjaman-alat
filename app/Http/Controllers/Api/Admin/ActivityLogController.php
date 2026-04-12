<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        // Mengambil log terbaru beserta data user yang melakukan aksi
        $query = ActivityLog::with('user')->latest();

        // Jika ada request limit (untuk dashboard), ambil beberapa saja. Jika tidak, gunakan pagination (untuk halaman utama log)
        if ($request->has('limit')) {
            $logs = $query->take($request->limit)->get();
        } else {
            $logs = $query->paginate(15);
        }

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil data log aktivitas',
            'data' => $logs
        ], 200);
    }
}
