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

    /**
     * Display a listing of the resource.
     */
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

    /**
     * Store a newly created resource in storage.
     */
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

    /**
     * Display the specified resource.
     */
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

    /**
     * Update the specified resource in storage.
     */
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

    /**
     * Remove the specified resource from storage.
     */
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

    /**
     * Get loans by status.
     */
    public function byStatus($status)
    {
        $validStatus = ['pending', 'disetujui', 'ditolak', 'kembali'];

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

    /**
     * Get data for select dropdowns.
     */
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

    /**
     * Get dashboard statistics.
     */
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
