<?php

namespace App\Services\Peminjam;

use App\Models\Tool;
use App\Models\Loan;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class PeminjamService
{
    /**
     * Get all available tools (stok > 0)
     */
    public function getAllAvailableTools(): \Illuminate\Database\Eloquent\Collection
    {
        return Tool::with('category')
            ->where('stok', '>', 0)
            ->latest()
            ->get();
    }

    /**
     * Get tool detail by ID
     */
    public function getToolDetail(int $toolId): ?Tool
    {
        return Tool::with('category')->find($toolId);
    }

    /**
     * Submit new loan request
     */
    public function submitLoanRequest(int $userId, int $toolId, string $tanggalKembali): Loan
    {
        $tool = Tool::find($toolId);

        if (!$tool || $tool->stok <= 0) {
            throw new \Exception('Stok alat tidak tersedia.');
        }

        // Cek apakah user sudah meminjam alat yang sama dan belum selesai
        $existingLoan = Loan::where('user_id', $userId)
            ->where('tool_id', $toolId)
            ->whereIn('status', ['pending', 'disetujui'])
            ->first();

        if ($existingLoan) {
            throw new \Exception('Anda masih memiliki peminjaman alat ini yang belum selesai.');
        }

        $loan = Loan::create([
            'user_id' => $userId,
            'tool_id' => $toolId,
            'tanggal_pinjam' => now()->toDateString(),
            'tanggal_kembali' => $tanggalKembali,
            'status' => 'pending'
        ]);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'Ajukan Peminjaman',
            'description' => "User mengajukan peminjaman alat: {$tool->nama_alat}"
        ]);

        return $loan->load(['tool']);
    }

    /**
     * Get user loan history
     */
    public function getUserLoanHistory(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return Loan::with(['tool'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get loan detail by ID (pastikan milik user yang login)
     */
    public function getLoanById(int $loanId, int $userId): ?Loan
    {
        return Loan::with(['tool'])
            ->where('id', $loanId)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Get loan statistics for user
     */
    public function getUserLoanStats(int $userId): array
    {
        return [
            'total_loans' => Loan::where('user_id', $userId)->count(),
            'pending_loans' => Loan::where('user_id', $userId)->where('status', 'pending')->count(),
            'active_loans' => Loan::where('user_id', $userId)->where('status', 'disetujui')->count(),
            'completed_loans' => Loan::where('user_id', $userId)->where('status', 'kembali')->count(),
            'rejected_loans' => Loan::where('user_id', $userId)->where('status', 'ditolak')->count(),
        ];
    }
}
