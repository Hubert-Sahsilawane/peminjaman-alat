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

        // ✅ PERBAIKAN: Tangkap ID baik dari parameter 'user' maupun 'id'
        $userId = $this->route('user') ?? $this->route('id');

        // Jika yang diakses adalah endpoint update profile sendiri
        if ($this->is('api/profile*')) {
            $userId = $this->user()->id;
        }

        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                // ✅ PERBAIKAN: Email harus unik, tapi abaikan pengecekan untuk ID user ini sendiri saat update
                Rule::unique('users', 'email')->ignore($userId)
            ],
            'password' => $isUpdate ? 'nullable|string|min:6' : 'required|string|min:6',

            // ✅ PERBAIKAN: Role wajib ada jika diakses oleh admin, tapi diabaikan jika user update profile sendiri
            'role' => $this->is('api/profile*')
                ? 'sometimes|in:admin,petugas,peminjam'
                : 'required|string|in:admin,petugas,peminjam',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.string' => 'Nama harus berupa teks.',
            'name.max' => 'Nama maksimal 255 karakter.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar. Silakan gunakan email lain.',
            'password.required' => 'Password wajib diisi untuk akun baru.',
            'password.min' => 'Password minimal harus 6 karakter.',
            'role.required' => 'Role/Hak Akses wajib dipilih.',
            'role.in' => 'Role yang dipilih tidak valid.',
        ];
    }
}
