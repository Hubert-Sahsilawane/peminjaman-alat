<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ToolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'nama_alat' => $isUpdate ? 'sometimes|string|max:255' : 'required|string|max:255',
            'category_id' => $isUpdate ? 'sometimes|exists:categories,id' : 'required|exists:categories,id',
            'stok' => $isUpdate ? 'sometimes|integer|min:0' : 'required|integer|min:0',
            'harga_6jam' => $isUpdate ? 'sometimes|integer|min:0' : 'required|integer|min:0',
            'harga_12jam' => $isUpdate ? 'sometimes|integer|min:0' : 'required|integer|min:0',
            'harga_24jam' => $isUpdate ? 'sometimes|integer|min:0' : 'required|integer|min:0',
            'gambar' => 'nullable|file|max:2048', 
            'deskripsi' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'nama_alat.required' => 'Nama alat wajib diisi',
            'nama_alat.string' => 'Nama alat harus berupa teks',
            'nama_alat.max' => 'Nama alat maksimal 255 karakter',
            'category_id.required' => 'Kategori wajib dipilih',
            'category_id.exists' => 'Kategori tidak valid',
            'stok.required' => 'Stok wajib diisi',
            'stok.integer' => 'Stok harus berupa angka',
            'stok.min' => 'Stok minimal 0',
            'harga_6jam.required' => 'Harga 6 jam wajib diisi',
            'harga_6jam.integer' => 'Harga 6 jam harus berupa angka',
            'harga_6jam.min' => 'Harga 6 jam minimal 0',
            'harga_12jam.required' => 'Harga 12 jam wajib diisi',
            'harga_12jam.integer' => 'Harga 12 jam harus berupa angka',
            'harga_12jam.min' => 'Harga 12 jam minimal 0',
            'harga_24jam.required' => 'Harga 24 jam wajib diisi',
            'harga_24jam.integer' => 'Harga 24 jam harus berupa angka',
            'harga_24jam.min' => 'Harga 24 jam minimal 0',
            'gambar.image' => 'File harus berupa gambar',
            'gambar.mimes' => 'Format gambar harus jpeg, png, atau jpg',
            'gambar.max' => 'Ukuran gambar maksimal 2MB',
        ];
    }
}
