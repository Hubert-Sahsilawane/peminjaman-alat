<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserRequest;
use App\Http\Resources\User\UserResource;
use App\Services\User\UserService;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    // ==================== PUBLIC / PROFILE ====================

    public function profile()
    {
        $user = Auth::user();
        return response()->json([
            'success' => true,
            'message' => 'Profil user',
            'data' => new UserResource($user)
        ], 200);
    }

    public function updateProfile(UserRequest $request)
    {
        $user = Auth::user();
        $user = $this->userService->updateUser($user, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui',
            'data' => new UserResource($user)
        ], 200);
    }

    // ==================== ADMIN ONLY ====================

    public function index()
    {
        $this->checkRole('admin');
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
        $this->checkRole('admin');
        $user = $this->userService->createUser($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'User berhasil ditambahkan',
            'data' => new UserResource($user)
        ], 201);
    }

    public function show($id)
    {
        $this->checkRole('admin');
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
        $this->checkRole('admin');
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
        $this->checkRole('admin');
        $user = $this->userService->getUserById($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan'
            ], 404);
        }

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
        $this->checkRole('admin');
        $roles = $this->userService->getRoles();

        return response()->json([
            'success' => true,
            'message' => 'Daftar role',
            'data' => $roles
        ], 200);
    }

    private function checkRole($role)
    {
        if (Auth::user()->getRoleNames()->first() !== $role) {
            abort(403, 'Akses ditolak');
        }
    }
}
