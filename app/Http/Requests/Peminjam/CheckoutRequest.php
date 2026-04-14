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
            'tanggal_pinjam' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    if (strtotime($value) < strtotime(now()->toDateString())) {
                        $fail('Tanggal ambil alat tidak boleh di masa lampau.');
                    }
                }
            ],
            'tanggal_kembali' => [
                'required',
                'date',
                // ✅ UBAH BAGIAN INI: Boleh hari yang sama dengan tanggal pinjam
                'after_or_equal:tanggal_pinjam',
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
            'tanggal_pinjam.required' => 'Tanggal ambil alat wajib diisi.',
            'tanggal_pinjam.date' => 'Format tanggal ambil tidak valid.',
            'tanggal_kembali.required' => 'Rencana kembali wajib diisi.',
            'tanggal_kembali.date' => 'Format tanggal kembali tidak valid.',
            // ✅ UPDATE PESAN ERROR
            'tanggal_kembali.after_or_equal' => 'Tanggal kembali tidak boleh sebelum tanggal ambil alat.',
        ];
    }
}
