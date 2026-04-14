<?php

namespace App\Http\Controllers\Api\Petugas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\Petugas\PetugasResource;
use App\Http\Resources\Admin\LoanResource;
use App\Services\Petugas\PetugasService;

class PetugasController extends Controller
{
    protected PetugasService $petugasService;

    public function __construct(PetugasService $petugasService)
    {
        $this->petugasService = $petugasService;
    }

    /**
     * Dashboard Petugas
     */
    public function dashboard()
    {
        $data = $this->petugasService->getDashboardData();

        return response()->json([
            'success' => true,
            'message' => 'Data dashboard petugas',
            'data' => new PetugasResource($data)
        ], 200);
    }

    /**
     * Approve peminjaman
     */
    public function approve($id)
    {
        try {
            $loan = $this->petugasService->approveLoan($id);

            return response()->json([
                'success' => true,
                'message' => 'Peminjaman berhasil disetujui',
                'data' => new LoanResource($loan)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Reject peminjaman
     */
    public function reject($id)
    {
        try {
            $loan = $this->petugasService->rejectLoan($id);

            return response()->json([
                'success' => true,
                'message' => 'Peminjaman ditolak',
                'data' => new LoanResource($loan)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Proses pengembalian alat
     */
    public function processReturn(\Illuminate\Http\Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'denda_rusak' => 'nullable|numeric|min:0',
                'denda_hilang' => 'nullable|numeric|min:0',
                'keterangan_kondisi' => 'nullable|string',
                'bukti_penerimaan' => 'nullable|file|max:2048' // ✅ Terima file gambar
            ]);

            // Kirim request file ke Service
            $loan = $this->petugasService->processReturn($id, $validated, $request->file('bukti_penerimaan'));

            return response()->json([
                'success' => true,
                'message' => 'Alat berhasil dikembalikan dan denda (jika ada) telah diakumulasi.',
                'data' => new \App\Http\Resources\Admin\LoanResource($loan)
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
    /**
     * Laporan peminjaman (Digunakan di halaman Report)
     */
    public function report(Request $request)
    {
        try {
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');

            $data = $this->petugasService->getFilteredReport($startDate, $endDate);

            return response()->json([
                'success' => true,
                'message' => 'Data laporan berhasil diambil',
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Detail peminjaman
     */
    public function show($id)
    {
        $loan = $this->petugasService->getLoanById($id);

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
}
