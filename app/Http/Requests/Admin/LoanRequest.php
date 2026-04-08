<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class LoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'user_id' => $isUpdate ? 'sometimes|exists:users,id' : 'required|exists:users,id',
            'tool_id' => $isUpdate ? 'sometimes|exists:tools,id' : 'required|exists:tools,id',
            'tanggal_pinjam' => $isUpdate ? 'sometimes|date' : 'required|date',
            'tanggal_kembali' => $isUpdate ? 'sometimes|date|after_or_equal:tanggal_pinjam' : 'required|date|after_or_equal:tanggal_pinjam',
            'status' => $isUpdate ? 'sometimes|in:pending,disetujui,ditolak,kembali' : 'nullable|in:pending,disetujui,ditolak,kembali',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'Peminjam wajib dipilih',
            'user_id.exists' => 'Peminjam tidak valid',
            'tool_id.required' => 'Alat wajib dipilih',
            'tool_id.exists' => 'Alat tidak valid',
            'tanggal_pinjam.required' => 'Tanggal pinjam wajib diisi',
            'tanggal_pinjam.date' => 'Format tanggal pinjam tidak valid',
            'tanggal_kembali.required' => 'Tanggal kembali wajib diisi',
            'tanggal_kembali.date' => 'Format tanggal kembali tidak valid',
            'tanggal_kembali.after_or_equal' => 'Tanggal kembali harus setelah atau sama dengan tanggal pinjam',
            'status.in' => 'Status tidak valid',
        ];
    }
}
