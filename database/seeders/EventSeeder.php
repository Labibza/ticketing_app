<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Lokasi;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Mengisi data awal tabel events.
     */
    public function run(): void
    {
        /*
         * Mengambil lokasi dari tabel lokasi.
         * LokasiSeeder harus dijalankan sebelum EventSeeder.
         */
        $stadionUtama = Lokasi::where(
            'nama_lokasi',
            'Stadion Utama'
        )->firstOrFail();

        $galeriSeniKota = Lokasi::where(
            'nama_lokasi',
            'Galeri Seni Kota'
        )->firstOrFail();

        $tamanKota = Lokasi::where(
            'nama_lokasi',
            'Taman Kota'
        )->firstOrFail();

        $events = [
            [
                'user_id' => 1,
                'kategori_id' => 1,
                'lokasi_id' => $stadionUtama->id,
                'judul' => 'Konser Musik Rock',
                'deskripsi' =>
                    'Nikmati malam penuh energi dengan band rock terkenal.',
                'tanggal_waktu' => now()
                    ->addDays(10)
                    ->setTime(19, 0),
                'lokasi' => $stadionUtama->nama_lokasi,
                'gambar' => 'events/konser_rock.jpg',
            ],
            [
                'user_id' => 1,
                'kategori_id' => 2,
                'lokasi_id' => $galeriSeniKota->id,
                'judul' => 'Pameran Seni Kontemporer',
                'deskripsi' =>
                    'Jelajahi karya seni modern dari seniman lokal dan internasional.',
                'tanggal_waktu' => now()
                    ->addDays(15)
                    ->setTime(10, 0),
                'lokasi' => $galeriSeniKota->nama_lokasi,
                'gambar' => 'events/pameran_seni.jpg',
            ],
            [
                'user_id' => 1,
                'kategori_id' => 3,
                'lokasi_id' => $tamanKota->id,
                'judul' => 'Festival Makanan Internasional',
                'deskripsi' =>
                    'Cicipi berbagai hidangan lezat dari seluruh dunia.',
                'tanggal_waktu' => now()
                    ->addDays(20)
                    ->setTime(12, 0),
                'lokasi' => $tamanKota->nama_lokasi,
                'gambar' => 'events/festival_makanan.jpg',
            ],
        ];

        foreach ($events as $eventData) {
            /*
             * Mencegah event dengan judul sama
             * dibuat berulang kali saat seeder dijalankan.
             */
            Event::updateOrCreate(
                [
                    'judul' => $eventData['judul'],
                ],
                $eventData
            );
        }
    }
}
