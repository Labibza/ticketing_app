@extends('layouts.admin_layouts')

@section('title', 'Tambah Event')

@section('content')
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
                Tambah Event
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Lengkapi data event dan tambahkan minimal satu jenis tiket.
            </p>
        </div>

        {{-- Error Summary --}}
        @if ($errors->any())
            <div class="alert alert-error mb-6 shadow-sm">
                <div>
                    <h3 class="font-semibold">
                        Data event belum dapat disimpan.
                    </h3>

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

        {{-- Main Form --}}
        <form
            method="POST"
            action="{{ route('admin.events.store') }}"
            enctype="multipart/form-data"
            id="event-form"
        >
            @csrf

            {{-- Event Information Card --}}
            <div class="card bg-white shadow-sm mb-6">
                <div class="card-body">
                    <div class="mb-5">
                        <h3 class="card-title text-xl">
                            Informasi Event
                        </h3>

                        <p class="text-sm text-gray-500">
                            Masukkan informasi utama event yang akan
                            ditampilkan kepada pengguna.
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
                                value="{{ old('judul') }}"
                                placeholder="Contoh: Konser Musik Semarang 2026"
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
                                <option value="" disabled
                                    @selected(!old('kategori_id'))
                                >
                                    Pilih kategori event
                                </option>

                                @foreach ($categories as $category)
                                    <option
                                        value="{{ $category->id }}"
                                        @selected(
                                            old('kategori_id') ==
                                            $category->id
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
                        <div class="form-control">
                            <label
                                for="lokasi_id"
                                class="label"
                            >
                                <span class="label-text font-semibold">
                                    Lokasi Event
                                </span>
                            </label>

                            <select
                                id="lokasi_id"
                                name="lokasi_id"
                                class="select select-bordered w-full
                                    @error('lokasi_id') select-error @enderror"
                                required
                            >
                                <option value="">
                                    Pilih lokasi
                                </option>

                                @foreach ($lokasis as $lokasi)
                                    <option
                                        value="{{ $lokasi->id }}"
                                        @selected(
                                            old('lokasi_id') == $lokasi->id
                                        )
                                    >
                                        {{ $lokasi->nama_lokasi }}
                                    </option>
                                @endforeach
                            </select>

                            @error('lokasi_id')
                                <span class="mt-1 text-sm text-error">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        {{-- Date and Time --}}
                        <div class="space-y-2">
                            <label for="tanggal_waktu" class="block">
                                <span class="text-sm font-medium">
                                    Tanggal dan Waktu
                                </span>

                                <span class="text-error">*</span>
                            </label>

                            <input
                                type="datetime-local"
                                id="tanggal_waktu"
                                name="tanggal_waktu"
                                value="{{ old('tanggal_waktu') }}"
                                min="{{ now()->format('Y-m-d\TH:i') }}"
                                class="input input-bordered w-full
                                    @error('tanggal_waktu') input-error @enderror"
                                required
                            >

                            @error('tanggal_waktu')
                                <p class="text-error text-sm">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Image Upload --}}
                        <div class="space-y-2 md:col-span-2">
                            <label for="gambar" class="block">
                                <span class="text-sm font-medium">
                                    Gambar Event
                                </span>
                            </label>

                            <input
                                type="file"
                                id="gambar"
                                name="gambar"
                                accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                                class="file-input file-input-bordered w-full
                                    @error('gambar') file-input-error @enderror"
                            >

                            <p class="text-xs text-gray-500">
                                Format JPG, JPEG, atau PNG. Ukuran maksimal
                                2 MB. Jika dikosongkan, gambar default akan
                                digunakan.
                            </p>

                            @error('gambar')
                                <p class="text-error text-sm">
                                    {{ $message }}
                                </p>
                            @enderror

                            {{-- Image Preview --}}
                            <div
                                id="image-preview-container"
                                class="hidden mt-4"
                            >
                                <p class="text-sm font-medium mb-2">
                                    Preview Gambar
                                </p>

                                <div
                                    class="relative inline-block
                                           rounded-xl overflow-hidden
                                           border border-gray-200"
                                >
                                    <img
                                        id="image-preview"
                                        src=""
                                        alt="Preview gambar event"
                                        class="w-full max-w-md h-64
                                               object-cover"
                                    >

                                    <button
                                        type="button"
                                        id="remove-image-preview"
                                        class="btn btn-sm btn-circle
                                               btn-error text-white
                                               absolute top-2 right-2"
                                        title="Hapus gambar"
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
                                placeholder="Tuliskan informasi lengkap mengenai event..."
                                class="textarea textarea-bordered w-full
                                    @error('deskripsi') textarea-error @enderror"
                                required
                            >{{ old('deskripsi') }}</textarea>

                            @error('deskripsi')
                                <p class="text-error text-sm">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Ticket Card --}}
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
                                Event harus mempunyai minimal satu tiket.
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

                    {{-- Dynamic Ticket Container --}}
                    <div
                        id="tickets-container"
                        class="space-y-5"
                    ></div>
                </div>
            </div>

            {{-- Form Actions --}}
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
                    id="submit-button"
                    class="btn btn-primary"
                >
                    Simpan Event
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const ticketsContainer = document.getElementById(
                'tickets-container'
            );

            const addTicketButton = document.getElementById(
                'add-ticket-button'
            );

            const imageInput = document.getElementById('gambar');

            const imagePreviewContainer = document.getElementById(
                'image-preview-container'
            );

            const imagePreview = document.getElementById(
                'image-preview'
            );

            const removeImagePreviewButton = document.getElementById(
                'remove-image-preview'
            );

            /*
             * Mengambil old input jika validasi sebelumnya gagal.
             * Jika tidak ada, satu tiket reguler akan ditambahkan.
             */
            const initialTickets = @js(
                old('tikets', [
                    [
                        'tipe' => 'reguler',
                        'harga' => '',
                        'stok' => '',
                    ],
                ])
            );

            /*
             * Daftar error dari Laravel agar error tiket
             * dapat ditampilkan pada card yang sesuai.
             */
            const validationErrors = @js($errors->toArray());

            let ticketCounter = 0;

            /**
             * Mengamankan teks sebelum dimasukkan
             * ke dalam HTML JavaScript.
             */
            function escapeHtml(value) {
                const element = document.createElement('div');

                element.textContent = value ?? '';

                return element.innerHTML;
            }

            /**
             * Mengambil pesan validasi tiket.
             */
            function getTicketError(index, field) {
                const key = `tikets.${index}.${field}`;

                if (
                    validationErrors[key] &&
                    validationErrors[key].length > 0
                ) {
                    return validationErrors[key][0];
                }

                return '';
            }

            /**
             * Membuat satu ticket card.
             */
            function addTicket(ticket = {}, oldIndex = null) {
                const inputIndex = ticketCounter;
                const validationIndex = oldIndex ?? inputIndex;

                ticketCounter++;

                const type = ticket.tipe ?? 'reguler';
                const price = ticket.harga ?? '';
                const stock = ticket.stok ?? '';

                const typeError = getTicketError(
                    validationIndex,
                    'tipe'
                );

                const priceError = getTicketError(
                    validationIndex,
                    'harga'
                );

                const stockError = getTicketError(
                    validationIndex,
                    'stok'
                );

                const card = document.createElement('div');

                card.className =
                    'ticket-card border border-gray-200 ' +
                    'rounded-xl p-5 bg-gray-50';

                card.dataset.ticketIndex = inputIndex;

                card.innerHTML = `
                    <div
                        class="flex items-center justify-between
                               gap-4 mb-5"
                    >
                        <div>
                            <h4
                                class="ticket-title
                                       font-semibold text-lg"
                            >
                                Tiket
                            </h4>

                            <p class="text-xs text-gray-500">
                                Tentukan tipe, harga, dan stok tiket.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="remove-ticket-button
                                   btn btn-sm btn-error
                                   text-white"
                        >
                            Hapus
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
                                name="tikets[${inputIndex}][tipe]"
                                class="select select-bordered w-full
                                    ${typeError ? 'select-error' : ''}"
                                required
                            >
                                <option
                                    value="reguler"
                                    ${type === 'reguler' ? 'selected' : ''}
                                >
                                    Reguler
                                </option>

                                <option
                                    value="premium"
                                    ${type === 'premium' ? 'selected' : ''}
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
                                    name="tikets[${inputIndex}][harga]"
                                    value="${escapeHtml(price)}"
                                    min="0"
                                    step="0.01"
                                    placeholder="100000"
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
                                name="tikets[${inputIndex}][stok]"
                                value="${escapeHtml(stock)}"
                                min="0"
                                step="1"
                                placeholder="100"
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

                ticketsContainer.appendChild(card);

                updateTicketNumbers();
                updateRemoveButtons();
            }

            /**
             * Memperbarui nomor card tiket.
             */
            function updateTicketNumbers() {
                const cards = ticketsContainer.querySelectorAll(
                    '.ticket-card'
                );

                cards.forEach((card, index) => {
                    const title = card.querySelector(
                        '.ticket-title'
                    );

                    title.textContent = `Tiket #${index + 1}`;
                });
            }

            /**
             * Tombol hapus dinonaktifkan jika hanya
             * tersisa satu tiket.
             */
            function updateRemoveButtons() {
                const cards = ticketsContainer.querySelectorAll(
                    '.ticket-card'
                );

                const removeButtons =
                    ticketsContainer.querySelectorAll(
                        '.remove-ticket-button'
                    );

                removeButtons.forEach((button) => {
                    button.disabled = cards.length <= 1;

                    button.title = cards.length <= 1
                        ? 'Event harus memiliki minimal satu tiket'
                        : 'Hapus tiket';
                });
            }

            /**
             * Tambahkan ticket card ketika tombol diklik.
             */
            addTicketButton.addEventListener('click', () => {
                addTicket({
                    tipe: 'reguler',
                    harga: '',
                    stok: '',
                });
            });

            /**
             * Event delegation untuk menghapus ticket card.
             */
            ticketsContainer.addEventListener('click', (event) => {
                const removeButton = event.target.closest(
                    '.remove-ticket-button'
                );

                if (!removeButton) {
                    return;
                }

                const cards = ticketsContainer.querySelectorAll(
                    '.ticket-card'
                );

                if (cards.length <= 1) {
                    return;
                }

                removeButton.closest('.ticket-card').remove();

                updateTicketNumbers();
                updateRemoveButtons();
            });

            /**
             * Menampilkan tiket awal atau old input.
             */
            if (
                Array.isArray(initialTickets) &&
                initialTickets.length > 0
            ) {
                initialTickets.forEach((ticket, index) => {
                    addTicket(ticket, index);
                });
            } else {
                addTicket({
                    tipe: 'reguler',
                    harga: '',
                    stok: '',
                });
            }

            /**
             * Preview gambar menggunakan FileReader.
             */
            imageInput.addEventListener('change', () => {
                const file = imageInput.files[0];

                if (!file) {
                    hideImagePreview();

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
                    hideImagePreview();

                    return;
                }

                if (file.size > 2 * 1024 * 1024) {
                    alert(
                        'Ukuran gambar maksimal 2 MB.'
                    );

                    imageInput.value = '';
                    hideImagePreview();

                    return;
                }

                const reader = new FileReader();

                reader.onload = (event) => {
                    imagePreview.src = event.target.result;

                    imagePreviewContainer.classList.remove(
                        'hidden'
                    );
                };

                reader.readAsDataURL(file);
            });

            /**
             * Menghapus pilihan dan preview gambar.
             */
            removeImagePreviewButton.addEventListener(
                'click',
                () => {
                    imageInput.value = '';

                    hideImagePreview();
                }
            );

            function hideImagePreview() {
                imagePreview.src = '';

                imagePreviewContainer.classList.add('hidden');
            }
        });
    </script>
@endpush
