<?php

namespace App\Http\Controllers\Api\Peminjam;

use App\Http\Controllers\Controller;
use App\Http\Requests\Peminjam\CartRequest;
use App\Http\Requests\Peminjam\CheckoutRequest;
use App\Http\Resources\Admin\ToolResource;
use App\Http\Resources\Peminjam\CartResource;
use App\Http\Resources\Peminjam\CheckoutResource;
use App\Services\Peminjam\PeminjamService;
use Illuminate\Support\Facades\Auth;

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
}
