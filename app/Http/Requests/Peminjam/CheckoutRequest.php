<?php

namespace App\Http\Requests\Peminjam;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tanggal_pinjam' => 'required|date|after_or_equal:today',
            'tanggal_kembali' => 'required|date|after:tanggal_pinjam',
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal_pinjam.required' => 'Tanggal pinjam wajib diisi',
            'tanggal_pinjam.after_or_equal' => 'Tanggal pinjam tidak boleh kurang dari hari ini',
            'tanggal_kembali.required' => 'Tanggal kembali wajib diisi',
            'tanggal_kembali.after' => 'Tanggal kembali harus setelah tanggal pinjam',
        ];
    }
}
