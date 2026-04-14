<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Jalankan migration (Membuat Trigger)
     */
    public function up(): void
    {
        // Hapus trigger jika sebelumnya sudah ada agar tidak bentrok
        DB::unprepared("DROP TRIGGER IF EXISTS trg_calculate_denda");

        // Buat Trigger baru: BEFORE UPDATE pada tabel 'loans'
        DB::unprepared("
            CREATE TRIGGER trg_calculate_denda
            BEFORE UPDATE ON loans
            FOR EACH ROW
            BEGIN
                DECLARE keterlambatan INT;

                -- Cek apakah admin sedang memproses pengembalian (status diubah jadi 'kembali')
                IF NEW.status = 'kembali' AND OLD.status != 'kembali' THEN

                    -- Hitung selisih hari antara Hari Ini (CURDATE) dengan batas Tanggal Kembali
                    SET keterlambatan = DATEDIFF(CURDATE(), OLD.tanggal_kembali);

                    -- Jika telat (lebih dari 0 hari)
                    IF keterlambatan > 0 THEN
                        -- Denda 200rb per hari
                        SET NEW.denda = keterlambatan * 200000;
                        -- Tambahkan denda ke total bayar
                        SET NEW.total_bayar = OLD.subtotal + NEW.denda;
                    ELSE
                        -- Jika tepat waktu / tidak telat
                        SET NEW.denda = 0;
                        SET NEW.total_bayar = OLD.subtotal;
                    END IF;

                END IF;
            END
        ");
    }

    /**
     * Kembalikan (Reverse) migration
     */
    public function down(): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS trg_calculate_denda");
    }
};
