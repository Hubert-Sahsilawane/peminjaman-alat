<?php

namespace App\Services\Petugas;

use App\Models\Loan;
use App\Models\Tool;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

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
                ->whereIn('status', ['kembali', 'telat'])
                ->whereMonth('tanggal_kembali', date('m')) // Filter bulan ini saja (Opsional)
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

    public function processReturn(int $loanId, array $data = [], $file = null)
    {
        $loan = Loan::findOrFail($loanId);

        $dendaRusak = $data['denda_rusak'] ?? 0;
        $dendaHilang = $data['denda_hilang'] ?? 0;
        $keteranganFisik = $data['keterangan_kondisi'] ?? '';

        // Upload Gambar Petugas jika ada
        $pathGambarPetugas = null;
        if ($file) {
            $pathGambarPetugas = $file->store('returns_petugas', 'public');
        }

        $totalBayarBaru = $loan->subtotal + $loan->denda + $dendaRusak + $dendaHilang;
        $statusPembayaran = ($totalBayarBaru > $loan->subtotal) ? 'pending' : $loan->status_pembayaran;

        $loan->update([
            'status' => 'kembali',
            'denda_rusak' => $dendaRusak,
            'denda_hilang' => $dendaHilang,
            'keterangan_kondisi' => $keteranganFisik,
            'bukti_penerimaan_petugas' => $pathGambarPetugas, // Simpan URL gambar
            'total_bayar' => $totalBayarBaru,
            'status_pembayaran' => $statusPembayaran
        ]);

        // ✅ LOGIKA BARU: Pengecekan Kondisi Sebelum Tambah Stok
        if ($loan->tool) {
            $kondisiAman = true;

            // Jika peminjam lapor rusak/hilang, ATAU petugas memberi denda fisik, maka kondisi = TIDAK AMAN
            if (
                $loan->kondisi_kembali === 'Ada Kerusakan' ||
                $loan->kondisi_kembali === 'Hilang / Tidak Lengkap' ||
                $dendaRusak > 0 ||
                $dendaHilang > 0
            ) {
                $kondisiAman = false;
            }

            // Jika kondisi aman, stok alat bertambah.
            // Jika tidak aman, stok dibiarkan tetap berkurang agar tidak disewa orang lain.
            if ($kondisiAman) {
                $loan->tool->increment('stok', $loan->stok);
            }
        }

        return $loan;
    }

    public function getLoanById(int $id): ?Loan
    {
        return Loan::with(['user', 'tool'])->find($id);
    }

    public function getAllLoansForReport(int $limit = 100)
    {
        // Mengambil semua data peminjaman untuk dicetak, dilimit agar tidak berat
        return Loan::with(['user', 'tool'])
            ->latest()
            ->take($limit)
            ->get();
    }

    public function getReportStats(): array
    {
        return [
            'total_loans' => Loan::count(),
            'total_pending' => Loan::where('status', 'pending')->count(),
            'total_approved' => Loan::where('status', 'disetujui')->count(),
            'total_returned' => Loan::whereIn('status', ['kembali', 'telat'])->count(),
            'total_rejected' => Loan::where('status', 'ditolak')->count(),
        ];
    }

    public function getFilteredReport($startDate = null, $endDate = null)
    {
        $query = Loan::with(['user', 'tool'])
            ->whereIn('status', ['kembali', 'telat']); // Hanya transaksi selesai yang masuk income

        if ($startDate && $endDate) {
            // Filter berdasarkan tanggal pinjam
            $query->whereBetween('tanggal_pinjam', [$startDate, $endDate]);
        }

        return $query->latest()->get();
    }
}
