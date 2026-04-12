<?php

namespace App\Http\Requests\Peminjam;

use Illuminate\Foundation\Http\FormRequest;

class CartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT');

        return [
            'tool_id' => $isUpdate ? 'sometimes|exists:tools,id' : 'required|exists:tools,id',
            'quantity' => 'required|integer|min:1',
            'durasi' => $isUpdate ? 'sometimes|in:6jam,12jam,24jam' : 'required|in:6jam,12jam,24jam',
        ];
    }

    public function messages(): array
    {
        return [
            'tool_id.required' => 'Alat wajib dipilih',
            'tool_id.exists' => 'Alat tidak valid',
            'quantity.required' => 'Jumlah wajib diisi',
            'quantity.min' => 'Jumlah minimal 1',
            'durasi.required' => 'Durasi wajib dipilih',
            'durasi.in' => 'Durasi tidak valid',
        ];
    }
}
