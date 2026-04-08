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
    public function processReturn($id)
    {
        try {
            $loan = $this->petugasService->processReturn($id);

            return response()->json([
                'success' => true,
                'message' => 'Alat berhasil dikembalikan',
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
     * Laporan peminjaman
     */
    public function report(Request $request)
    {
        $loans = $this->petugasService->getAllLoansForReport(10);
        $stats = $this->petugasService->getReportStats();

        return response()->json([
            'success' => true,
            'message' => 'Laporan peminjaman',
            'data' => [
                'stats' => $stats,
                'loans' => LoanResource::collection($loans),
                'pagination' => [
                    'current_page' => $loans->currentPage(),
                    'last_page' => $loans->lastPage(),
                    'per_page' => $loans->perPage(),
                    'total' => $loans->total(),
                ]
            ]
        ], 200);
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
