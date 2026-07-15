<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Event extends Model
{
    use HasFactory;

    /**
     * Field yang dapat diisi menggunakan mass assignment.
     */
    protected $fillable = [
        'user_id',
        'kategori_id',
        'judul',
        'deskripsi',
        'lokasi',
        'gambar',
        'tanggal_waktu',
    ];

    /**
     * Konversi tipe data otomatis.
     */
    protected $casts = [
        'tanggal_waktu' => 'datetime',
    ];

    /**
     * Menambahkan accessor ke hasil model.
     */
    protected $appends = [
        'status',
        'image_url',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Event dimiliki oleh satu kategori.
     */
    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    /**
     * Event dibuat oleh satu pengguna.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Event memiliki banyak tiket.
     */
    public function tikets()
    {
        return $this->hasMany(Tiket::class);
    }

    /**
     * Event dapat memiliki banyak order.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    /**
     * Mendapatkan status event berdasarkan tanggal mulai.
     *
     * Upcoming  : event belum dimulai.
     * Ongoing   : event dimulai dalam tiga jam terakhir.
     * Completed : event telah berlangsung lebih dari tiga jam.
     */
    public function getStatusAttribute(): string
    {
        if (!$this->tanggal_waktu) {
            return 'Completed';
        }

        $eventStart = $this->tanggal_waktu->copy();
        $eventEnd = $eventStart->copy()->addHours(3);
        $currentTime = now();

        if ($eventStart->greaterThan($currentTime)) {
            return 'Upcoming';
        }

        if (
            $eventStart->lessThanOrEqualTo($currentTime) &&
            $eventEnd->greaterThan($currentTime)
        ) {
            return 'Ongoing';
        }

        return 'Completed';
    }

    /**
     * Mendapatkan URL gambar event.
     */
    public function getImageUrlAttribute(): string
    {
        $image = trim((string) $this->gambar);

        /*
         * Gunakan langsung jika gambar berasal dari URL eksternal.
         */
        if (
            $image !== '' &&
            filter_var($image, FILTER_VALIDATE_URL)
        ) {
            return $image;
        }

        /*
         * Seeder lama mungkin menyimpan path:
         * storage/konser.jpg
         *
         * Storage Laravel membutuhkan path:
         * konser.jpg
         */
        $storagePath = preg_replace(
            '#^/?storage/#',
            '',
            $image
        );

        if (
            $storagePath !== '' &&
            Storage::disk('public')->exists($storagePath)
        ) {
            return Storage::disk('public')->url($storagePath);
        }

        /*
         * Gambar bawaan apabila file event tidak ditemukan.
         */
        return Storage::disk('public')->url('konser.jpg');
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Memeriksa apakah event sudah mempunyai penjualan.
     */
    public function hasSales(): bool
    {
        return $this->orders()->exists();
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Mengambil event yang belum dimulai.
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where(
            'tanggal_waktu',
            '>',
            now()
        );
    }

    /**
     * Mengambil event yang sedang berlangsung.
     *
     * Durasi event diasumsikan selama tiga jam.
     */
    public function scopeOngoing(Builder $query): Builder
    {
        return $query
            ->where(
                'tanggal_waktu',
                '<=',
                now()
            )
            ->where(
                'tanggal_waktu',
                '>',
                now()->subHours(3)
            );
    }

    /**
     * Mengambil event yang sudah selesai.
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where(
            'tanggal_waktu',
            '<=',
            now()->subHours(3)
        );
    }
}