<x-app-layout>
    <div class="max-w-7xl mx-auto py-8 px-6">
        {{-- Event Header --}}
        <div class="card bg-base-100 shadow-xl mb-8">
            <div class="card-body">
                <div class="flex flex-col lg:flex-row gap-8">
                    {{-- Event Image --}}
                    <div class="lg:w-1/2">
                        <img
                            src="{{ $event->image_url }}"
                            alt="{{ $event->judul }}"
                            class="w-full h-72 lg:h-96 object-cover rounded-xl"
                            onerror="
                                this.onerror = null;
                                this.src = '{{ asset('storage/konser.jpg') }}';
                            "
                        >
                    </div>

                    {{-- Event Details --}}
                    <div class="lg:w-1/2">
                        <h1 class="text-4xl font-bold mb-4">
                            {{ $event->judul ?? $event->nama }}
                        </h1>

                        @if ($event->kategori)
                            <div class="badge badge-primary badge-lg mb-4">
                                {{ $event->kategori->nama }}
                            </div>
                        @endif

                        <div class="space-y-4 mb-6">
                            {{-- Tanggal dan Waktu --}}
                            <div class="flex items-start gap-3">
                                <svg
                                    class="w-5 h-5 mt-1 text-primary shrink-0"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                    ></path>
                                </svg>

                                <div>
                                    <p class="font-semibold">
                                        Tanggal dan Waktu
                                    </p>

                                    <p class="text-gray-600">
                                        @if ($event->tanggal_waktu)
                                            {{
                                                \Carbon\Carbon::parse(
                                                    $event->tanggal_waktu
                                                )
                                                    ->locale('id')
                                                    ->translatedFormat(
                                                        'd F Y, H:i'
                                                    )
                                            }}
                                            WIB
                                        @elseif ($event->tanggal)
                                            {{
                                                \Carbon\Carbon::parse(
                                                    $event->tanggal
                                                )
                                                    ->locale('id')
                                                    ->translatedFormat(
                                                        'd F Y, H:i'
                                                    )
                                            }}
                                            WIB
                                        @else
                                            Tanggal tidak tersedia
                                        @endif
                                    </p>
                                </div>
                            </div>

                            {{-- Lokasi --}}
                            <div class="flex items-start gap-3">
                                <svg
                                    class="w-5 h-5 mt-1 text-primary shrink-0"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                                    ></path>

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                                    ></path>
                                </svg>

                                <div>
                                    <p class="font-semibold">
                                        Lokasi
                                    </p>

                                    <p class="text-gray-600">
                                        {{
                                            $event->lokasiData?->nama_lokasi
                                            ?? $event->lokasi
                                            ?? 'Lokasi tidak tersedia'
                                        }}
                                    </p>

                                    @if (
                                        $event->lokasiData &&
                                        $event->lokasiData->aktif === 'N'
                                    )
                                        <span
                                            class="badge badge-warning badge-sm mt-2"
                                        >
                                            Lokasi Tidak Aktif
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Status Event --}}
                            <div class="flex items-start gap-3">
                                <svg
                                    class="w-5 h-5 mt-1 text-primary shrink-0"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                    ></path>
                                </svg>

                                <div>
                                    <p class="font-semibold">
                                        Status Event
                                    </p>

                                    @php
                                        $statusClass = match ($event->status) {
                                            'Upcoming' => 'badge-info',
                                            'Ongoing' => 'badge-success',
                                            default => 'badge-ghost',
                                        };

                                        $statusLabel = match ($event->status) {
                                            'Upcoming' => 'Akan Datang',
                                            'Ongoing' => 'Sedang Berlangsung',
                                            default => 'Selesai',
                                        };
                                    @endphp

                                    <span
                                        class="badge {{ $statusClass }} mt-1"
                                    >
                                        {{ $statusLabel }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Description --}}
                        @if ($event->deskripsi)
                            <div class="mb-6">
                                <h2 class="text-lg font-semibold mb-2">
                                    Deskripsi Event
                                </h2>

                                <p
                                    class="text-gray-600 leading-relaxed whitespace-pre-line"
                                >
                                    {{ $event->deskripsi }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Ticket Options --}}
        @if ($event->tikets && $event->tikets->count() > 0)
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <div class="mb-6">
                        <h2 class="card-title text-2xl">
                            Pilih Tiket
                        </h2>

                        <p class="text-gray-500 mt-1">
                            Pilih jenis tiket yang sesuai dengan kebutuhan Anda.
                        </p>
                    </div>

                    <div
                        class="grid grid-cols-1 md:grid-cols-2
                            lg:grid-cols-3 gap-6"
                    >
                        @foreach ($event->tikets as $tiket)
                            <div
                                class="card bg-base-200
                                    hover:bg-base-300
                                    transition-colors duration-200"
                            >
                                <div class="card-body">
                                    <div
                                        class="flex justify-between
                                            items-start gap-3"
                                    >
                                        <h3 class="card-title text-lg">
                                            Tiket
                                            {{ ucfirst($tiket->tipe) }}
                                        </h3>

                                        @if (
                                            $tiket->stok !== null &&
                                            $tiket->stok > 0
                                        )
                                            <span
                                                class="badge badge-success"
                                            >
                                                Tersedia
                                            </span>
                                        @else
                                            <span
                                                class="badge badge-error"
                                            >
                                                Habis
                                            </span>
                                        @endif
                                    </div>

                                    @if ($tiket->deskripsi)
                                        <p
                                            class="text-sm text-gray-600 mb-4"
                                        >
                                            {{ $tiket->deskripsi }}
                                        </p>
                                    @endif

                                    <div class="my-4">
                                        <p class="text-sm text-gray-500">
                                            Harga tiket
                                        </p>

                                        <p
                                            class="text-2xl font-bold
                                                text-primary"
                                        >
                                            Rp
                                            {{
                                                number_format(
                                                    $tiket->harga,
                                                    0,
                                                    ',',
                                                    '.'
                                                )
                                            }}
                                        </p>
                                    </div>

                                    <div
                                        class="flex justify-between
                                            items-center mb-4"
                                    >
                                        <span class="text-sm text-gray-500">
                                            Stok tiket
                                        </span>

                                        <span class="font-semibold">
                                            @if ($tiket->stok !== null)
                                                {{ $tiket->stok }} tiket
                                            @else
                                                Tidak terbatas
                                            @endif
                                        </span>
                                    </div>

                                    <button
                                        type="button"
                                        class="btn btn-primary w-full
                                            {{
                                                $tiket->stok !== null &&
                                                $tiket->stok <= 0
                                                    ? 'btn-disabled'
                                                    : ''
                                            }}"
                                        @disabled(
                                            $tiket->stok !== null &&
                                            $tiket->stok <= 0
                                        )
                                    >
                                        @if (
                                            $tiket->stok !== null &&
                                            $tiket->stok <= 0
                                        )
                                            Habis Terjual
                                        @else
                                            Beli Sekarang
                                        @endif
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body text-center py-12">
                    <div class="text-5xl mb-4">
                        🎫
                    </div>

                    <h3 class="text-xl font-semibold mb-2">
                        Tiket Tidak Tersedia
                    </h3>

                    <p class="text-gray-600">
                        Belum ada tiket yang tersedia untuk event ini.
                    </p>
                </div>
            </div>
        @endif

        {{-- Related Events --}}
        @if (
            isset($relatedEvents) &&
            $relatedEvents->isNotEmpty()
        )
            <section class="mt-12">
                <div class="mb-6">
                    <p class="text-sm font-semibold text-primary uppercase">
                        Rekomendasi Lainnya
                    </p>

                    <h2 class="text-3xl font-bold">
                        Event Terkait
                    </h2>

                    <p class="text-gray-500 mt-1">
                        Event lain dalam kategori
                        {{ $event->kategori?->nama ?? 'yang sama' }}.
                    </p>
                </div>

                <div
                    class="grid grid-cols-1 sm:grid-cols-2
                        lg:grid-cols-4 gap-6"
                >
                    @foreach ($relatedEvents as $relatedEvent)
                        <x-event-card
                            :title="$relatedEvent->judul"
                            :date="$relatedEvent->tanggal_waktu"
                            :location="
                                $relatedEvent
                                    ->lokasiData
                                    ?->nama_lokasi
                                ?? $relatedEvent->lokasi
                                ?? 'Lokasi tidak tersedia'
                            "
                            :price="$relatedEvent->tikets_min_harga"
                            :image="$relatedEvent->image_url"
                            :href="route(
                                'events.show',
                                $relatedEvent
                            )"
                        />
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Back Button --}}
        <div class="mt-8">
            <a
                href="{{ route('home') }}"
                class="btn btn-outline btn-wide"
            >
                <svg
                    class="w-4 h-4 mr-2"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"
                    ></path>
                </svg>

                Kembali ke Beranda
            </a>
        </div>
    </div>
</x-app-layout>
