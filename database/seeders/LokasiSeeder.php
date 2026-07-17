<?php

namespace Database\Seeders;

use App\Models\Lokasi;
use Illuminate\Database\Seeder;

class LokasiSeeder extends Seeder
{
    /**
     * Mengisi data awal tabel lokasi.
     */
    public function run(): void
    {
        $lokasiData = [
            [
                'id' => 1,
                'nama_lokasi' => 'Stadion Utama',
                'aktif' => 'Y',
            ],
            [
                'id' => 2,
                'nama_lokasi' => 'Galeri Seni Kota',
                'aktif' => 'Y',
            ],
            [
                'id' => 3,
                'nama_lokasi' => 'Taman Kota',
                'aktif' => 'Y',
            ],
        ];

        foreach ($lokasiData as $data) {
            Lokasi::updateOrCreate(
                [
                    'id' => $data['id'],
                ],
                [
                    'nama_lokasi' => $data['nama_lokasi'],
                    'aktif' => $data['aktif'],
                ]
            );
        }
    }
}
