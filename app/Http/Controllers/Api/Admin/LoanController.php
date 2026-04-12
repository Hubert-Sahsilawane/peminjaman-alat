<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoanRequest;
use App\Http\Resources\Admin\LoanResource;
use App\Services\Admin\LoanService;

class LoanController extends Controller
{
    protected LoanService $loanService;

    public function __construct(LoanService $loanService)
    {
        $this->loanService = $loanService;
    }

    public function index()
    {
        $loans = $this->loanService->getAllLoans(10);

        return response()->json([
            'success' => true,
            'message' => 'Daftar peminjaman berhasil diambil',
            'data' => [
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

    public function store(LoanRequest $request)
    {
        try {
            $loan = $this->loanService->createLoan($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Peminjaman berhasil ditambahkan',
                'data' => new LoanResource($loan)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function show($id)
    {
        $loan = $this->loanService->getLoanById($id);

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

    public function update(LoanRequest $request, $id)
    {
        $loan = $this->loanService->getLoanById($id);

        if (!$loan) {
            return response()->json([
                'success' => false,
                'message' => 'Peminjaman tidak ditemukan'
            ], 404);
        }

        try {
            $loan = $this->loanService->updateLoan($loan, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Peminjaman berhasil diperbarui',
                'data' => new LoanResource($loan)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function destroy($id)
    {
        $loan = $this->loanService->getLoanById($id);

        if (!$loan) {
            return response()->json([
                'success' => false,
                'message' => 'Peminjaman tidak ditemukan'
            ], 404);
        }

        $this->loanService->deleteLoan($loan);

        return response()->json([
            'success' => true,
            'message' => 'Peminjaman berhasil dihapus'
        ], 200);
    }

    public function byStatus($status)
{
    $validStatus = ['pending', 'disetujui', 'ditolak', 'kembali', 'telat'];  // ✅ Tambah 'telat'

    if (!in_array($status, $validStatus)) {
        return response()->json([
            'success' => false,
            'message' => 'Status tidak valid'
        ], 400);
    }

    $loans = $this->loanService->getLoansByStatus($status);

    return response()->json([
        'success' => true,
        'message' => "Daftar peminjaman dengan status: {$status}",
        'data' => LoanResource::collection($loans)
    ], 200);
}

    public function selectData()
    {
        return response()->json([
            'success' => true,
            'message' => 'Data untuk select',
            'data' => [
                'users' => $this->loanService->getUsersForSelect(),
                'tools' => $this->loanService->getToolsForSelect(),
            ]
        ], 200);
    }

    public function stats()
    {
        $stats = $this->loanService->getDashboardStats();

        return response()->json([
            'success' => true,
            'message' => 'Statistik peminjaman',
            'data' => $stats
        ], 200);
    }
}
