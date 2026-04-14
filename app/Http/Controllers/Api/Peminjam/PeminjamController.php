<?php

namespace App\Http\Controllers\Api\Peminjam;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request; // ✅ Pastikan Request di-import
use App\Http\Requests\Peminjam\CartRequest;
use App\Http\Requests\Peminjam\CheckoutRequest;
use App\Http\Resources\Admin\ToolResource;
use App\Http\Resources\Peminjam\CartResource;
use App\Http\Resources\Peminjam\CheckoutResource;
use App\Services\Peminjam\PeminjamService;
use Illuminate\Support\Facades\Auth;
use App\Mail\PaymentSuccessfulMail;
use Illuminate\Support\Facades\Mail;

class PeminjamController extends Controller
{
    protected PeminjamService $peminjamService;

    public function __construct(PeminjamService $peminjamService)
    {
        $this->peminjamService = $peminjamService;
    }

    // ==================== ALAT ====================

    public function tools()
    {
        $tools = $this->peminjamService->getAllAvailableTools();

        return response()->json([
            'success' => true,
            'message' => 'Daftar alat tersedia',
            'data' => ToolResource::collection($tools)
        ], 200);
    }

    public function toolDetail($id)
    {
        $tool = $this->peminjamService->getToolDetail($id);

        if (!$tool) {
            return response()->json([
                'success' => false,
                'message' => 'Alat tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail alat',
            'data' => new ToolResource($tool)
        ], 200);
    }

    // ==================== CART ====================

    public function getCart()
    {
        $cart = $this->peminjamService->getCart();

        return response()->json([
            'success' => true,
            'message' => 'Keranjang belanja',
            'data' => [
                'items' => CartResource::collection($cart['items']),
                'total' => $cart['total'],
                'total_formatted' => $cart['total_formatted'],
            ]
        ], 200);
    }

    public function addToCart(CartRequest $request)
    {
        try {
            $cart = $this->peminjamService->addToCart(
                $request->tool_id,
                $request->quantity,
                $request->durasi
            );

            return response()->json([
                'success' => true,
                'message' => 'Item berhasil ditambahkan ke keranjang',
                'data' => new CartResource($cart)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function updateCartItem(CartRequest $request, $id)
    {
        try {
            $cart = $this->peminjamService->updateCartItem($id, $request->quantity);

            return response()->json([
                'success' => true,
                'message' => 'Keranjang berhasil diperbarui',
                'data' => new CartResource($cart)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    public function removeFromCart($id)
    {
        try {
            $this->peminjamService->removeFromCart($id);

            return response()->json([
                'success' => true,
                'message' => 'Item berhasil dihapus dari keranjang'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    public function clearCart()
    {
        $this->peminjamService->clearCart();

        return response()->json([
            'success' => true,
            'message' => 'Keranjang berhasil dikosongkan'
        ], 200);
    }

    // ==================== CHECKOUT ====================

    public function checkout(CheckoutRequest $request)
    {
        try {
            $result = $this->peminjamService->checkout($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Checkout berhasil! Menunggu konfirmasi petugas.',
                'data' => new CheckoutResource($result)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    // ==================== RIWAYAT ====================

    public function history()
    {
        $loans = $this->peminjamService->getUserLoanHistory(Auth::id());
        $stats = $this->peminjamService->getUserLoanStats(Auth::id());

        return response()->json([
            'success' => true,
            'message' => 'Riwayat peminjaman',
            'data' => [
                'stats' => $stats,
                'loans' => \App\Http\Resources\Admin\LoanResource::collection($loans)
            ]
        ], 200);
    }

    public function loanDetail($id)
    {
        $loan = $this->peminjamService->getLoanById($id, Auth::id());

        if (!$loan) {
            return response()->json([
                'success' => false,
                'message' => 'Peminjaman tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail peminjaman',
            'data' => new \App\Http\Resources\Admin\LoanResource($loan)
        ], 200);
    }

    // ==================== DASHBOARD ====================

    public function dashboard()
    {
        $tools = $this->peminjamService->getAllAvailableTools();
        $loans = $this->peminjamService->getUserLoanHistory(Auth::id());
        $stats = $this->peminjamService->getUserLoanStats(Auth::id());
        $cart = $this->peminjamService->getCart();

        return response()->json([
            'success' => true,
            'message' => 'Dashboard peminjam',
            'data' => [
                'stats' => $stats,
                'available_tools' => ToolResource::collection($tools->take(5)),
                'recent_loans' => \App\Http\Resources\Admin\LoanResource::collection($loans->take(5)),
                'total_available_tools' => $tools->count(),
                'cart_summary' => [
                    'total_items' => count($cart['items']),
                    'total' => $cart['total'],
                    'total_formatted' => $cart['total_formatted'],
                ]
            ]
        ], 200);
    }

    // ==================== TRANSAKSI / PEMBAYARAN ====================

    public function invoice($invoice_number)
    {
        try {
            $invoice = $this->peminjamService->getInvoiceDetail($invoice_number, Auth::id());

            return response()->json([
                'success' => true,
                'message' => 'Detail Invoice',
                'data' => $invoice
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    // ✅ METHOD PAY DIPERBARUI DENGAN EMAIL
    // ✅ METHOD PAY DIPERBARUI DENGAN EMAIL & VARIABEL LENGKAP
    public function pay($invoice_number, Request $request)
    {
        try {
            // 1. Eksekusi service untuk ubah status di database
            $this->peminjamService->payInvoice($invoice_number, Auth::id());

            // 2. Ambil metode pembayaran yang dipilih
            $method = $request->input('method', 'Transfer / E-Wallet / QRIS');

            // 3. AMBIL DATA LENGKAP DARI INVOICE INI
            $loans = \App\Models\Loan::with(['user', 'tool'])
                        ->where('invoice_number', $invoice_number)
                        ->get();

            // Ambil detail data dari item pertama
            $firstLoan = $loans->first();
            $userId = $firstLoan->user_id;
            $userName = $firstLoan->user->name ?? 'Peminjam Adora Cam';
            $tanggalPinjam = $firstLoan->tanggal_pinjam;
            $tanggalSelesai = $firstLoan->tanggal_kembali;
            $statusPembayaran = 'Lunas'; // Karena method ini dipanggil setelah payInvoice berhasil

            // Ambil semua nama alat, gabungkan dengan koma jika lebih dari 1
            $itemName = $loans->pluck('tool.nama_alat')->implode(', ');

            // 4. Ambil email para Admin dan Petugas
            $adminAndPetugasEmails = \App\Models\User::whereIn('role', ['admin', 'petugas'])->pluck('email');

            // 5. Kirim Notifikasi Email dengan Data Lengkap
            if ($adminAndPetugasEmails->isNotEmpty()) {
                Mail::to($adminAndPetugasEmails)->send(new PaymentSuccessfulMail(
                    $userId,
                    $userName,
                    $invoice_number,
                    $itemName,
                    $tanggalPinjam,
                    $tanggalSelesai,
                    $statusPembayaran,
                    $method
                ));
            }

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil! Notifikasi telah dikirim ke admin.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

   public function returnAlat(Request $request, $id)
    {
        try {
            // Validasi Input dari Modal Peminjam
            $validated = $request->validate([
                'kondisi_kembali' => 'required|string',
                'catatan_peminjam' => 'nullable|string',
                // ✅ UPDATE: Diubah menjadi 'file' agar lebih longgar dan tidak gampang error
                'gambar' => 'nullable|file|max:2048'
            ]);

            // Teruskan ke Service
            $result = $this->peminjamService->requestReturn($id, Auth::id(), $validated, $request->file('gambar'));

            return response()->json([
                'success' => true,
                'message' => 'Laporan pengembalian berhasil dikirim.',
                'data' => $result
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // ✅ Menangkap error validasi agar pesannya lebih jelas di frontend
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
