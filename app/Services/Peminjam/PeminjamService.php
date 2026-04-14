<?php

namespace App\Services\Peminjam;

use App\Models\Tool;
use App\Models\Loan;
use App\Models\Cart;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PeminjamService
{
    // ==================== ALAT ====================

    public function getAllAvailableTools(): \Illuminate\Database\Eloquent\Collection
    {
        return Tool::with('category')
            // Kita tampilkan semua alat, termasuk yang stoknya habis agar user bisa lihat katalog penuh
            ->latest()
            ->get();
    }

    public function getToolDetail(int $toolId): ?Tool
    {
        return Tool::with('category')->find($toolId);
    }

    // ==================== CART ====================

    public function getCart()
    {
        $carts = Cart::with('tool')
            ->where('user_id', Auth::id())
            ->get();

        $total = $carts->sum('subtotal');

        return [
            'items' => $carts,
            'total' => $total,
            'total_formatted' => 'Rp ' . number_format($total, 0, ',', '.'),
        ];
    }

    public function addToCart(int $toolId, int $quantity, string $durasi): Cart
    {
        $tool = Tool::findOrFail($toolId);

        // Ambil harga sesuai durasi
        $hargaSatuan = match ($durasi) {
            '6jam' => $tool->harga_6jam,
            '12jam' => $tool->harga_12jam,
            '24jam' => $tool->harga_24jam,
            default => $tool->harga_24jam,
        };

        if ($hargaSatuan <= 0) {
            throw new \Exception('Harga untuk durasi ini belum tersedia');
        }

        $subtotal = $hargaSatuan * $quantity;

        // Cek apakah sudah ada di keranjang
        $cart = Cart::where('user_id', Auth::id())
            ->where('tool_id', $toolId)
            ->where('durasi', $durasi)
            ->first();

        if ($cart) {
            $cart->update([
                'quantity' => $cart->quantity + $quantity,
                'subtotal' => $cart->subtotal + $subtotal,
            ]);
            return $cart;
        }

        return Cart::create([
            'user_id' => Auth::id(),
            'tool_id' => $toolId,
            'quantity' => $quantity,
            'durasi' => $durasi,
            'harga_satuan' => $hargaSatuan,
            'subtotal' => $subtotal,
        ]);
    }

    public function updateCartItem(int $cartId, int $quantity): Cart
    {
        $cart = Cart::where('id', $cartId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $cart->update([
            'quantity' => $quantity,
            'subtotal' => $cart->harga_satuan * $quantity,
        ]);

        return $cart;
    }

    public function removeFromCart(int $cartId): void
    {
        Cart::where('id', $cartId)
            ->where('user_id', Auth::id())
            ->delete();
    }

    public function clearCart(): void
    {
        Cart::where('user_id', Auth::id())->delete();
    }

    // ==================== CHECKOUT & LOAN ====================

    public function submitLoanRequest(int $userId, int $toolId, int $stok, string $tanggalKembali): Loan
    {
        $tool = Tool::findOrFail($toolId);

        if ($tool->stok < $stok) {
            throw new \Exception("Stok alat tidak mencukupi. Stok tersedia: {$tool->stok}, Dibutuhkan: {$stok}");
        }

        // Cek apakah user sudah meminjam alat yang sama dan belum selesai
        $existingLoan = Loan::where('user_id', $userId)
            ->where('tool_id', $toolId)
            ->whereIn('status', ['pending', 'disetujui', 'telat'])
            ->first();

        if ($existingLoan) {
            throw new \Exception('Anda masih memiliki peminjaman alat ini yang belum selesai.');
        }

        $loan = Loan::create([
            'user_id' => $userId,
            'tool_id' => $toolId,
            'stok' => $stok,
            'tanggal_pinjam' => now()->toDateString(),
            'tanggal_kembali' => $tanggalKembali,
            'status' => 'pending'
        ]);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'Ajukan Peminjaman',
            'description' => "User mengajukan peminjaman alat: {$tool->nama_alat}, Jumlah: {$stok}"
        ]);

        return $loan->load(['tool']);
    }

    public function checkout(array $data): array
    {
        $carts = Cart::with('tool')
            ->where('user_id', Auth::id())
            ->get();

        if ($carts->isEmpty()) {
            throw new \Exception('Keranjang belanja kosong');
        }

        DB::beginTransaction();

        try {
            // Generate 1 invoice untuk semua item
            $invoiceNumber = Loan::generateInvoiceNumber();
            $loans = [];
            $grandTotal = 0;

            foreach ($carts as $cart) {
                $tool = $cart->tool;

                // Tetap cek stok di sini untuk memastikan saat checkout alat belum habis disewa orang lain
                if ($tool->stok < $cart->quantity) {
                    throw new \Exception("Stok {$tool->nama_alat} tidak mencukupi. Tersedia: {$tool->stok}, Dibutuhkan: {$cart->quantity}");
                }

                $subtotal = $cart->subtotal;
                $grandTotal += $subtotal;

                $loan = Loan::create([
                    'invoice_number' => $invoiceNumber,
                    'user_id' => Auth::id(),
                    'tool_id' => $cart->tool_id,
                    'stok' => $cart->quantity,
                    'durasi' => $cart->durasi,
                    'harga_satuan' => $cart->harga_satuan,
                    'subtotal' => $subtotal,
                    'total_bayar' => $subtotal,
                    'tanggal_pinjam' => $data['tanggal_pinjam'],
                    'tanggal_kembali' => $data['tanggal_kembali'],
                    'status' => 'pending',
                    'status_pembayaran' => 'pending',
                ]);

                $loans[] = $loan;

                // ✅ BARIS PENGURANGAN STOK DI SINI DIHAPUS.
                // Stok hanya akan berkurang saat petugas klik "Approve" (disetujui).
            }

            // Update total_bayar untuk semua loan dengan invoice yang sama
            Loan::where('invoice_number', $invoiceNumber)
                ->update(['total_bayar' => $grandTotal]);

            // Kosongkan keranjang
            Cart::where('user_id', Auth::id())->delete();

            DB::commit();

            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'Checkout',
                'description' => "User melakukan checkout dengan invoice: {$invoiceNumber}, Total: Rp " . number_format($grandTotal, 0, ',', '.')
            ]);

            // Ambil semua loan dengan invoice yang sama
            $allLoans = Loan::with(['tool'])
                ->where('invoice_number', $invoiceNumber)
                ->get();

            return [
                'invoice_number' => $invoiceNumber,
                'items' => $allLoans,
                'grand_total' => $grandTotal,
                'tanggal_pinjam' => $data['tanggal_pinjam'],
                'tanggal_kembali' => $data['tanggal_kembali'],
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // ==================== RIWAYAT ====================

    public function getUserLoanHistory(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return Loan::with(['tool'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getLoanById(int $loanId, int $userId): ?Loan
    {
        return Loan::with(['tool'])
            ->where('id', $loanId)
            ->where('user_id', $userId)
            ->first();
    }

    public function getUserLoanStats(int $userId): array
    {
        return [
            'total_loans' => Loan::where('user_id', $userId)->count(),
            'pending_loans' => Loan::where('user_id', $userId)->where('status', 'pending')->count(),
            'active_loans' => Loan::where('user_id', $userId)->where('status', 'disetujui')->count(),
            'late_loans' => Loan::where('user_id', $userId)->where('status', 'telat')->count(),
            'completed_loans' => Loan::where('user_id', $userId)->where('status', 'kembali')->count(),
            'rejected_loans' => Loan::where('user_id', $userId)->where('status', 'ditolak')->count(),
        ];
    }

    // ==================== TRANSAKSI / PEMBAYARAN ====================

    public function getInvoiceDetail(string $invoiceNumber, int $userId)
    {
        $loans = Loan::with('tool')
            ->where('invoice_number', $invoiceNumber)
            ->where('user_id', $userId)
            ->get();

        if ($loans->isEmpty()) {
            throw new \Exception('Invoice tidak ditemukan.');
        }

        $grandTotal = 0;
        $statusPembayaran = 'lunas';

        foreach ($loans as $loan) {
            // ✅ Jumlahkan subtotal sewa + semua jenis denda
            $itemTotal = $loan->subtotal + $loan->denda + $loan->denda_rusak + $loan->denda_hilang;
            $grandTotal += $itemTotal;

            if ($loan->status_pembayaran === 'pending') {
                $statusPembayaran = 'pending';
            }
        }

        return [
            'invoice_number' => $invoiceNumber,
            'items' => $loans,
            'grand_total' => $grandTotal, // ✅ Sekarang Grand Total 100% Akurat!
            'status_pembayaran' => $statusPembayaran,
            'tanggal_pinjam' => $loans->first()->tanggal_pinjam,
            'tanggal_kembali' => $loans->first()->tanggal_kembali,
        ];
    }

    public function payInvoice(string $invoiceNumber, int $userId): bool
    {
        $loans = Loan::where('invoice_number', $invoiceNumber)
            ->where('user_id', $userId)
            ->get();

        if ($loans->isEmpty()) {
            throw new \Exception('Invoice tidak ditemukan.');
        }

        // Update semua item dalam invoice tersebut menjadi lunas
        Loan::where('invoice_number', $invoiceNumber)
            ->where('user_id', $userId)
            ->update(['status_pembayaran' => 'lunas']);

        ActivityLog::create([
            'user_id' => $userId,
            'action' => 'Pembayaran',
            'description' => "User melakukan pembayaran untuk invoice: {$invoiceNumber}"
        ]);

        return true;
    }

    // app/Services/Peminjam/PeminjamService.php

public function requestReturn(int $loanId, int $userId, array $data, $file = null)
    {
        $loan = Loan::where('id', $loanId)->where('user_id', $userId)->firstOrFail();

        if ($loan->status !== 'disetujui' && $loan->status !== 'telat') {
            throw new \Exception('Status barang tidak valid untuk dikembalikan.');
        }

        // Proses Upload Gambar
        $pathGambar = null;
        if ($file) {
            $pathGambar = $file->store('returns', 'public');
        }

        // ✅ PERBAIKAN: Hapus update status ke 'kembali'.
        // Biarkan status tetap 'disetujui', kita hanya menyimpan laporan kondisinya saja.
        $loan->update([
            'kondisi_kembali' => $data['kondisi_kembali'],
            'catatan_peminjam' => $data['catatan_peminjam'],
            'bukti_kembali' => $pathGambar
        ]);

        $loan->refresh();

        return [
            'has_denda' => $loan->denda > 0,
            'invoice_number' => $loan->invoice_number,
            'denda' => $loan->denda
        ];
    }
}
