<?php

namespace App\Services\Admin;

use App\Models\Loan;
use App\Models\Tool;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;

class LoanService
{
    public function getAllLoans(int $perPage = 10): LengthAwarePaginator
    {
        return Loan::with(['user', 'tool'])->latest()->paginate($perPage);
    }

    public function getLoanById(int $id): ?Loan
    {
        return Loan::with(['user', 'tool', 'petugas'])->find($id);
    }

    public function createLoan(array $data): Loan
    {
        $tool = Tool::find($data['tool_id']);

        // Cek stok jika status langsung disetujui
        if (isset($data['status']) && $data['status'] === 'disetujui') {
            if (!$tool || $tool->stok < 1) {
                throw new \Exception('Stok alat kosong, tidak bisa set status Disetujui.');
            }
        }

        // Set default status jika tidak ada
        if (!isset($data['status'])) {
            $data['status'] = 'pending';
        }

        // Set default tanggal pinjam jika tidak ada
        if (!isset($data['tanggal_pinjam'])) {
            $data['tanggal_pinjam'] = now()->toDateString();
        }

        $loan = Loan::create([
            'user_id' => $data['user_id'],
            'tool_id' => $data['tool_id'],
            'tanggal_pinjam' => $data['tanggal_pinjam'],
            'tanggal_kembali' => $data['tanggal_kembali'],  // <-- PERBAIKI: jadi tanggal_kembali
            'status' => $data['status'],
            'petugas_id' => Auth::id(),
        ]);

        // Kurangi stok jika status disetujui
        if ($data['status'] === 'disetujui' && $tool) {
            $tool->decrement('stok');
        }

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'Tambah Peminjaman',
            'description' => "Menambahkan peminjaman baru untuk user ID: {$data['user_id']} dengan alat ID: {$data['tool_id']}"
        ]);

        return $loan->load(['user', 'tool']);
    }

    public function updateLoan(Loan $loan, array $data): Loan
    {
        $oldStatus = $loan->status;
        $tool = $loan->tool;

        // Logika perubahan stok berdasarkan status
        if ($oldStatus === 'pending' && isset($data['status']) && $data['status'] === 'disetujui') {
            // Pending → Disetujui: kurangi stok
            if ($tool && $tool->stok < 1) {
                throw new \Exception('Stok alat habis, tidak bisa menyetujui peminjaman.');
            }
            $tool?->decrement('stok');
        }
        elseif ($oldStatus === 'disetujui' && isset($data['status']) && $data['status'] === 'kembali') {
            // Disetujui → Kembali: tambah stok
            $tool?->increment('stok');
        }
        elseif ($oldStatus === 'disetujui' && isset($data['status']) && in_array($data['status'], ['pending', 'ditolak'])) {
            // Disetujui → Pending/Ditolak: kembalikan stok
            $tool?->increment('stok');
        }

        // Update loan
        $loan->update($data);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'Update Peminjaman',
            'description' => "Memperbarui peminjaman ID: {$loan->id}, status: {$oldStatus} → {$loan->status}"
        ]);

        return $loan->load(['user', 'tool']);
    }

    public function deleteLoan(Loan $loan): bool
    {
        // Jika menghapus data yang statusnya masih 'disetujui', kembalikan stok
        if ($loan->status === 'disetujui' && $loan->tool) {
            $loan->tool->increment('stok');
        }

        $loanId = $loan->id;
        $loan->delete();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'Hapus Peminjaman',
            'description' => "Menghapus peminjaman ID: {$loanId}"
        ]);

        return true;
    }

    public function getLoansByStatus(string $status): \Illuminate\Database\Eloquent\Collection
    {
        return Loan::with(['user', 'tool'])
            ->where('status', $status)
            ->latest()
            ->get();
    }

    public function getLoansByUser(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return Loan::with(['tool'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getUsersForSelect(): \Illuminate\Database\Eloquent\Collection
    {
        return User::whereHas('roles', function ($q) {
            $q->where('name', 'peminjam');
        })->get(['id', 'name', 'email']);
    }

    public function getToolsForSelect(): \Illuminate\Database\Eloquent\Collection
    {
        return Tool::all(['id', 'nama_alat', 'stok']);
    }

    public function getDashboardStats(): array
    {
        return [
            'total_pending' => Loan::where('status', 'pending')->count(),
            'total_active' => Loan::where('status', 'disetujui')->count(),
            'total_returned' => Loan::where('status', 'kembali')->count(),
            'total_rejected' => Loan::where('status', 'ditolak')->count(),
        ];
    }
}
