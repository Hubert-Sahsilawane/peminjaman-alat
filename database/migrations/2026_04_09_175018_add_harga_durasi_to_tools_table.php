<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tools', function (Blueprint $table) {
            $table->integer('harga_6jam')->default(0)->after('stok');
            $table->integer('harga_12jam')->default(0)->after('harga_6jam');
            $table->integer('harga_24jam')->default(0)->after('harga_12jam');
        });
    }

    public function down(): void
    {
        Schema::table('tools', function (Blueprint $table) {
            $table->dropColumn(['harga_6jam', 'harga_12jam', 'harga_24jam']);
        });
    }
};
