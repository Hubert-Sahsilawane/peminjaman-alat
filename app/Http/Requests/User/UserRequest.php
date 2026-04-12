<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
        $userId = $this->route('user') ? $this->route('user') : null;

        // Untuk update profile, ambil dari user yang login
        if ($this->is('api/profile*')) {
            $userId = $this->user()->id;
        }

        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($userId)
            ],
            'password' => $isUpdate ? 'nullable|min:6' : 'required|min:6',
            'role' => $isUpdate ? 'sometimes|in:admin,petugas,peminjam' : 'required|in:admin,petugas,peminjam',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi',
            'name.string' => 'Nama harus berupa teks',
            'name.max' => 'Nama maksimal 255 karakter',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah terdaftar',
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal 6 karakter',
            'role.required' => 'Role wajib dipilih',
            'role.in' => 'Role tidak valid',
        ];
    }
}
