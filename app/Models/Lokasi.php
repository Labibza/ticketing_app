<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lokasi extends Model
{
    use HasFactory;

    /**
     * Nama tabel dibuat singular sesuai ketentuan soal.
     */
    protected $table = 'lokasi';

    /**
     * Field yang dapat diisi menggunakan mass assignment.
     */
    protected $fillable = [
        'nama_lokasi',
        'aktif',
    ];

    /**
     * Scope untuk mengambil lokasi yang aktif.
     */
    public function scopeAktif(
        Builder $query
    ): Builder {
        return $query->where(
            'aktif',
            'Y'
        );
    }

    /**
     * Scope untuk mengambil lokasi yang tidak aktif.
     */
    public function scopeTidakAktif(
        Builder $query
    ): Builder {
        return $query->where(
            'aktif',
            'N'
        );
    }

    /**
     * Mendapatkan label status lokasi.
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->aktif === 'Y'
            ? 'Aktif'
            : 'Tidak Aktif';
    }
}
