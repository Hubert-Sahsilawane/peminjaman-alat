<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\Auth\RegisterResource;
use App\Services\Auth\RegisterService;

class RegisterController extends Controller
{
    protected RegisterService $registerService;

    public function __construct(RegisterService $registerService)
    {
        $this->registerService = $registerService;
    }

    public function register(RegisterRequest $request)
    {
        $result = $this->registerService->attemptRegister($request->validated());

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Registrasi gagal'
            ], 400);
        }

        return new RegisterResource($result);
    }
}
