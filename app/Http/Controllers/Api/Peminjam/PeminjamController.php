<?php

namespace App\Http\Controllers\Api\Peminjam;

use App\Http\Controllers\Controller;
use App\Http\Requests\Peminjam\PeminjamRequest;
use App\Http\Resources\Admin\ToolResource;
use App\Http\Resources\Admin\LoanResource;
use App\Services\Peminjam\PeminjamService;
use Illuminate\Support\Facades\Auth;  // <-- TAMBAHKAN INI

class PeminjamController extends Controller
{
    protected PeminjamService $peminjamService;

    public function __construct(PeminjamService $peminjamService)
    {
        $this->peminjamService = $peminjamService;
    }

    /**
     * Daftar alat tersedia
     * GET /api/peminjam/tools
     */
    public function tools()
    {
        $tools = $this->peminjamService->getAllAvailableTools();

        return response()->json([
            'success' => true,
            'message' => 'Daftar alat tersedia',
            'data' => ToolResource::collection($tools)
        ], 200);
    }

    /**
     * Detail alat
     * GET /api/peminjam/tools/{id}
     */
    public function toolDetail($id)
    {
        $tool = $this->peminjamService->getToolDetail($id);

        if (!$tool) {
            return response()->json([
                'success' => false,
                'message' => 'Alat tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail alat',
            'data' => new ToolResource($tool)
        ], 200);
    }

    /**
     * Ajukan peminjaman
     * POST /api/peminjam/submit
     */
    public function submitLoan(PeminjamRequest $request)
    {
        try {
            $loan = $this->peminjamService->submitLoanRequest(
                Auth::id(),  // <-- GANTI auth()->id() dengan Auth::id()
                $request->tool_id,
                $request->tanggal_kembali
            );

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan peminjaman berhasil, menunggu persetujuan petugas',
                'data' => new LoanResource($loan)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Riwayat peminjaman
     * GET /api/peminjam/history
     */
    public function history()
    {
        $loans = $this->peminjamService->getUserLoanHistory(Auth::id());  // <-- GANTI
        $stats = $this->peminjamService->getUserLoanStats(Auth::id());    // <-- GANTI

        return response()->json([
            'success' => true,
            'message' => 'Riwayat peminjaman',
            'data' => [
                'stats' => $stats,
                'loans' => LoanResource::collection($loans)
            ]
        ], 200);
    }

    /**
     * Detail peminjaman user
     * GET /api/peminjam/loans/{id}
     */
    public function loanDetail($id)
    {
        $loan = $this->peminjamService->getLoanById($id, Auth::id());  // <-- GANTI

        if (!$loan) {
            return response()->json([
                'success' => false,
                'message' => 'Peminjaman tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail peminjaman',
            'data' => new LoanResource($loan)
        ], 200);
    }

    /**
     * Dashboard Peminjam
     * GET /api/peminjam/dashboard
     */
    public function dashboard()
    {
        $tools = $this->peminjamService->getAllAvailableTools();
        $loans = $this->peminjamService->getUserLoanHistory(Auth::id());  // <-- GANTI
        $stats = $this->peminjamService->getUserLoanStats(Auth::id());    // <-- GANTI

        return response()->json([
            'success' => true,
            'message' => 'Dashboard peminjam',
            'data' => [
                'stats' => $stats,
                'available_tools' => ToolResource::collection($tools->take(5)),
                'recent_loans' => LoanResource::collection($loans->take(5)),
                'total_available_tools' => $tools->count(),
            ]
        ], 200);
    }
}
