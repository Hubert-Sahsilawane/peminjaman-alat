<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            // Hapus constraint unique dari invoice_number
            $table->dropUnique('loans_invoice_number_unique');

            // Tambah index biasa untuk kecepatan query
            $table->index('invoice_number');
        });
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropIndex(['invoice_number']);
            $table->unique('invoice_number');
        });
    }
};
