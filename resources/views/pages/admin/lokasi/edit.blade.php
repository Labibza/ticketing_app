@extends('layouts.admin_layouts')

@section('title', 'Edit Lokasi')

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="mb-6">
            <h1 class="text-3xl font-bold">
                Edit Lokasi
            </h1>

            <p class="mt-1 text-gray-500">
                Perbarui nama atau status lokasi.
            </p>
        </div>

        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <form
                    action="{{ route(
                        'admin.lokasi.update',
                        $lokasi
                    ) }}"
                    method="POST"
                >
                    @csrf
                    @method('PUT')

                    @include(
                        'pages.admin.lokasi._form',
                        [
                            'submitLabel' => 'Simpan Perubahan',
                        ]
                    )
                </form>
            </div>
        </div>
    </div>
@endsection
