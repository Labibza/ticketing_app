<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventFormRequest extends FormRequest
{
    /**
     * Menentukan pengguna yang boleh mengirim request.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    /**
     * Aturan validasi event dan tiket.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            /*
            |--------------------------------------------------------------------------
            | Validasi data event
            |--------------------------------------------------------------------------
            */

            'judul' => [
                'required',
                'string',
                'max:255',
            ],

            'deskripsi' => [
                'required',
                'string',
            ],

            'lokasi' => [
                'required',
                'string',
                'max:255',
            ],

            'kategori_id' => [
                'required',
                'integer',
                'exists:kategoris,id',
            ],

            'tanggal_waktu' => [
                'required',
                'date',
                'after:now',
            ],

            'gambar' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png',
                'max:2048',
            ],

            /*
            |--------------------------------------------------------------------------
            | Validasi array tiket
            |--------------------------------------------------------------------------
            */

            'tikets' => [
                'required',
                'array',
                'min:1',
            ],

            'tikets.*.id' => [
                'nullable',
                'integer',
                'exists:tikets,id',
            ],

            'tikets.*.tipe' => [
                'required',
                'string',
                'in:reguler,premium',
            ],

            'tikets.*.harga' => [
                'required',
                'numeric',
                'min:0',
            ],

            'tikets.*.stok' => [
                'required',
                'integer',
                'min:0',
            ],
        ];
    }

    /**
     * Pesan kesalahan validasi dalam Bahasa Indonesia.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            /*
            |--------------------------------------------------------------------------
            | Pesan validasi event
            |--------------------------------------------------------------------------
            */

            'judul.required' =>
                'Judul event wajib diisi.',

            'judul.string' =>
                'Judul event harus berupa teks.',

            'judul.max' =>
                'Judul event maksimal 255 karakter.',

            'deskripsi.required' =>
                'Deskripsi event wajib diisi.',

            'deskripsi.string' =>
                'Deskripsi event harus berupa teks.',

            'lokasi.required' =>
                'Lokasi event wajib diisi.',

            'lokasi.string' =>
                'Lokasi event harus berupa teks.',

            'lokasi.max' =>
                'Lokasi event maksimal 255 karakter.',

            'kategori_id.required' =>
                'Kategori event wajib dipilih.',

            'kategori_id.integer' =>
                'Kategori event tidak valid.',

            'kategori_id.exists' =>
                'Kategori event yang dipilih tidak ditemukan.',

            'tanggal_waktu.required' =>
                'Tanggal dan waktu event wajib diisi.',

            'tanggal_waktu.date' =>
                'Format tanggal dan waktu event tidak valid.',

            'tanggal_waktu.after' =>
                'Tanggal dan waktu event harus setelah waktu sekarang.',

            'gambar.image' =>
                'File yang diunggah harus berupa gambar.',

            'gambar.mimes' =>
                'Gambar event harus berformat JPG, JPEG, atau PNG.',

            'gambar.max' =>
                'Ukuran gambar event maksimal 2 MB.',

            /*
            |--------------------------------------------------------------------------
            | Pesan validasi tiket
            |--------------------------------------------------------------------------
            */

            'tikets.required' =>
                'Event harus memiliki minimal satu tiket.',

            'tikets.array' =>
                'Data tiket harus berupa daftar tiket.',

            'tikets.min' =>
                'Event harus memiliki minimal satu tiket.',

            'tikets.*.id.integer' =>
                'ID tiket tidak valid.',

            'tikets.*.id.exists' =>
                'Tiket yang dipilih tidak ditemukan.',

            'tikets.*.tipe.required' =>
                'Tipe tiket wajib dipilih.',

            'tikets.*.tipe.string' =>
                'Tipe tiket harus berupa teks.',

            'tikets.*.tipe.in' =>
                'Tipe tiket hanya boleh reguler atau premium.',

            'tikets.*.harga.required' =>
                'Harga tiket wajib diisi.',

            'tikets.*.harga.numeric' =>
                'Harga tiket harus berupa angka.',

            'tikets.*.harga.min' =>
                'Harga tiket tidak boleh kurang dari Rp0.',

            'tikets.*.stok.required' =>
                'Stok tiket wajib diisi.',

            'tikets.*.stok.integer' =>
                'Stok tiket harus berupa bilangan bulat.',

            'tikets.*.stok.min' =>
                'Stok tiket tidak boleh kurang dari 0.',
        ];
    }

    /**
     * Nama atribut yang lebih mudah dibaca pada pesan bawaan Laravel.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'judul' => 'judul event',
            'deskripsi' => 'deskripsi event',
            'lokasi' => 'lokasi event',
            'kategori_id' => 'kategori event',
            'tanggal_waktu' => 'tanggal dan waktu event',
            'gambar' => 'gambar event',
            'tikets' => 'daftar tiket',
            'tikets.*.id' => 'ID tiket',
            'tikets.*.tipe' => 'tipe tiket',
            'tikets.*.harga' => 'harga tiket',
            'tikets.*.stok' => 'stok tiket',
        ];
    }
}