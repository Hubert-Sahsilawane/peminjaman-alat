<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Http\Resources\Admin\UserResource;
use App\Services\Admin\UserService;
use Illuminate\Support\Facades\Auth; // ✅ TAMBAHKAN INI
class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index()
    {
        $users = $this->userService->getAllUsers(request('search'), 10);

        return response()->json([
            'success' => true,
            'message' => 'Daftar user berhasil diambil',
            'data' => [
                'users' => UserResource::collection($users),
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                ]
            ]
        ], 200);
    }

    public function store(UserRequest $request)
    {
        $user = $this->userService->createUser($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'User berhasil ditambahkan',
            'data' => new UserResource($user)
        ], 201);
    }

    public function show($id)
    {
        $user = $this->userService->getUserById($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail user',
            'data' => new UserResource($user)
        ], 200);
    }

    public function update(UserRequest $request, $id)
    {
        $user = $this->userService->getUserById($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan'
            ], 404);
        }

        $user = $this->userService->updateUser($user, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'User berhasil diperbarui',
            'data' => new UserResource($user)
        ], 200);
    }

    public function destroy($id)
{
    $user = $this->userService->getUserById($id);

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'User tidak ditemukan'
        ], 404);
    }

    // ✅ Ganti auth()->id() dengan Auth::id()
    $result = $this->userService->deleteUser($user, Auth::id());

    if (!$result) {
        return response()->json([
            'success' => false,
            'message' => 'Anda tidak dapat menghapus akun sendiri'
        ], 400);
    }

    return response()->json([
        'success' => true,
        'message' => 'User berhasil dihapus'
    ], 200);
}

    public function roles()
    {
        $roles = $this->userService->getRoles();

        return response()->json([
            'success' => true,
            'message' => 'Daftar role',
            'data' => $roles
        ], 200);
    }
}
