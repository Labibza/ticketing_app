@extends('layouts.admin_layouts')

@section('title', 'Management Lokasi')

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div
            class="flex flex-col justify-between gap-4
                md:flex-row md:items-center"
        >
            <div>
                <h1 class="text-3xl font-bold">
                    Management Lokasi
                </h1>

                <p class="mt-1 text-gray-500">
                    Kelola lokasi yang digunakan dalam event.
                </p>
            </div>

            <a
                href="{{ route('admin.lokasi.create') }}"
                class="btn btn-primary"
            >
                + Tambah Lokasi
            </a>
        </div>

        {{-- Notifikasi --}}
        @if (session('success'))
            <div class="alert alert-success">
                <span>
                    {{ session('success') }}
                </span>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error">
                <span>
                    {{ session('error') }}
                </span>
            </div>
        @endif

        {{-- Filter --}}
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <form
                    action="{{ route('admin.lokasi.index') }}"
                    method="GET"
                    class="grid grid-cols-1 gap-3 md:grid-cols-4"
                >
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari nama lokasi..."
                        class="input input-bordered md:col-span-2"
                    >

                    <select
                        name="aktif"
                        class="select select-bordered"
                    >
                        <option value="">
                            Semua Status
                        </option>

                        <option
                            value="Y"
                            @selected(request('aktif') === 'Y')
                        >
                            Aktif
                        </option>

                        <option
                            value="N"
                            @selected(request('aktif') === 'N')
                        >
                            Tidak Aktif
                        </option>
                    </select>

                    <div class="flex gap-2">
                        <button
                            type="submit"
                            class="btn btn-primary flex-1"
                        >
                            Filter
                        </button>

                        <a
                            href="{{ route('admin.lokasi.index') }}"
                            class="btn btn-ghost"
                        >
                            Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tabel lokasi --}}
        <div class="card bg-base-100 shadow">
            <div class="card-body overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th class="w-16">
                                No.
                            </th>

                            <th>
                                Nama Lokasi
                            </th>

                            <th>
                                Aktif
                            </th>

                            <th>
                                Status
                            </th>

                            <th class="text-center">
                                Aksi
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($lokasis as $lokasi)
                            <tr>
                                <td>
                                    {{ $lokasis->firstItem() + $loop->index }}
                                </td>

                                <td class="font-semibold">
                                    {{ $lokasi->nama_lokasi }}
                                </td>

                                <td>
                                    {{ $lokasi->aktif }}
                                </td>

                                <td>
                                    @if ($lokasi->aktif === 'Y')
                                        <span class="badge badge-success">
                                            Aktif
                                        </span>
                                    @else
                                        <span class="badge badge-ghost">
                                            Tidak Aktif
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <div
                                        class="flex justify-center gap-2"
                                    >
                                        <a
                                            href="{{ route(
                                                'admin.lokasi.edit',
                                                $lokasi
                                            ) }}"
                                            class="btn btn-sm btn-warning"
                                        >
                                            Edit
                                        </a>

                                        <form
                                            action="{{ route(
                                                'admin.lokasi.destroy',
                                                $lokasi
                                            ) }}"
                                            method="POST"
                                            onsubmit="
                                                return confirm(
                                                    'Hapus lokasi {{ addslashes($lokasi->nama_lokasi) }}?'
                                                );
                                            "
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-error"
                                            >
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="5"
                                    class="py-10 text-center text-gray-500"
                                >
                                    Data lokasi belum tersedia.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if ($lokasis->hasPages())
                    <div class="mt-6">
                        {{ $lokasis->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
