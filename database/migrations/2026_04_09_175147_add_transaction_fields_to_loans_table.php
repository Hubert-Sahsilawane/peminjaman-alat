<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->string('invoice_number')->unique()->nullable()->after('id');
            $table->enum('durasi', ['6jam', '12jam', '24jam'])->default('24jam')->after('stok');
            $table->integer('harga_satuan')->default(0)->after('durasi');
            $table->integer('subtotal')->default(0)->after('harga_satuan');
            $table->integer('denda')->default(0)->after('subtotal');
            $table->integer('total_bayar')->default(0)->after('denda');
            $table->enum('status_pembayaran', ['pending', 'lunas'])->default('pending')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn(['invoice_number', 'durasi', 'harga_satuan', 'subtotal', 'denda', 'total_bayar', 'status_pembayaran']);
        });
    }
};
