<?php

namespace App\Services\Admin;

use App\Models\Category;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryService
{
    public function getAllCategories(int $perPage = 10): LengthAwarePaginator
    {
        return Category::withCount('tools')->latest()->paginate($perPage);
    }

    public function getCategoryById(int $id): ?Category
    {
        return Category::withCount('tools')->find($id);
    }

    public function createCategory(array $data): Category
    {
        $category = Category::create($data);

        if (Auth::check()) {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'Tambah Kategori',
                'description' => "Menambah kategori: {$category->nama_kategori}"
            ]);
        }

        return $category;
    }

    public function updateCategory(Category $category, array $data): Category
    {
        $oldName = $category->nama_kategori;
        $category->update($data);

        if (Auth::check()) {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'Update Kategori',
                'description' => "Mengubah kategori '{$oldName}' menjadi '{$category->nama_kategori}'"
            ]);
        }

        return $category;
    }

    public function deleteCategory(Category $category): array
    {
        // Cek apakah kategori masih memiliki alat
        $toolsCount = $category->tools()->count();

        if ($toolsCount > 0) {
            return [
                'success' => false,
                'message' => "Kategori tidak bisa dihapus karena masih memiliki {$toolsCount} data Alat. Hapus atau pindahkan alat terlebih dahulu."
            ];
        }

        $categoryName = $category->nama_kategori;
        $category->delete();

        if (Auth::check()) {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'Hapus Kategori',
                'description' => "Menghapus kategori: {$categoryName}"
            ]);
        }

        return ['success' => true, 'message' => 'Kategori berhasil dihapus.'];
    }

    public function getAllCategoriesForSelect(): \Illuminate\Database\Eloquent\Collection
    {
        return Category::all(['id', 'nama_kategori']);
    }
}
