<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel events.
     */
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained();

            $table->foreignId('kategori_id')
                ->constrained();

            $table->string('judul');
            $table->text('deskripsi');
            $table->string('lokasi');
            $table->string('gambar');
            $table->dateTime('tanggal_waktu');
            $table->timestamps();
        });
    }

    /**
     * Menghapus tabel events.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};