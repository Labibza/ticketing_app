@extends('layouts.admin_layouts')

@section('title', 'Edit Event')

@section('content')
    @php
        /*
         * Menyimpan informasi apakah setiap tiket
         * sudah pernah digunakan pada transaksi.
         */
        $ticketSalesMap = $event->tikets
            ->mapWithKeys(function ($ticket) {
                return [
                    (string) $ticket->id =>
                        ($ticket->detail_orders_count ?? 0) > 0,
                ];
            })
            ->all();

        /*
         * Gunakan old input jika validasi gagal.
         * Jika tidak, gunakan tiket yang ada di database.
         */
        $initialTickets = old('tikets');

        if ($initialTickets === null) {
            $initialTickets = $event->tikets
                ->map(function ($ticket) {
                    return [
                        'id' => $ticket->id,
                        'tipe' => $ticket->tipe,
                        'harga' => $ticket->harga,
                        'stok' => $ticket->stok,
                    ];
                })
                ->values()
                ->all();
        }
    @endphp

    <div class="max-w-6xl mx-auto">
        {{-- Back Button --}}
        <div class="mb-6">
            <a
                href="{{ route('admin.events.index') }}"
                class="btn btn-ghost px-0 hover:bg-transparent
                       hover:text-blue-700"
            >
                ← Kembali ke Manajemen Event
            </a>
        </div>

        {{-- Page Header --}}
        <div class="mb-6">
            <h2 class="text-3xl font-bold text-gray-900">
                Edit Event
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Perbarui informasi event dan data tiket.
            </p>
        </div>

        {{-- Sales Warning --}}
        @if ($hasSales)
            <div class="alert alert-warning mb-6 shadow-sm">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="w-6 h-6 shrink-0"
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

                <div>
                    <p class="font-semibold">
                        Event ini sudah memiliki penjualan tiket.
                    </p>

                    <p class="text-sm">
                        Beberapa field mungkin tidak dapat diubah.
                        Tanggal event dan tiket yang sudah terjual
                        akan dilindungi.
                    </p>
                </div>
            </div>
        @endif

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="alert alert-error mb-6 shadow-sm">
                <div>
                    <p class="font-semibold">
                        Data event belum dapat diperbarui.
                    </p>

                    <ul class="list-disc list-inside mt-2 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error mb-6 shadow-sm">
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <form
            method="POST"
            action="{{ route('admin.events.update', $event) }}"
            enctype="multipart/form-data"
            id="event-form"
        >
            @csrf
            @method('PUT')

            {{-- Event Information --}}
            <div class="card bg-white shadow-sm mb-6">
                <div class="card-body">
                    <div class="mb-5">
                        <h3 class="card-title text-xl">
                            Informasi Event
                        </h3>

                        <p class="text-sm text-gray-500">
                            Perbarui informasi utama event.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Judul --}}
                        <div class="space-y-2">
                            <label for="judul" class="block">
                                <span class="text-sm font-medium">
                                    Judul Event
                                </span>

                                <span class="text-error">*</span>
                            </label>

                            <input
                                type="text"
                                id="judul"
                                name="judul"
                                value="{{ old('judul', $event->judul) }}"
                                class="input input-bordered w-full
                                    @error('judul') input-error @enderror"
                                maxlength="255"
                                required
                                autofocus
                            >

                            @error('judul')
                                <p class="text-error text-sm">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Kategori --}}
                        <div class="space-y-2">
                            <label for="kategori_id" class="block">
                                <span class="text-sm font-medium">
                                    Kategori
                                </span>

                                <span class="text-error">*</span>
                            </label>

                            <select
                                id="kategori_id"
                                name="kategori_id"
                                class="select select-bordered w-full
                                    @error('kategori_id') select-error @enderror"
                                required
                            >
                                <option value="" disabled>
                                    Pilih kategori
                                </option>

                                @foreach ($categories as $category)
                                    <option
                                        value="{{ $category->id }}"
                                        @selected(
                                            old(
                                                'kategori_id',
                                                $event->kategori_id
                                            ) == $category->id
                                        )
                                    >
                                        {{ $category->nama }}
                                    </option>
                                @endforeach
                            </select>

                            @error('kategori_id')
                                <p class="text-error text-sm">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Lokasi --}}
                        <div class="space-y-2">
                            <label for="lokasi" class="block">
                                <span class="text-sm font-medium">
                                    Lokasi
                                </span>

                                <span class="text-error">*</span>
                            </label>

                            <input
                                type="text"
                                id="lokasi"
                                name="lokasi"
                                value="{{ old('lokasi', $event->lokasi) }}"
                                class="input input-bordered w-full
                                    @error('lokasi') input-error @enderror"
                                maxlength="255"
                                required
                            >

                            @error('lokasi')
                                <p class="text-error text-sm">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Date --}}
                        <div class="space-y-2">
                            <label for="tanggal_waktu" class="block">
                                <span class="text-sm font-medium">
                                    Tanggal dan Waktu
                                </span>

                                <span class="text-error">*</span>

                                @if ($hasSales)
                                    <span
                                        class="badge badge-warning
                                               badge-sm ml-2"
                                    >
                                        Tidak dapat diubah
                                    </span>
                                @endif
                            </label>

                            <input
                                type="datetime-local"
                                id="tanggal_waktu"
                                name="tanggal_waktu"
                                value="{{
                                    old(
                                        'tanggal_waktu',
                                        $event->tanggal_waktu
                                            ->format('Y-m-d\TH:i')
                                    )
                                }}"
                                class="input input-bordered w-full
                                    @error('tanggal_waktu') input-error @enderror
                                    {{ $hasSales ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                                @if (!$hasSales)
                                    min="{{ now()->format('Y-m-d\TH:i') }}"
                                @endif
                                @readonly($hasSales)
                                required
                            >

                            @if ($hasSales)
                                <p class="text-xs text-warning">
                                    Tanggal dikunci karena event sudah
                                    memiliki penjualan.
                                </p>
                            @endif

                            @error('tanggal_waktu')
                                <p class="text-error text-sm">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Image --}}
                        <div class="space-y-4 md:col-span-2">
                            <div>
                                <p class="text-sm font-medium mb-2">
                                    Gambar Saat Ini
                                </p>

                                <img
                                    src="{{ $event->image_url }}"
                                    alt="{{ $event->judul }}"
                                    class="w-full max-w-md h-64
                                           object-cover rounded-xl
                                           border border-gray-200"
                                    onerror="
                                        this.onerror = null;
                                        this.src =
                                        '{{ asset('storage/konser.jpg') }}';
                                    "
                                >
                            </div>

                            <div class="space-y-2">
                                <label for="gambar" class="block">
                                    <span class="text-sm font-medium">
                                        Ganti Gambar
                                    </span>
                                </label>

                                <input
                                    type="file"
                                    id="gambar"
                                    name="gambar"
                                    accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                                    class="file-input file-input-bordered
                                           w-full
                                        @error('gambar')
                                            file-input-error
                                        @enderror"
                                >

                                <p class="text-xs text-gray-500">
                                    Kosongkan jika tidak ingin mengubah gambar.
                                    Maksimal 2 MB, format JPG, JPEG, atau PNG.
                                </p>

                                @error('gambar')
                                    <p class="text-error text-sm">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- New Image Preview --}}
                            <div
                                id="image-preview-container"
                                class="hidden"
                            >
                                <p class="text-sm font-medium mb-2">
                                    Preview Gambar Baru
                                </p>

                                <div
                                    class="relative inline-block
                                           rounded-xl overflow-hidden
                                           border border-gray-200"
                                >
                                    <img
                                        id="image-preview"
                                        src=""
                                        alt="Preview gambar baru"
                                        class="w-full max-w-md h-64
                                               object-cover"
                                    >

                                    <button
                                        type="button"
                                        id="remove-image-preview"
                                        class="btn btn-sm btn-circle
                                               btn-error text-white
                                               absolute top-2 right-2"
                                    >
                                        ×
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Description --}}
                        <div class="space-y-2 md:col-span-2">
                            <label for="deskripsi" class="block">
                                <span class="text-sm font-medium">
                                    Deskripsi
                                </span>

                                <span class="text-error">*</span>
                            </label>

                            <textarea
                                id="deskripsi"
                                name="deskripsi"
                                rows="7"
                                class="textarea textarea-bordered w-full
                                    @error('deskripsi')
                                        textarea-error
                                    @enderror"
                                required
                            >{{ old('deskripsi', $event->deskripsi) }}</textarea>

                            @error('deskripsi')
                                <p class="text-error text-sm">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Ticket Data --}}
            <div class="card bg-white shadow-sm mb-6">
                <div class="card-body">
                    <div
                        class="flex flex-col gap-4 mb-5
                               sm:flex-row sm:items-center
                               sm:justify-between"
                    >
                        <div>
                            <h3 class="card-title text-xl">
                                Data Tiket
                            </h3>

                            <p class="text-sm text-gray-500">
                                Perbarui tiket lama atau tambahkan tiket baru.
                            </p>
                        </div>

                        <button
                            type="button"
                            id="add-ticket-button"
                            class="btn btn-outline btn-primary"
                        >
                            + Tambah Tiket
                        </button>
                    </div>

                    @error('tikets')
                        <div class="alert alert-error mb-4">
                            <span>{{ $message }}</span>
                        </div>
                    @enderror

                    <div
                        id="tickets-container"
                        class="space-y-5"
                    ></div>
                </div>
            </div>

            {{-- Actions --}}
            <div
                class="flex flex-col-reverse gap-3
                       sm:flex-row sm:justify-end"
            >
                <a
                    href="{{ route('admin.events.index') }}"
                    class="btn btn-outline"
                >
                    Batal
                </a>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const container = document.getElementById(
                'tickets-container'
            );

            const addButton = document.getElementById(
                'add-ticket-button'
            );

            const imageInput = document.getElementById('gambar');

            const previewContainer = document.getElementById(
                'image-preview-container'
            );

            const previewImage = document.getElementById(
                'image-preview'
            );

            const removePreviewButton = document.getElementById(
                'remove-image-preview'
            );

            const initialTickets = @js($initialTickets);
            const ticketSalesMap = @js($ticketSalesMap);
            const validationErrors = @js($errors->toArray());

            let ticketIndex = 0;

            function escapeHtml(value) {
                const element = document.createElement('div');

                element.textContent = value ?? '';

                return element.innerHTML;
            }

            function getTicketError(index, field) {
                const key = `tikets.${index}.${field}`;

                return validationErrors[key]?.[0] ?? '';
            }

            function isTicketSold(ticket) {
                if (!ticket.id) {
                    return false;
                }

                return Boolean(
                    ticketSalesMap[String(ticket.id)]
                );
            }

            function addTicket(ticket = {}, validationIndex = null) {
                const index = ticketIndex++;
                const errorIndex = validationIndex ?? index;
                const sold = isTicketSold(ticket);

                const typeError = getTicketError(
                    errorIndex,
                    'tipe'
                );

                const priceError = getTicketError(
                    errorIndex,
                    'harga'
                );

                const stockError = getTicketError(
                    errorIndex,
                    'stok'
                );

                const card = document.createElement('div');

                card.className =
                    'ticket-card border border-gray-200 ' +
                    'rounded-xl p-5 bg-gray-50';

                card.dataset.sold = sold ? 'true' : 'false';

                card.innerHTML = `
                    ${
                        ticket.id
                            ? `
                                <input
                                    type="hidden"
                                    name="tikets[${index}][id]"
                                    value="${escapeHtml(ticket.id)}"
                                >
                            `
                            : ''
                    }

                    <div
                        class="flex flex-col gap-3 mb-5
                               sm:flex-row sm:items-center
                               sm:justify-between"
                    >
                        <div class="flex items-center gap-2">
                            <h4
                                class="ticket-title
                                       font-semibold text-lg"
                            >
                                Tiket
                            </h4>

                            ${
                                sold
                                    ? `
                                        <span
                                            class="badge badge-warning"
                                        >
                                            Sudah Terjual
                                        </span>
                                    `
                                    : ''
                            }
                        </div>

                        <button
                            type="button"
                            class="remove-ticket-button
                                   btn btn-sm btn-error text-white"
                            ${sold ? 'disabled' : ''}
                        >
                            ${
                                sold
                                    ? 'Tidak Dapat Dihapus'
                                    : 'Hapus'
                            }
                        </button>
                    </div>

                    <div
                        class="grid grid-cols-1 gap-5
                               md:grid-cols-3"
                    >
                        <div class="space-y-2">
                            <label class="block">
                                <span class="text-sm font-medium">
                                    Tipe Tiket
                                </span>
                                <span class="text-error">*</span>
                            </label>

                            <select
                                name="tikets[${index}][tipe]"
                                class="select select-bordered w-full
                                    ${typeError ? 'select-error' : ''}"
                                required
                            >
                                <option
                                    value="reguler"
                                    ${
                                        ticket.tipe === 'premium'
                                            ? ''
                                            : 'selected'
                                    }
                                >
                                    Reguler
                                </option>

                                <option
                                    value="premium"
                                    ${
                                        ticket.tipe === 'premium'
                                            ? 'selected'
                                            : ''
                                    }
                                >
                                    Premium
                                </option>
                            </select>

                            ${
                                typeError
                                    ? `
                                        <p class="text-error text-sm">
                                            ${escapeHtml(typeError)}
                                        </p>
                                    `
                                    : ''
                            }
                        </div>

                        <div class="space-y-2">
                            <label class="block">
                                <span class="text-sm font-medium">
                                    Harga
                                </span>
                                <span class="text-error">*</span>
                            </label>

                            <label
                                class="input input-bordered
                                       flex items-center gap-2
                                    ${priceError ? 'input-error' : ''}"
                            >
                                <span class="text-gray-500">
                                    Rp
                                </span>

                                <input
                                    type="number"
                                    name="tikets[${index}][harga]"
                                    value="${escapeHtml(ticket.harga ?? '')}"
                                    min="0"
                                    step="0.01"
                                    class="grow"
                                    required
                                >
                            </label>

                            ${
                                priceError
                                    ? `
                                        <p class="text-error text-sm">
                                            ${escapeHtml(priceError)}
                                        </p>
                                    `
                                    : ''
                            }
                        </div>

                        <div class="space-y-2">
                            <label class="block">
                                <span class="text-sm font-medium">
                                    Stok
                                </span>
                                <span class="text-error">*</span>
                            </label>

                            <input
                                type="number"
                                name="tikets[${index}][stok]"
                                value="${escapeHtml(ticket.stok ?? '')}"
                                min="0"
                                step="1"
                                class="input input-bordered w-full
                                    ${stockError ? 'input-error' : ''}"
                                required
                            >

                            ${
                                stockError
                                    ? `
                                        <p class="text-error text-sm">
                                            ${escapeHtml(stockError)}
                                        </p>
                                    `
                                    : ''
                            }
                        </div>
                    </div>
                `;

                container.appendChild(card);

                updateTicketNumbers();
                updateRemoveButtons();
            }

            function updateTicketNumbers() {
                const cards = container.querySelectorAll(
                    '.ticket-card'
                );

                cards.forEach(function (card, index) {
                    card.querySelector(
                        '.ticket-title'
                    ).textContent = `Tiket #${index + 1}`;
                });
            }

            function updateRemoveButtons() {
                const cards = container.querySelectorAll(
                    '.ticket-card'
                );

                cards.forEach(function (card) {
                    const button = card.querySelector(
                        '.remove-ticket-button'
                    );

                    const sold = card.dataset.sold === 'true';

                    if (sold) {
                        button.disabled = true;
                        return;
                    }

                    button.disabled = cards.length <= 1;
                });
            }

            addButton.addEventListener('click', function () {
                addTicket({
                    tipe: 'reguler',
                    harga: '',
                    stok: '',
                });
            });

            container.addEventListener('click', function (event) {
                const button = event.target.closest(
                    '.remove-ticket-button'
                );

                if (!button || button.disabled) {
                    return;
                }

                const card = button.closest('.ticket-card');

                if (card.dataset.sold === 'true') {
                    alert(
                        'Tiket yang sudah terjual tidak dapat dihapus.'
                    );
                    return;
                }

                const cards = container.querySelectorAll(
                    '.ticket-card'
                );

                if (cards.length <= 1) {
                    return;
                }

                card.remove();

                updateTicketNumbers();
                updateRemoveButtons();
            });

            if (
                Array.isArray(initialTickets) &&
                initialTickets.length > 0
            ) {
                initialTickets.forEach(function (ticket, index) {
                    addTicket(ticket, index);
                });
            } else {
                addTicket();
            }

            imageInput.addEventListener('change', function () {
                const file = imageInput.files[0];

                if (!file) {
                    hidePreview();
                    return;
                }

                const validTypes = [
                    'image/jpeg',
                    'image/png',
                ];

                if (!validTypes.includes(file.type)) {
                    alert(
                        'Gambar harus berformat JPG, JPEG, atau PNG.'
                    );

                    imageInput.value = '';
                    hidePreview();
                    return;
                }

                if (file.size > 2 * 1024 * 1024) {
                    alert('Ukuran gambar maksimal 2 MB.');

                    imageInput.value = '';
                    hidePreview();
                    return;
                }

                const reader = new FileReader();

                reader.onload = function (event) {
                    previewImage.src = event.target.result;
                    previewContainer.classList.remove('hidden');
                };

                reader.readAsDataURL(file);
            });

            removePreviewButton.addEventListener(
                'click',
                function () {
                    imageInput.value = '';
                    hidePreview();
                }
            );

            function hidePreview() {
                previewImage.src = '';
                previewContainer.classList.add('hidden');
            }
        });
    </script>
@endpush