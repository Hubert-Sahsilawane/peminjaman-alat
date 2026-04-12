<?php

namespace App\Http\Controllers\Api\Peminjam;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Tool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    // Lihat keranjang
    public function index()
    {
        $carts = Cart::with('tool')
            ->where('user_id', Auth::id())
            ->get();

        $total = $carts->sum('subtotal');

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $carts,
                'total' => $total,
                'total_formatted' => 'Rp ' . number_format($total, 0, ',', '.'),
            ]
        ]);
    }

    // Tambah ke keranjang
    public function store(Request $request)
    {
        $request->validate([
            'tool_id' => 'required|exists:tools,id',
            'quantity' => 'required|integer|min:1',
            'durasi' => 'required|in:6jam,12jam,24jam',
        ]);

        $tool = Tool::find($request->tool_id);

        // Ambil harga sesuai durasi
        $hargaSatuan = match ($request->durasi) {
            '6jam' => $tool->harga_6jam,
            '12jam' => $tool->harga_12jam,
            '24jam' => $tool->harga_24jam,
            default => $tool->harga_24jam,
        };

        if ($hargaSatuan <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Harga untuk durasi ini belum tersedia'
            ], 400);
        }

        $subtotal = $hargaSatuan * $request->quantity;

        // Cek apakah sudah ada di keranjang
        $cart = Cart::where('user_id', Auth::id())
            ->where('tool_id', $request->tool_id)
            ->where('durasi', $request->durasi)
            ->first();

        if ($cart) {
            $cart->update([
                'quantity' => $cart->quantity + $request->quantity,
                'subtotal' => $cart->subtotal + $subtotal,
            ]);
        } else {
            Cart::create([
                'user_id' => Auth::id(),
                'tool_id' => $request->tool_id,
                'quantity' => $request->quantity,
                'durasi' => $request->durasi,
                'harga_satuan' => $hargaSatuan,
                'subtotal' => $subtotal,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Item berhasil ditambahkan ke keranjang',
        ]);
    }

    // Update keranjang
    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = Cart::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'Item tidak ditemukan'
            ], 404);
        }

        $cart->update([
            'quantity' => $request->quantity,
            'subtotal' => $cart->harga_satuan * $request->quantity,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Keranjang berhasil diperbarui',
        ]);
    }

    // Hapus item dari keranjang
    public function destroy($id)
    {
        $cart = Cart::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'Item tidak ditemukan'
            ], 404);
        }

        $cart->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item berhasil dihapus dari keranjang',
        ]);
    }

    // Kosongkan keranjang
    public function clear()
    {
        Cart::where('user_id', Auth::id())->delete();

        return response()->json([
            'success' => true,
            'message' => 'Keranjang berhasil dikosongkan',
        ]);
    }
}
