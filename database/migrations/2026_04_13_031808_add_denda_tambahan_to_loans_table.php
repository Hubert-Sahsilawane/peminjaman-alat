<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->integer('denda_rusak')->default(0)->after('denda');
            $table->integer('denda_hilang')->default(0)->after('denda_rusak');
            $table->string('keterangan_kondisi')->nullable()->after('denda_hilang');
        });
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn(['denda_rusak', 'denda_hilang', 'keterangan_kondisi']);
        });
    }
};
