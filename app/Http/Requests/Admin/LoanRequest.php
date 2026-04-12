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
            'stok' => $isUpdate ? 'sometimes|integer|min:1' : 'required|integer|min:1',
            'durasi' => $isUpdate ? 'sometimes|in:6jam,12jam,24jam' : 'required|in:6jam,12jam,24jam',  // ✅ TAMBAHKAN
            'tanggal_pinjam' => [
                $isUpdate ? 'sometimes' : 'required',
                'date',
                function ($attribute, $value, $fail) {
                    if (strtotime($value) < strtotime(now()->toDateString())) {
                        $fail('Tanggal pinjam tidak boleh masa lampau.');
                    }
                }
            ],
            'tanggal_kembali' => [
                $isUpdate ? 'sometimes' : 'required',
                'date',
                'after_or_equal:tanggal_pinjam',
                function ($attribute, $value, $fail) {
                    $maxDate = now()->addDays(30)->toDateString();
                    if (strtotime($value) > strtotime($maxDate)) {
                        $fail('Tanggal kembali maksimal 30 hari dari hari ini.');
                    }
                }
            ],
            'status' => $isUpdate ? 'sometimes|in:pending,disetujui,ditolak,kembali,telat' : 'nullable|in:pending,disetujui,ditolak,kembali,telat',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'Peminjam wajib dipilih',
            'user_id.exists' => 'Peminjam tidak valid',
            'tool_id.required' => 'Alat wajib dipilih',
            'tool_id.exists' => 'Alat tidak valid',
            'stok.required' => 'Jumlah alat yang dipinjam wajib diisi',
            'stok.integer' => 'Jumlah alat harus berupa angka',
            'stok.min' => 'Jumlah alat minimal 1',
            'durasi.required' => 'Durasi wajib dipilih',
            'durasi.in' => 'Durasi tidak valid',
            'tanggal_pinjam.required' => 'Tanggal pinjam wajib diisi',
            'tanggal_pinjam.date' => 'Format tanggal pinjam tidak valid',
            'tanggal_kembali.required' => 'Tanggal kembali wajib diisi',
            'tanggal_kembali.date' => 'Format tanggal kembali tidak valid',
            'tanggal_kembali.after_or_equal' => 'Tanggal kembali harus setelah atau sama dengan tanggal pinjam',
            'status.in' => 'Status tidak valid',
        ];
    }
}
