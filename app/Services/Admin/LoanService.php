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
    $stok = $data['stok'] ?? 1;

    // Cek stok
    if (!$tool || $tool->stok < $stok) {
        throw new \Exception("Stok alat tidak mencukupi. Stok tersedia: {$tool->stok}, Dibutuhkan: {$stok}");
    }

    // Set default status jika tidak ada
    if (!isset($data['status'])) {
        $data['status'] = 'pending';
    }

    // Set default tanggal pinjam jika tidak ada
    if (!isset($data['tanggal_pinjam'])) {
        $data['tanggal_pinjam'] = now()->toDateString();
    }

    // ✅ Ambil harga berdasarkan durasi
    $hargaSatuan = match ($data['durasi']) {
        '6jam' => $tool->harga_6jam,
        '12jam' => $tool->harga_12jam,
        '24jam' => $tool->harga_24jam,
        default => $tool->harga_24jam,
    };

    $subtotal = $hargaSatuan * $stok;

    $loan = Loan::create([
        'invoice_number' => Loan::generateInvoiceNumber(),
        'user_id' => $data['user_id'],
        'tool_id' => $data['tool_id'],
        'stok' => $stok,
        'durasi' => $data['durasi'],
        'harga_satuan' => $hargaSatuan,
        'subtotal' => $subtotal,
        'total_bayar' => $subtotal,
        'tanggal_pinjam' => $data['tanggal_pinjam'],
        'tanggal_kembali' => $data['tanggal_kembali'],
        'status' => $data['status'],
        'petugas_id' => Auth::id(),
    ]);

    // Kurangi stok alat jika status disetujui
    if ($data['status'] === 'disetujui' && $tool) {
        $tool->decrement('stok', $stok);
    }

    ActivityLog::create([
        'user_id' => Auth::id(),
        'action' => 'Tambah Peminjaman',
        'description' => "Menambahkan peminjaman baru untuk user ID: {$data['user_id']} dengan alat ID: {$data['tool_id']}, Jumlah: {$stok}, Durasi: {$data['durasi']}"
    ]);

    return $loan->load(['user', 'tool']);
}

    public function updateLoan(Loan $loan, array $data): Loan
    {
        $oldStatus = $loan->status;
        $tool = $loan->tool;
        $stokDipinjam = $data['stok'] ?? $loan->stok;  // ← Ganti quantity ke stok

        // Cek stok jika stok dipinjam berubah
        if (isset($data['stok']) && $data['stok'] != $loan->stok) {
            $diff = $data['stok'] - $loan->stok;
            if ($diff > 0 && $tool && $tool->stok < $diff) {
                throw new \Exception("Stok alat tidak mencukupi. Stok tersedia: {$tool->stok}, Dibutuhkan tambahan: {$diff}");
            }
        }

        // Logika perubahan stok berdasarkan status
        if ($oldStatus === 'pending' && isset($data['status']) && $data['status'] === 'disetujui') {
            // Pending → Disetujui: kurangi stok
            if ($tool && $tool->stok < $stokDipinjam) {
                throw new \Exception("Stok alat tidak mencukupi. Stok tersedia: {$tool->stok}, Dibutuhkan: {$stokDipinjam}");
            }
            $tool?->decrement('stok', $stokDipinjam);
        }
        elseif (in_array($oldStatus, ['disetujui', 'telat']) && isset($data['status']) && $data['status'] === 'kembali') {
            // Disetujui/Telat → Kembali: tambah stok
            $tool?->increment('stok', $loan->stok);
        }
        elseif ($oldStatus === 'disetujui' && isset($data['status']) && in_array($data['status'], ['pending', 'ditolak'])) {
            // Disetujui → Pending/Ditolak: kembalikan stok
            $tool?->increment('stok', $loan->stok);
        }

        // Update loan
        $loan->update($data);

        // Cek keterlambatan untuk status disetujui
        if ($loan->status === 'disetujui') {
            $this->checkLateStatus($loan);
        }

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'Update Peminjaman',
            'description' => "Memperbarui peminjaman ID: {$loan->id}, status: {$oldStatus} → {$loan->status}"
        ]);

        return $loan->load(['user', 'tool']);
    }

    public function checkLateStatus(Loan $loan): void
    {
        if ($loan->status === 'disetujui') {
            $today = now()->toDateString();
            $tanggalKembali = $loan->tanggal_kembali;

            $lateDays = now()->parse($today)->diffInDays(now()->parse($tanggalKembali), false);

            if ($lateDays >= 3) {
                $loan->update(['status' => 'telat']);

                ActivityLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'Status Berubah Jadi Telat',
                    'description' => "Peminjaman ID: {$loan->id} telat {$lateDays} hari dari tanggal kembali."
                ]);
            }
        }
    }

    public function checkAllLateStatuses(): void
    {
        $activeLoans = Loan::where('status', 'disetujui')->get();
        foreach ($activeLoans as $loan) {
            $this->checkLateStatus($loan);
        }
    }

    public function deleteLoan(Loan $loan): bool
    {
        if (in_array($loan->status, ['disetujui', 'telat']) && $loan->tool) {
            $loan->tool->increment('stok', $loan->stok);
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
        $validStatus = ['pending', 'disetujui', 'ditolak', 'kembali', 'telat'];

        if (!in_array($status, $validStatus)) {
            return collect();
        }

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
            'total_late' => Loan::where('status', 'telat')->count(),
            'total_returned' => Loan::where('status', 'kembali')->count(),
            'total_rejected' => Loan::where('status', 'ditolak')->count(),
        ];
    }
}
