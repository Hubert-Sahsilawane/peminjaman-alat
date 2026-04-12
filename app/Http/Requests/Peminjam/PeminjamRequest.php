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
            'stok' => 'required|integer|min:1',  // ← Ganti quantity ke stok
            'tanggal_kembali' => [
                'required',
                'date',
                'after:today',
                function ($attribute, $value, $fail) {
                    $maxDate = now()->addDays(30)->toDateString();
                    if (strtotime($value) > strtotime($maxDate)) {
                        $fail('Tanggal kembali maksimal 30 hari dari hari ini.');
                    }
                }
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'tool_id.required' => 'Alat wajib dipilih',
            'tool_id.exists' => 'Alat tidak valid',
            'stok.required' => 'Jumlah alat yang dipinjam wajib diisi',
            'stok.integer' => 'Jumlah alat harus berupa angka',
            'stok.min' => 'Jumlah alat minimal 1',
            'tanggal_kembali.required' => 'Tanggal rencana kembali wajib diisi',
            'tanggal_kembali.date' => 'Format tanggal tidak valid',
            'tanggal_kembali.after' => 'Tanggal kembali harus setelah hari ini',
        ];
    }
}
