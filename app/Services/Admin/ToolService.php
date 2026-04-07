<?php

namespace App\Services\Admin;

use App\Models\Tool;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\LengthAwarePaginator;

class ToolService
{
    public function getAllTools(int $perPage = 10): LengthAwarePaginator
    {
        return Tool::with('category')->latest()->paginate($perPage);
    }

    public function getToolById(int $id): ?Tool
    {
        return Tool::with('category')->find($id);
    }

    public function createTool(array $data, $gambarFile = null): Tool
    {
        if ($gambarFile) {
            $data['gambar'] = $gambarFile->store('tools', 'public');
        }

        $tool = Tool::create($data);

        if (Auth::check()) {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'Tambah Alat',
                'description' => "Menambahkan alat baru: {$tool->nama_alat} (Stok: {$tool->stok})"
            ]);
        }

        return $tool;
    }

    public function updateTool(Tool $tool, array $data, $gambarFile = null): Tool
    {
        // Handle upload gambar baru
        if ($gambarFile) {
            // Hapus gambar lama jika ada
            if ($tool->gambar && Storage::disk('public')->exists($tool->gambar)) {
                Storage::disk('public')->delete($tool->gambar);
            }
            $data['gambar'] = $gambarFile->store('tools', 'public');
        }

        // Hanya update field yang dikirim
        $tool->fill($data);

        // Cek apakah ada perubahan
        if ($tool->isDirty()) {
            $tool->save();

            if (Auth::check()) {
                ActivityLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'Update Alat',
                    'description' => "Memperbarui data alat: {$tool->nama_alat}"
                ]);
            }
        }

        return $tool->fresh();
    }

    public function deleteTool(Tool $tool): bool
    {
        // Hapus file gambar dari storage jika ada
        if ($tool->gambar && Storage::disk('public')->exists($tool->gambar)) {
            Storage::disk('public')->delete($tool->gambar);
        }

        $toolName = $tool->nama_alat;
        $tool->delete();

        if (Auth::check()) {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'Hapus Alat',
                'description' => "Menghapus alat: {$toolName}"
            ]);
        }

        return true;
    }

    public function updateStock(Tool $tool, int $quantity, string $operation = 'decrement'): Tool
    {
        if ($operation === 'decrement') {
            $tool->decrement('stok', $quantity);
        } else {
            $tool->increment('stok', $quantity);
        }

        return $tool->fresh();
    }

    public function getAvailableTools()
    {
        return Tool::with('category')->where('stok', '>', 0)->get();
    }

    public function getAllToolsForSelect(): \Illuminate\Database\Eloquent\Collection
    {
        return Tool::all(['id', 'nama_alat', 'stok']);
    }
}
