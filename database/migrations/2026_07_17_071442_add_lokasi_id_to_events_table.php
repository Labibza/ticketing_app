<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan relasi lokasi pada tabel events.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table
                ->foreignId('lokasi_id')
                ->nullable()
                ->after('kategori_id')
                ->constrained('lokasi')
                ->restrictOnDelete();
        });
    }

    /**
     * Menghapus relasi lokasi dari tabel events.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign([
                'lokasi_id',
            ]);

            $table->dropColumn('lokasi_id');
        });
    }
};
