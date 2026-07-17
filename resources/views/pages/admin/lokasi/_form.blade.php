<div class="space-y-5">
    {{-- Nama lokasi --}}
    <div class="form-control">
        <label
            for="nama_lokasi"
            class="label"
        >
            <span class="label-text font-semibold">
                Nama Lokasi
            </span>
        </label>

        <input
            type="text"
            id="nama_lokasi"
            name="nama_lokasi"
            value="{{ old('nama_lokasi', $lokasi->nama_lokasi ?? '') }}"
            placeholder="Contoh: Stadion Utama"
            maxlength="255"
            class="input input-bordered w-full
                @error('nama_lokasi') input-error @enderror"
            required
        >

        @error('nama_lokasi')
            <span class="mt-1 text-sm text-error">
                {{ $message }}
            </span>
        @enderror
    </div>

    {{-- Status aktif --}}
    <div class="form-control">
        <label
            for="aktif"
            class="label"
        >
            <span class="label-text font-semibold">
                Status
            </span>
        </label>

        <select
            id="aktif"
            name="aktif"
            class="select select-bordered w-full
                @error('aktif') select-error @enderror"
            required
        >
            <option
                value="Y"
                @selected(
                    old(
                        'aktif',
                        $lokasi->aktif ?? 'Y'
                    ) === 'Y'
                )
            >
                Aktif
            </option>

            <option
                value="N"
                @selected(
                    old(
                        'aktif',
                        $lokasi->aktif ?? 'Y'
                    ) === 'N'
                )
            >
                Tidak Aktif
            </option>
        </select>

        <p class="mt-1 text-sm text-gray-500">
            Y berarti aktif, sedangkan N berarti tidak aktif.
        </p>

        @error('aktif')
            <span class="mt-1 text-sm text-error">
                {{ $message }}
            </span>
        @enderror
    </div>
</div>

<div class="mt-8 flex justify-end gap-3">
    <a
        href="{{ route('admin.lokasi.index') }}"
        class="btn btn-ghost"
    >
        Batal
    </a>

    <button
        type="submit"
        class="btn btn-primary"
    >
        {{ $submitLabel }}
    </button>
</div>
