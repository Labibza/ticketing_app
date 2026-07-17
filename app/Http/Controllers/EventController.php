<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventFormRequest;
use App\Models\Event;
use App\Models\Kategori;
use App\Models\Lokasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class EventController extends Controller
{
    /**
     * Menampilkan daftar event pada halaman admin.
     */
    public function index(Request $request): View
    {
        /*
         * Sort hanya boleh asc atau desc.
         */
        $sort = strtolower(
            (string) $request->get('sort', 'asc')
        );

        if (!in_array($sort, ['asc', 'desc'], true)) {
            $sort = 'asc';
        }

        $events = Event::query()
            ->with([
                'kategori',
                'lokasiData',
                'tikets',
            ])

            /*
             * Filter berdasarkan kategori.
             */
            ->when(
                $request->filled('kategori_id'),
                function ($query) use ($request) {
                    $query->where(
                        'kategori_id',
                        $request->integer('kategori_id')
                    );
                }
            )

            /*
             * Pencarian berdasarkan judul atau lokasi.
             */
            ->when(
                $request->filled('search'),
                function ($query) use ($request) {
                    $search = trim(
                        (string) $request->get('search')
                    );

                    $query->where(
                        function ($subQuery) use ($search) {
                            $subQuery
                                ->where(
                                    'judul',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhereHas(
                                    'lokasiData',
                                    function ($lokasiQuery) use ($search) {
                                        $lokasiQuery->where(
                                            'nama_lokasi',
                                            'like',
                                            "%{$search}%"
                                        );
                                    }
                                )

                                /*
                                 * Cadangan untuk event lama yang masih
                                 * menggunakan kolom teks lokasi.
                                 */
                                ->orWhere(
                                    'lokasi',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
                }
            )

            /*
             * Urutkan berdasarkan tanggal event.
             */
            ->orderBy(
                'tanggal_waktu',
                $sort
            )

            /*
             * Pagination 10 data per halaman.
             */
            ->paginate(10)
            ->withQueryString();

        $categories = Kategori::query()
            ->orderBy('nama')
            ->get();

        return view(
            'pages.admin.events.index',
            [
                'events' => $events,
                'categories' => $categories,
                'sort' => $sort,
            ]
        );
    }

    /**
     * Menampilkan form tambah event.
     */
    public function create(): View
    {
        $categories = Kategori::query()
            ->orderBy('nama')
            ->get();

        /*
         * Hanya lokasi aktif yang dapat dipilih
         * ketika membuat event baru.
         */
        $lokasis = Lokasi::aktif()
            ->orderBy('nama_lokasi')
            ->get();

        return view(
            'pages.admin.events.create',
            [
                'categories' => $categories,
                'lokasis' => $lokasis,
            ]
        );
    }

    /**
     * Menyimpan event dan tiket baru.
     */
    public function store(
        EventFormRequest $request
    ): RedirectResponse {
        $validated = $request->validated();

        /*
         * Memastikan lokasi yang dipilih tersedia
         * dan berstatus aktif.
         */
        $lokasi = Lokasi::aktif()
            ->findOrFail(
                $validated['lokasi_id']
            );

        /*
         * Gambar default jika tidak ada file upload.
         */
        $imagePath = 'konser.jpg';

        if (
            $request->hasFile('gambar') &&
            $request->file('gambar')->isValid()
        ) {
            $imagePath = $request
                ->file('gambar')
                ->store(
                    'events',
                    'public'
                );
        }

        try {
            DB::transaction(
                function () use (
                    $validated,
                    $imagePath,
                    $request,
                    $lokasi
                ) {
                    $event = Event::create([
                        'user_id' => $request->user()->id,
                        'kategori_id' =>
                            $validated['kategori_id'],

                        /*
                         * Relasi ke tabel lokasi.
                         */
                        'lokasi_id' => $lokasi->id,

                        'judul' => $validated['judul'],
                        'deskripsi' =>
                            $validated['deskripsi'],

                        /*
                         * Kolom lama tetap diisi agar
                         * bagian aplikasi lama tidak rusak.
                         */
                        'lokasi' => $lokasi->nama_lokasi,

                        'gambar' => $imagePath,
                        'tanggal_waktu' =>
                            $validated['tanggal_waktu'],
                    ]);

                    foreach (
                        $validated['tikets']
                        as $ticketData
                    ) {
                        $event
                            ->tikets()
                            ->create(
                                $this->ticketPayload(
                                    $ticketData
                                )
                            );
                    }
                }
            );
        } catch (Throwable $exception) {
            /*
             * Hapus file baru apabila transaksi gagal.
             */
            if ($imagePath !== 'konser.jpg') {
                $this->deleteStoredImage(
                    $imagePath
                );
            }

            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Event gagal ditambahkan. Silakan coba kembali.'
                );
        }

        return redirect()
            ->route('admin.events.index')
            ->with(
                'success',
                'Event dan data tiket berhasil ditambahkan.'
            );
    }

    /**
     * Menampilkan form edit event.
     */
    public function edit(Event $event): View
    {
        /*
         * detail_orders_count digunakan untuk mengetahui
         * tiket yang sudah pernah terjual.
         */
        $event->load([
            'kategori',
            'lokasiData',
            'tikets' => function ($query) {
                $query
                    ->withCount('detailOrders')
                    ->orderBy('id');
            },
        ]);

        $categories = Kategori::query()
            ->orderBy('nama')
            ->get();

        /*
         * Tampilkan:
         * - seluruh lokasi aktif;
         * - lokasi event saat ini walaupun sudah tidak aktif.
         */
        $lokasis = Lokasi::query()
            ->where(
                function ($query) use ($event) {
                    $query->where(
                        'aktif',
                        'Y'
                    );

                    if ($event->lokasi_id) {
                        $query->orWhere(
                            'id',
                            $event->lokasi_id
                        );
                    }
                }
            )
            ->orderBy('nama_lokasi')
            ->get();

        $hasSales = $event->hasSales();

        return view(
            'pages.admin.events.edit',
            [
                'event' => $event,
                'categories' => $categories,
                'lokasis' => $lokasis,
                'hasSales' => $hasSales,
            ]
        );
    }

    /**
     * Memperbarui event dan tiket.
     */
    public function update(
        EventFormRequest $request,
        Event $event
    ): RedirectResponse {
        $validated = $request->validated();

        /*
         * Lokasi boleh digunakan ketika:
         * - masih aktif; atau
         * - merupakan lokasi event saat ini.
         */
        $lokasi = Lokasi::query()
            ->whereKey(
                $validated['lokasi_id']
            )
            ->where(
                function ($query) use ($event) {
                    $query->where(
                        'aktif',
                        'Y'
                    );

                    if ($event->lokasi_id) {
                        $query->orWhere(
                            'id',
                            $event->lokasi_id
                        );
                    }
                }
            )
            ->firstOrFail();

        $event->load('tikets');

        $hasSales = $event->hasSales();

        /*
         * Tanggal event yang sudah memiliki penjualan
         * tidak boleh diubah.
         */
        if (
            $hasSales &&
            $this->dateHasChanged(
                $event->tanggal_waktu,
                $validated['tanggal_waktu']
            )
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'tanggal_waktu' =>
                        'Tanggal dan waktu tidak dapat diubah karena event sudah memiliki penjualan tiket.',
                ]);
        }

        /*
         * Seluruh ID tiket milik event.
         */
        $existingTicketIds = $event
            ->tikets
            ->pluck('id')
            ->map(
                fn ($id) => (int) $id
            );

        $submittedTickets = collect(
            $validated['tikets']
        );

        /*
         * ID tiket lama yang dikirim dari form.
         */
        $submittedTicketIds = $submittedTickets
            ->pluck('id')
            ->filter(
                fn ($id) => filled($id)
            )
            ->map(
                fn ($id) => (int) $id
            )
            ->values();

        /*
         * Mencegah ID tiket dari event lain.
         */
        $invalidTicketIds = $submittedTicketIds
            ->diff($existingTicketIds);

        if ($invalidTicketIds->isNotEmpty()) {
            return back()
                ->withInput()
                ->withErrors([
                    'tikets' =>
                        'Terdapat tiket yang tidak termasuk dalam event ini.',
                ]);
        }

        /*
         * Tiket lama yang tidak lagi dikirim form
         * dianggap akan dihapus.
         */
        $removedTicketIds = $existingTicketIds
            ->diff($submittedTicketIds);

        /*
         * Sesuai perlindungan data transaksi,
         * tiket tidak boleh dihapus jika event
         * sudah memiliki penjualan.
         */
        if (
            $hasSales &&
            $removedTicketIds->isNotEmpty()
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'tikets' =>
                        'Tiket tidak dapat dihapus karena event sudah memiliki penjualan.',
                ]);
        }

        $oldImagePath = $event->gambar;
        $newImagePath = $oldImagePath;
        $hasNewImage = false;

        /*
         * Simpan gambar baru jika diunggah.
         */
        if (
            $request->hasFile('gambar') &&
            $request->file('gambar')->isValid()
        ) {
            $newImagePath = $request
                ->file('gambar')
                ->store(
                    'events',
                    'public'
                );

            $hasNewImage = true;
        }

        try {
            DB::transaction(
                function () use (
                    $event,
                    $validated,
                    $lokasi,
                    $newImagePath,
                    $submittedTickets,
                    $removedTicketIds,
                    $hasSales
                ) {
                    /*
                     * Perbarui data utama event.
                     */
                    $event->update([
                        'kategori_id' =>
                            $validated['kategori_id'],

                        'lokasi_id' => $lokasi->id,

                        'judul' => $validated['judul'],
                        'deskripsi' =>
                            $validated['deskripsi'],

                        /*
                         * Sinkronkan kolom lokasi teks.
                         */
                        'lokasi' => $lokasi->nama_lokasi,

                        'gambar' => $newImagePath,
                        'tanggal_waktu' =>
                            $validated['tanggal_waktu'],
                    ]);

                    /*
                     * Update tiket lama dan buat tiket baru.
                     */
                    foreach (
                        $submittedTickets
                        as $ticketData
                    ) {
                        $payload = $this->ticketPayload(
                            $ticketData
                        );

                        if (!empty($ticketData['id'])) {
                            $ticket = $event
                                ->tikets()
                                ->whereKey(
                                    (int) $ticketData['id']
                                )
                                ->firstOrFail();

                            $ticket->update(
                                $payload
                            );
                        } else {
                            $event
                                ->tikets()
                                ->create(
                                    $payload
                                );
                        }
                    }

                    /*
                     * Tiket hanya dapat dihapus apabila
                     * event belum memiliki penjualan.
                     */
                    if (
                        !$hasSales &&
                        $removedTicketIds->isNotEmpty()
                    ) {
                        $event
                            ->tikets()
                            ->whereKey(
                                $removedTicketIds->all()
                            )
                            ->delete();
                    }
                }
            );
        } catch (Throwable $exception) {
            /*
             * Hapus gambar baru apabila transaksi gagal.
             */
            if ($hasNewImage) {
                $this->deleteStoredImage(
                    $newImagePath
                );
            }

            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Event gagal diperbarui. Silakan coba kembali.'
                );
        }

        /*
         * Hapus gambar lama hanya jika gambar baru
         * berhasil disimpan dan database berhasil diperbarui.
         */
        if (
            $hasNewImage &&
            $newImagePath !== $oldImagePath
        ) {
            $this->deleteStoredImage(
                $oldImagePath
            );
        }

        return redirect()
            ->route('admin.events.index')
            ->with(
                'success',
                'Event dan data tiket berhasil diperbarui.'
            );
    }

    /**
     * Menghapus event.
     */
    public function destroy(
        Event $event
    ): RedirectResponse {
        /*
         * Event yang memiliki transaksi
         * tidak boleh dihapus.
         */
        if ($event->hasSales()) {
            return redirect()
                ->route('admin.events.index')
                ->with(
                    'error',
                    'Event tidak dapat dihapus karena sudah memiliki penjualan tiket.'
                );
        }

        $imagePath = $event->gambar;

        try {
            DB::transaction(
                function () use ($event) {
                    /*
                     * Tiket akan ikut terhapus jika
                     * foreign key menggunakan cascade.
                     */
                    $event->delete();
                }
            );
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('admin.events.index')
                ->with(
                    'error',
                    'Event gagal dihapus. Silakan coba kembali.'
                );
        }

        /*
         * Hapus file gambar setelah database berhasil.
         */
        $this->deleteStoredImage(
            $imagePath
        );

        return redirect()
            ->route('admin.events.index')
            ->with(
                'success',
                'Event berhasil dihapus.'
            );
    }

    /**
     * Menampilkan detail event publik.
     */
    public function show(Event $event): View
    {
        $event->load([
            'kategori',
            'lokasiData',
            'tikets' => function ($query) {
                $query->orderBy('harga');
            },
        ]);

        /*
         * Event terkait:
         * - kategori sama;
         * - bukan event yang sedang dibuka;
         * - masih akan berlangsung;
         * - maksimal empat data.
         */
        $relatedEvents = Event::query()
            ->with([
                'kategori',
                'lokasiData',
                'tikets',
            ])
            ->where(
                'kategori_id',
                $event->kategori_id
            )
            ->where(
                'id',
                '!=',
                $event->id
            )
            ->upcoming()
            ->orderBy('tanggal_waktu')
            ->limit(4)
            ->get()
            ->map(
                function (Event $relatedEvent) {
                    $relatedEvent->setAttribute(
                        'tikets_min_harga',
                        $relatedEvent
                            ->tikets
                            ->min('harga')
                    );

                    return $relatedEvent;
                }
            );

        return view(
            'events.show',
            [
                'event' => $event,
                'relatedEvents' => $relatedEvents,
            ]
        );
    }

    /**
     * Menormalisasi data tiket sebelum disimpan.
     *
     * @param array<string, mixed> $ticketData
     * @return array<string, mixed>
     */
    private function ticketPayload(
        array $ticketData
    ): array {
        return [
            'tipe' => strtolower(
                trim(
                    (string) $ticketData['tipe']
                )
            ),

            'harga' => (float) $ticketData['harga'],

            'stok' => (int) $ticketData['stok'],
        ];
    }

    /**
     * Memeriksa apakah tanggal event berubah.
     */
    private function dateHasChanged(
        Carbon|string|null $currentDate,
        string $newDate
    ): bool {
        if (!$currentDate) {
            return true;
        }

        $current = Carbon::parse(
            $currentDate
        )->format('Y-m-d H:i');

        $submitted = Carbon::parse(
            $newDate
        )->format('Y-m-d H:i');

        return $current !== $submitted;
    }

    /**
     * Menghapus gambar lokal dari public storage.
     *
     * Gambar default dan URL eksternal tidak dihapus.
     */
    private function deleteStoredImage(
        ?string $imagePath
    ): void {
        if (!$imagePath) {
            return;
        }

        /*
         * URL eksternal tidak berada
         * pada storage lokal.
         */
        if (
            filter_var(
                $imagePath,
                FILTER_VALIDATE_URL
            )
        ) {
            return;
        }

        /*
         * Normalisasi data lama:
         * storage/events/file.jpg
         * menjadi:
         * events/file.jpg
         */
        $normalizedPath = preg_replace(
            '#^/?storage/#',
            '',
            trim($imagePath)
        );

        if (!$normalizedPath) {
            return;
        }

        /*
         * Jangan hapus gambar default.
         */
        if (
            in_array(
                $normalizedPath,
                [
                    'konser.jpg',
                    'events/konser.jpg',
                ],
                true
            )
        ) {
            return;
        }

        if (
            Storage::disk('public')
                ->exists($normalizedPath)
        ) {
            Storage::disk('public')
                ->delete($normalizedPath);
        }
    }
}
