<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            // Tambah kolom quantity (jumlah alat yang dipinjam)
            $table->integer('quantity')->default(1)->after('tool_id');

            // Ubah enum status tambahkan 'telat'
            $table->enum('status', ['pending', 'disetujui', 'ditolak', 'kembali', 'telat'])
                  ->default('pending')->change();
        });
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn('quantity');
            $table->enum('status', ['pending', 'disetujui', 'ditolak', 'kembali'])
                  ->default('pending')->change();
        });
    }
};
