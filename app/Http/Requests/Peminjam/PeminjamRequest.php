<?php

namespace App\Http\Requests\Peminjam;

use Illuminate\Foundation\Http\FormRequest;

class PeminjamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tool_id' => 'required|exists:tools,id',
            'tanggal_kembali' => 'required|date|after:today',
        ];
    }

    public function messages(): array
    {
        return [
            'tool_id.required' => 'Alat wajib dipilih',
            'tool_id.exists' => 'Alat tidak valid',
            'tanggal_kembali.required' => 'Tanggal rencana kembali wajib diisi',
            'tanggal_kembali.date' => 'Format tanggal tidak valid',
            'tanggal_kembali.after' => 'Tanggal kembali harus setelah hari ini',
        ];
    }
}
