<?php

namespace App\Http\Controllers;

use App\Http\Requests\LokasiFormRequest;
use App\Models\Lokasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LokasiController extends Controller
{
    /**
     * Menampilkan daftar lokasi.
     */
    public function index(
        Request $request
    ): View {
        $query = Lokasi::query();

        /*
         * Pencarian berdasarkan nama lokasi.
         */
        if ($request->filled('search')) {
            $query->where(
                'nama_lokasi',
                'like',
                '%' . $request->search . '%'
            );
        }

        /*
         * Filter berdasarkan status aktif.
         */
        if ($request->filled('aktif')) {
            $query->where(
                'aktif',
                $request->aktif
            );
        }

        $lokasis = $query
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return view(
            'pages.admin.lokasi.index',
            compact('lokasis')
        );
    }

    /**
     * Menampilkan form tambah lokasi.
     */
    public function create(): View
    {
        return view(
            'pages.admin.lokasi.create'
        );
    }

    /**
     * Menyimpan lokasi baru.
     */
    public function store(
        LokasiFormRequest $request
    ): RedirectResponse {
        $data = $request->validated();

        /*
         * Jika status tidak dikirim,
         * lokasi dianggap aktif.
         */
        $data['aktif'] = $data['aktif'] ?? 'Y';

        Lokasi::create($data);

        return redirect()
            ->route('admin.lokasi.index')
            ->with(
                'success',
                'Lokasi berhasil ditambahkan.'
            );
    }

    /**
     * Menampilkan form edit lokasi.
     */
    public function edit(
        Lokasi $lokasi
    ): View {
        return view(
            'pages.admin.lokasi.edit',
            compact('lokasi')
        );
    }

    /**
     * Memperbarui data lokasi.
     */
    public function update(
        LokasiFormRequest $request,
        Lokasi $lokasi
    ): RedirectResponse {
        $lokasi->update(
            $request->validated()
        );

        return redirect()
            ->route('admin.lokasi.index')
            ->with(
                'success',
                'Lokasi berhasil diperbarui.'
            );
    }

    /**
     * Menghapus data lokasi.
     */
    public function destroy(
        Lokasi $lokasi
    ): RedirectResponse {
        $lokasi->delete();

        return redirect()
            ->route('admin.lokasi.index')
            ->with(
                'success',
                'Lokasi berhasil dihapus.'
            );
    }
}
