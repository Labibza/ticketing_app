<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LokasiFormRequest extends FormRequest
{
    /**
     * Hanya admin yang diperbolehkan mengelola lokasi.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    /**
     * Aturan validasi data lokasi.
     */
    public function rules(): array
    {
        $lokasi = $this->route('lokasi');

        return [
            'nama_lokasi' => [
                'required',
                'string',
                'max:255',

                Rule::unique(
                    'lokasi',
                    'nama_lokasi'
                )->ignore(
                    $lokasi?->id
                ),
            ],

            'aktif' => [
                'required',
                Rule::in([
                    'Y',
                    'N',
                ]),
            ],
        ];
    }

    /**
     * Pesan validasi dalam Bahasa Indonesia.
     */
    public function messages(): array
    {
        return [
            'nama_lokasi.required' =>
                'Nama lokasi wajib diisi.',

            'nama_lokasi.string' =>
                'Nama lokasi harus berupa teks.',

            'nama_lokasi.max' =>
                'Nama lokasi maksimal 255 karakter.',

            'nama_lokasi.unique' =>
                'Nama lokasi tersebut sudah tersedia.',

            'aktif.required' =>
                'Status lokasi wajib dipilih.',

            'aktif.in' =>
                'Status lokasi hanya boleh Y atau N.',
        ];
    }
}
