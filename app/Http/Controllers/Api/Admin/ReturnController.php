<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Http\Resources\Admin\LoanResource;
use App\Services\Admin\LoanService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;    

class ReturnController extends Controller
{
    protected LoanService $loanService;

    // ✅ Injeksi LoanService
    public function __construct(LoanService $loanService)
    {
        $this->loanService = $loanService;
    }

    public function history()
    {
        $loans = Loan::with(['user', 'tool', 'petugas'])
            ->whereIn('status', ['kembali', 'telat'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Riwayat pengembalian',
            'data' => LoanResource::collection($loans)
        ], 200);
    }

    public function active()
    {
        $loans = Loan::with(['user', 'tool'])
            ->where('status', 'disetujui')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Alat sedang dipinjam',
            'data' => LoanResource::collection($loans)
        ], 200);
    }

    public function process($id)
    {
        try {
            $loan = Loan::findOrFail($id);

            if ($loan->status !== 'disetujui') {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak valid atau sudah dikembalikan.'
                ], 400);
            }

            $this->loanService->updateLoan($loan, [
                'status' => 'kembali',
                'petugas_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Alat berhasil dikembalikan'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error Backend: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        // 1. Validasi input tanggal_kembali tidak boleh sebelum hari ini (date >= today)
        $request->validate([
            'tanggal_kembali' => 'required|date|after_or_equal:today',
        ], [
            'tanggal_kembali.after_or_equal' => 'Tanggal pengembalian tidak boleh tanggal di masa lalu (sebelum hari ini).',
        ]);

        try {
            $loan = Loan::findOrFail($id);

            // 2. Pastikan yang bisa di-edit hanya status kembali atau telat
            if (!in_array($loan->status, ['kembali', 'telat'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya peminjaman yang sudah selesai/dikembalikan yang tanggalnya dapat diubah.'
                ], 400);
            }

            // 3. Update tanggal kembali melalui service
            $this->loanService->updateLoan($loan, [
                'tanggal_kembali' => $request->tanggal_kembali
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tanggal pengembalian berhasil diubah.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
