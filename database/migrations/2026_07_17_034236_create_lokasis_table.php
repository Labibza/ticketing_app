<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use function Laravel\Prompts\table;

return new class extends Migration
{
    /**
     * Membuat Tabel Lokasi.
     */
    public function up(): void
    {
        Schema::create('lokasi', function (Blueprint $table) {
            $table->id();
            $table->string(
                'nama_lokasi',
                255
            );

            /*
            * Y = aktif
            * N = tidak aktif
            */
            $table->char(
                'aktif',
                1
            )->default('Y');

            $table->timestamps();
        });
    }

    /**
     * Menghapus tabel Lokasi
     */
    public function down(): void
    {
        Schema::dropIfExists('lokasi');
    }
};
