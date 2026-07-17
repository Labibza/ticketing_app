@extends('layouts.admin_layouts')

@section('title', 'Tambah Lokasi')

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="mb-6">
            <h1 class="text-3xl font-bold">
                Tambah Lokasi
            </h1>

            <p class="mt-1 text-gray-500">
                Tambahkan lokasi baru untuk pelaksanaan event.
            </p>
        </div>

        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <form
                    action="{{ route('admin.lokasi.store') }}"
                    method="POST"
                >
                    @csrf

                    @include(
                        'pages.admin.lokasi._form',
                        [
                            'submitLabel' => 'Simpan Lokasi',
                        ]
                    )
                </form>
            </div>
        </div>
    </div>
@endsection
