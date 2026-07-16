@extends('layouts.admin_layouts')

@section('title', 'Manajemen Event')

@section('content')
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div
            class="flex flex-col gap-4 mb-6
                   sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h2 class="text-3xl font-bold text-gray-900">
                    Manajemen Event
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Kelola data event, jadwal, kategori, gambar,
                    dan tiket.
                </p>
            </div>

            <a
                href="{{ route('admin.events.create') }}"
                class="btn btn-primary"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    class="w-5 h-5"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M12 5v14M5 12h14"
                    />
                </svg>

                Tambah Event
            </a>
        </div>

        <!-- Success Alert -->
        @if (session('success'))
            <div class="alert alert-success mb-6 shadow-sm">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="w-6 h-6"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="m5 13 4 4L19 7"
                    />
                </svg>

                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Error Alert -->
        @if (session('error'))
            <div class="alert alert-error mb-6 shadow-sm">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="w-6 h-6"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 9v4m0 4h.01M10.29 3.86
                           1.82 18a2 2 0 0 0 1.71 3h16.94
                           a2 2 0 0 0 1.71-3L13.71 3.86
                           a2 2 0 0 0-3.42 0z"
                    />
                </svg>

                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Filter Form -->
        <div class="card bg-white shadow-sm mb-6">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-semibold text-lg">
                            Filter Event
                        </h3>

                        <p class="text-sm text-gray-500">
                            Cari dan urutkan data event yang tersedia.
                        </p>
                    </div>
                </div>

                <form
                    method="GET"
                    action="{{ route('admin.events.index') }}"
                >
                    <div
                        class="grid grid-cols-1 gap-4
                               md:grid-cols-2 lg:grid-cols-4"
                    >
                        <!-- Search -->
                        <div class="space-y-2">
                            <label for="search" class="block">
                                <span class="text-sm font-medium">
                                    Pencarian
                                </span>
                            </label>

                            <input
                                type="search"
                                id="search"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Cari judul atau lokasi..."
                                class="input input-bordered w-full"
                            >
                        </div>

                        <!-- Category Filter -->
                        <div class="space-y-2">
                            <label for="kategori_id" class="block">
                                <span class="text-sm font-medium">
                                    Kategori
                                </span>
                            </label>

                            <select
                                id="kategori_id"
                                name="kategori_id"
                                class="select select-bordered w-full"
                            >
                                <option value="">
                                    Semua Kategori
                                </option>

                                @foreach ($categories as $category)
                                    <option
                                        value="{{ $category->id }}"
                                        @selected(
                                            request('kategori_id') ==
                                            $category->id
                                        )
                                    >
                                        {{ $category->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Sort -->
                        <div class="space-y-2">
                            <label for="sort" class="block">
                                <span class="text-sm font-medium">
                                    Urutkan Tanggal
                                </span>
                            </label>

                            <select
                                id="sort"
                                name="sort"
                                class="select select-bordered w-full"
                            >
                                <option
                                    value="asc"
                                    @selected(
                                        request('sort', 'asc') === 'asc'
                                    )
                                >
                                    Terdekat
                                </option>

                                <option
                                    value="desc"
                                    @selected(
                                        request('sort') === 'desc'
                                    )
                                >
                                    Terjauh
                                </option>
                            </select>
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-end gap-2">
                            <button
                                type="submit"
                                class="btn btn-primary flex-1"
                            >
                                Filter
                            </button>

                            <a
                                href="{{ route('admin.events.index') }}"
                                class="btn btn-outline"
                            >
                                Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Event Table -->
        <div class="card bg-white shadow-sm">
            <div class="card-body p-0">
                <div class="overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th>Gambar</th>
                                <th>Judul</th>
                                <th>Kategori</th>
                                <th>Tanggal</th>
                                <th>Lokasi</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($events as $event)
                                @php
                                    $statusClass = match ($event->status) {
                                        'Upcoming' =>
                                            'badge-info text-white',

                                        'Ongoing' =>
                                            'badge-warning',

                                        'Completed' =>
                                            'badge-success text-white',

                                        default =>
                                            'badge-ghost',
                                    };

                                    $statusLabel = match ($event->status) {
                                        'Upcoming' => 'Upcoming',
                                        'Ongoing' => 'Ongoing',
                                        'Completed' => 'Completed',
                                        default => $event->status,
                                    };
                                @endphp

                                <tr>
                                    <!-- Image -->
                                    <td>
                                        <img
                                            src="{{ $event->image_url }}"
                                            alt="{{ $event->judul }}"
                                            class="w-16 h-16 object-cover rounded-lg"
                                            onerror="
                                                this.onerror = null;
                                                this.src = '{{ asset('storage/konser.jpg') }}';
                                            "
                                        >
                                    </td>

                                    <!-- Title -->
                                    <td>
                                        <div class="max-w-xs">
                                            <p
                                                class="font-semibold
                                                       text-gray-900
                                                       line-clamp-2"
                                            >
                                                {{ $event->judul }}
                                            </p>

                                            <p
                                                class="text-xs
                                                       text-gray-500 mt-1"
                                            >
                                                {{ $event->tikets->count() }}
                                                jenis tiket
                                            </p>
                                        </div>
                                    </td>

                                    <!-- Category -->
                                    <td>
                                        @if ($event->kategori)
                                            <span
                                                class="badge
                                                       badge-outline"
                                            >
                                                {{ $event->kategori->nama }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">
                                                Tidak ada kategori
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Date -->
                                    <td>
                                        <div class="whitespace-nowrap">
                                            <p class="font-medium">
                                                {{ $event->tanggal_waktu
                                                    ->locale('id')
                                                    ->translatedFormat(
                                                        'd M Y'
                                                    )
                                                }}
                                            </p>

                                            <p class="text-sm text-gray-500">
                                                {{ $event->tanggal_waktu
                                                    ->format('H:i')
                                                }}
                                                WIB
                                            </p>
                                        </div>
                                    </td>

                                    <!-- Location -->
                                    <td>
                                        <div class="max-w-48">
                                            <p class="line-clamp-2">
                                                {{ $event->lokasi }}
                                            </p>
                                        </div>
                                    </td>

                                    <!-- Status -->
                                    <td>
                                        <span
                                            class="badge {{ $statusClass }}"
                                        >
                                            {{ $statusLabel }}
                                        </span>
                                    </td>

                                    <!-- Actions -->
                                    <td>
                                        <div
                                            class="flex justify-center
                                                   flex-wrap gap-2"
                                        >
                                            <!-- View -->
                                            <a
                                                href="{{
                                                    route(
                                                        'events.show',
                                                        $event
                                                    )
                                                }}"
                                                class="btn btn-sm
                                                       btn-ghost
                                                       text-blue-700"
                                                title="Lihat Event"
                                                target="_blank"
                                            >
                                                Lihat
                                            </a>

                                            <!-- Edit -->
                                            <a
                                                href="{{
                                                    route(
                                                        'admin.events.edit',
                                                        $event
                                                    )
                                                }}"
                                                class="btn btn-sm
                                                       btn-primary"
                                            >
                                                Edit
                                            </a>

                                            <!-- Delete -->
                                            <button
                                                type="button"
                                                class="btn btn-sm
                                                       btn-error text-white"
                                                data-id="{{ $event->id }}"
                                                data-title="{{
                                                    $event->judul
                                                }}"
                                                data-url="{{
                                                    route(
                                                        'admin.events.destroy',
                                                        $event
                                                    )
                                                }}"
                                                onclick="
                                                    openDeleteEventModal(this)
                                                "
                                            >
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td
                                        colspan="7"
                                        class="text-center py-16"
                                    >
                                        <div class="text-6xl mb-4">
                                            🎫
                                        </div>

                                        <h3
                                            class="text-lg font-semibold
                                                   text-gray-900"
                                        >
                                            Event tidak ditemukan
                                        </h3>

                                        <p
                                            class="text-sm text-gray-500
                                                   mt-1 mb-4"
                                        >
                                            Belum ada event atau data tidak
                                            sesuai dengan filter.
                                        </p>

                                        <a
                                            href="{{
                                                route(
                                                    'admin.events.create'
                                                )
                                            }}"
                                            class="btn btn-primary"
                                        >
                                            Tambah Event
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if ($events->hasPages())
                    <div class="px-6 py-5 border-t">
                        {{
                            $events
                                ->appends(
                                    request()->except('page')
                                )
                                ->links()
                        }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <dialog id="delete_event_modal" class="modal">
        <div class="modal-box">
            <h3 class="text-xl font-bold">
                Hapus Event
            </h3>

            <p class="py-4">
                Apakah Anda yakin ingin menghapus event
                <strong id="delete_event_title"></strong>?
            </p>

            <div class="alert alert-warning text-sm">
                <span>
                    Event yang sudah memiliki penjualan tiket
                    tidak dapat dihapus.
                </span>
            </div>

            <div class="modal-action">
                <button
                    type="button"
                    class="btn"
                    onclick="closeDeleteEventModal()"
                >
                    Batal
                </button>

                <form
                    id="delete_event_form"
                    method="POST"
                >
                    @csrf
                    @method('DELETE')

                    <button
                        type="submit"
                        class="btn btn-error text-white"
                    >
                        Hapus Event
                    </button>
                </form>
            </div>
        </div>

        <form method="dialog" class="modal-backdrop">
            <button>Tutup</button>
        </form>
    </dialog>
@endsection

@push('scripts')
    <script>
        function openDeleteEventModal(button) {
            const modal = document.getElementById(
                'delete_event_modal'
            );

            const form = document.getElementById(
                'delete_event_form'
            );

            const eventTitle = document.getElementById(
                'delete_event_title'
            );

            eventTitle.textContent = button.dataset.title;
            form.action = button.dataset.url;

            modal.showModal();
        }

        function closeDeleteEventModal() {
            const modal = document.getElementById(
                'delete_event_modal'
            );

            const form = document.getElementById(
                'delete_event_form'
            );

            form.removeAttribute('action');
            modal.close();
        }
    </script>
@endpush