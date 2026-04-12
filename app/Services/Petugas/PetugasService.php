<?php

namespace App\Services\Petugas;

use App\Models\Loan;
use App\Models\Tool;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;

class PetugasService
{
    public function getDashboardData(): array
    {
        return [
            'pending_loans' => Loan::with(['user', 'tool'])
                ->where('status', 'pending')
                ->latest()
                ->get(),
            'active_loans' => Loan::with(['user', 'tool'])
                ->where('status', 'disetujui')
                ->latest()
                ->get(),
            'completed_loans' => Loan::with(['user', 'tool'])
                ->where('status', 'kembali')
                ->latest()
                ->get(),
        ];
    }

    public function approveLoan(int $loanId): Loan
{
    $loan = Loan::findOrFail($loanId);

    if ($loan->status !== 'pending') {
        throw new \Exception('Peminjaman sudah diproses sebelumnya.');
    }

    $tool = Tool::find($loan->tool_id);
    $stokDipinjam = $loan->stok ?? 1;

    if ($tool && $tool->stok < $stokDipinjam) {
        throw new \Exception("Stok alat tidak mencukupi. Stok tersedia: {$tool->stok}, Dibutuhkan: {$stokDipinjam}");
    }

    $loan->update([
        'status' => 'disetujui',
        'petugas_id' => Auth::id(),
    ]);

    if ($tool) {
        $tool->decrement('stok', $stokDipinjam);
    }

    ActivityLog::create([
        'user_id' => Auth::id(),
        'action' => 'Setujui Peminjaman',
        'description' => "Petugas menyetujui peminjaman alat: {$loan->tool->nama_alat} oleh {$loan->user->name}, Jumlah: {$stokDipinjam}"
    ]);

    return $loan->load(['user', 'tool']);
}
    public function rejectLoan(int $loanId): Loan
    {
        $loan = Loan::findOrFail($loanId);

        if ($loan->status !== 'pending') {
            throw new \Exception('Peminjaman sudah diproses sebelumnya.');
        }

        $loan->update([
            'status' => 'ditolak',
            'petugas_id' => Auth::id(),
        ]);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'Tolak Peminjaman',
            'description' => "Petugas menolak peminjaman alat: {$loan->tool->nama_alat} oleh {$loan->user->name}"
        ]);

        return $loan->load(['user', 'tool']);
    }

    public function processReturn(int $loanId): Loan
{
    $loan = Loan::findOrFail($loanId);

    if ($loan->status !== 'disetujui') {
        throw new \Exception('Data tidak valid atau sudah dikembalikan.');
    }

    $loan->update([
        'status' => 'kembali',
        'petugas_id' => Auth::id(),
    ]);

    // Kembalikan stok alat
    if ($loan->tool) {
        $loan->tool->increment('stok', $loan->stok);
    }

    ActivityLog::create([
        'user_id' => Auth::id(),
        'action' => 'Proses Pengembalian',
        'description' => "Petugas memproses pengembalian alat: {$loan->tool->nama_alat} oleh {$loan->user->name}"
    ]);

    return $loan->load(['user', 'tool']);
}

    public function getAllLoansForReport(int $perPage = 10): LengthAwarePaginator
    {
        return Loan::with(['user', 'tool'])
            ->latest()
            ->paginate($perPage);
    }

    public function getLoanById(int $id): ?Loan
    {
        return Loan::with(['user', 'tool'])->find($id);
    }

    public function getReportStats(): array
    {
        return [
            'total_loans' => Loan::count(),
            'total_pending' => Loan::where('status', 'pending')->count(),
            'total_approved' => Loan::where('status', 'disetujui')->count(),
            'total_returned' => Loan::where('status', 'kembali')->count(),
            'total_rejected' => Loan::where('status', 'ditolak')->count(),
        ];
    }
}
