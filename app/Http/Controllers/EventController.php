<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventFormRequest;
use App\Models\Event;
use App\Models\Kategori;
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
     * Menampilkan daftar event pada dashboard admin.
     */
    public function index(Request $request): View
    {
        /*
         * Hanya menerima sort asc atau desc.
         * Nilai lain akan dikembalikan menjadi asc.
         */
        $sort = strtolower((string) $request->get('sort', 'asc'));

        if (!in_array($sort, ['asc', 'desc'], true)) {
            $sort = 'asc';
        }

        $events = Event::query()
            ->with([
                'kategori',
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

                    $query->where(function ($subQuery) use ($search) {
                        $subQuery
                            ->where(
                                'judul',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'lokasi',
                                'like',
                                "%{$search}%"
                            );
                    });
                }
            )

            /*
             * Mengurutkan berdasarkan tanggal event.
             */
            ->orderBy('tanggal_waktu', $sort)

            /*
             * Menampilkan 10 event per halaman.
             */
            ->paginate(10)
            ->withQueryString();

        $categories = Kategori::query()
            ->orderBy('nama')
            ->get();

        return view('pages.admin.events.index', [
            'events' => $events,
            'categories' => $categories,
            'sort' => $sort,
        ]);
    }

    /**
     * Menampilkan form tambah event.
     */
    public function create(): View
    {
        $categories = Kategori::query()
            ->orderBy('nama')
            ->get();

        return view('pages.admin.events.create', [
            'categories' => $categories,
        ]);
    }

    /**
     * Menyimpan event dan tiket baru.
     */
    public function store(
        EventFormRequest $request
    ): RedirectResponse {
        $validated = $request->validated();

        /*
         * Default gambar digunakan jika admin tidak mengunggah gambar.
         */
        $imagePath = 'konser.jpg';
        $uploadedImagePath = null;

        if ($request->hasFile('gambar')) {
            $uploadedImagePath = $request
                ->file('gambar')
                ->store('events', 'public');

            $imagePath = $uploadedImagePath;
        }

        try {
            DB::transaction(function () use (
                $validated,
                $imagePath,
                $request
            ) {
                /*
                 * Membuat event.
                 */
                $event = Event::create([
                    'user_id' => $request->user()->id,
                    'kategori_id' => $validated['kategori_id'],
                    'judul' => $validated['judul'],
                    'deskripsi' => $validated['deskripsi'],
                    'lokasi' => $validated['lokasi'],
                    'gambar' => $imagePath,
                    'tanggal_waktu' => $validated['tanggal_waktu'],
                ]);

                /*
                 * Membuat seluruh tiket yang berasal dari
                 * dynamic ticket form.
                 */
                foreach ($validated['tikets'] as $ticketData) {
                    $event->tikets()->create(
                        $this->ticketPayload($ticketData)
                    );
                }
            });
        } catch (Throwable $exception) {
            /*
             * Jika database gagal menyimpan, hapus file yang
             * baru saja diunggah agar tidak menjadi file yatim.
             */
            if ($uploadedImagePath) {
                Storage::disk('public')->delete(
                    $uploadedImagePath
                );
            }

            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Event gagal disimpan. Silakan coba kembali.'
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
         * detail_orders_count akan digunakan untuk menentukan
         * tiket mana yang sudah pernah terjual.
         */
        $event->load([
            'kategori',
            'tikets' => function ($query) {
                $query
                    ->withCount('detailOrders')
                    ->orderBy('id');
            },
        ]);

        $categories = Kategori::query()
            ->orderBy('nama')
            ->get();

        $hasSales = $event->hasSales();

        return view('pages.admin.events.edit', [
            'event' => $event,
            'categories' => $categories,
            'hasSales' => $hasSales,
        ]);
    }

    /**
     * Memperbarui event dan tiket.
     */
    public function update(
        EventFormRequest $request,
        Event $event
    ): RedirectResponse {
        $validated = $request->validated();

        $event->load('tikets');

        $hasSales = $event->hasSales();

        /*
         * Event yang sudah memiliki penjualan tidak boleh
         * dipindahkan tanggal dan waktunya.
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

        $existingTicketIds = $event
            ->tikets
            ->pluck('id')
            ->map(fn ($id) => (int) $id);

        $submittedTickets = collect(
            $validated['tikets']
        );

        $submittedTicketIds = $submittedTickets
            ->pluck('id')
            ->filter(fn ($id) => filled($id))
            ->map(fn ($id) => (int) $id)
            ->values();

        /*
         * Mencegah admin mengirim ID tiket milik event lain.
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
         * Tiket lama yang tidak lagi dikirim form dianggap
         * sebagai tiket yang akan dihapus.
         */
        $removedTicketIds = $existingTicketIds
            ->diff($submittedTicketIds);

        /*
         * Sesuai ketentuan tugas, tiket tidak boleh dihapus
         * jika event sudah memiliki penjualan.
         */
        if (
            $hasSales &&
            $removedTicketIds->isNotEmpty()
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'tikets' =>
                        'Tiket lama tidak dapat dihapus karena event sudah memiliki penjualan.',
                ]);
        }

        $oldImagePath = $event->gambar;
        $newImagePath = null;

        if ($request->hasFile('gambar')) {
            $newImagePath = $request
                ->file('gambar')
                ->store('events', 'public');
        }

        try {
            DB::transaction(function () use (
                $event,
                $validated,
                $newImagePath,
                $oldImagePath,
                $submittedTickets,
                $removedTicketIds,
                $hasSales
            ) {
                /*
                 * Perbarui data event.
                 */
                $event->update([
                    'kategori_id' => $validated['kategori_id'],
                    'judul' => $validated['judul'],
                    'deskripsi' => $validated['deskripsi'],
                    'lokasi' => $validated['lokasi'],
                    'gambar' => $newImagePath ?? $oldImagePath,
                    'tanggal_waktu' => $validated['tanggal_waktu'],
                ]);

                /*
                 * Update tiket lama dan buat tiket baru.
                 */
                foreach ($submittedTickets as $ticketData) {
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

                        $ticket->update($payload);
                    } else {
                        $event
                            ->tikets()
                            ->create($payload);
                    }
                }

                /*
                 * Hapus tiket yang dihilangkan dari form
                 * hanya ketika event belum mempunyai penjualan.
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
            });
        } catch (Throwable $exception) {
            /*
             * Hapus gambar baru jika transaksi database gagal.
             */
            if ($newImagePath) {
                Storage::disk('public')->delete(
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
         * Gambar lama dihapus setelah transaksi database berhasil.
         */
        if ($newImagePath) {
            $this->deleteStoredImage($oldImagePath);
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
         * Event yang telah mempunyai penjualan tidak boleh dihapus.
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
            /*
             * Tiket akan ikut terhapus melalui foreign key cascade.
             */
            $event->delete();
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
         * Hapus gambar setelah event berhasil dihapus.
         */
        $this->deleteStoredImage($imagePath);

        return redirect()
            ->route('admin.events.index')
            ->with(
                'success',
                'Event berhasil dihapus.'
            );
    }

    /**
     * Menampilkan detail event pada halaman publik.
     */
    public function show(Event $event): View
    {
        $event->load([
            'kategori',
            'tikets' => function ($query) {
                $query->orderBy('harga');
            },
        ]);

        /*
         * Event terkait:
         * - kategori sama;
         * - tidak termasuk event yang sedang dibuka;
         * - tanggalnya masih akan datang;
         * - maksimal empat event.
         */
        $relatedEvents = Event::query()
            ->with([
                'kategori',
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
            ->map(function (Event $relatedEvent) {
                $relatedEvent->setAttribute(
                    'tikets_min_harga',
                    $relatedEvent->tikets->min('harga')
                );

                return $relatedEvent;
            });

        return view('events.show', [
            'event' => $event,
            'relatedEvents' => $relatedEvents,
        ]);
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
                trim((string) $ticketData['tipe'])
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

        $current = Carbon::parse($currentDate)
            ->format('Y-m-d H:i');

        $submitted = Carbon::parse($newDate)
            ->format('Y-m-d H:i');

        return $current !== $submitted;
    }

    /**
     * Menghapus gambar lokal dari public storage.
     *
     * File default konser.jpg dan URL eksternal tidak dihapus.
     */
    private function deleteStoredImage(
        ?string $imagePath
    ): void {
        if (!$imagePath) {
            return;
        }

        /*
         * URL eksternal tidak berada di storage lokal.
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
         * Mendukung data lama yang tersimpan sebagai:
         * storage/events/gambar.jpg
         */
        $normalizedPath = preg_replace(
            '#^/?storage/#',
            '',
            trim($imagePath)
        );

        if (
            !$normalizedPath ||
            $normalizedPath === 'konser.jpg'
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