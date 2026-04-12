<?php

namespace App\Http\Controllers\Api\Peminjam;

use App\Http\Controllers\Controller;
use App\Http\Requests\Peminjam\CheckoutRequest;
use App\Http\Resources\Peminjam\CheckoutResource;
use App\Services\Peminjam\PeminjamService;

class CheckoutController extends Controller
{
    protected $peminjamService;

    public function __construct(PeminjamService $peminjamService)
    {
        $this->peminjamService = $peminjamService;
    }

    public function checkout(CheckoutRequest $request)
{
    try {
        $result = $this->peminjamService->checkout($request->validated());

        return new CheckoutResource($result);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 400);
    }
}
}
