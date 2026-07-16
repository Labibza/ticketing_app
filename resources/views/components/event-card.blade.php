@props([
    'title',
    'date',
    'location',
    'price',
    'image',
    'href' => null,
])

@php
    // Format Indonesian price
    $formattedPrice = $price
        ? 'Rp ' . number_format($price, 0, ',', '.')
        : 'Harga tidak tersedia';

    // Format Indonesian date
    $formattedDate = $date
        ? \Carbon\Carbon::parse($date)->locale('id')->translatedFormat('d F Y, H:i')
        : 'Tanggal tidak tersedia';

        
    $image = trim((string) ($image ?? ''));

    if (
        $image !== '' &&
        filter_var($image, FILTER_VALIDATE_URL)
    ) {
        /*
         * Gambar berasal dari URL eksternal.
         */
        $imageUrl = $image;
    } else {
        /*
         * Normalisasi data lama, misalnya:
         * storage/events/gambar.jpg
         * menjadi:
         * events/gambar.jpg
         */
        $imagePath = preg_replace(
            '#^/?storage/#',
            '',
            $image
        );

        $disk = \Illuminate\Support\Facades\Storage::disk(
            'public'
        );

        if (
            $imagePath !== '' &&
            $disk->exists($imagePath)
        ) {
            $imageUrl = $disk->url($imagePath);
        } else {
            $imageUrl = $disk->url('konser.jpg');
        }
    }
@endphp

<a href="{{ $href ?? '#' }}" class="block">
  <div class="card bg-base-100 h-96 shadow-sm hover:shadow-md transition-shadow duration-300">
      <figure>
          <img src="{{ $imageUrl }}" alt="{{ $title }}" class="w-full h-48 object-cover" loading="lazy" />
      </figure>

      <div class="card-body">
          <h2 class="card-title">
              {{ $title }}
          </h2>

          <p class="text-sm text-gray-500">
              {{ $formattedDate }}
          </p>

          <p class="text-sm">
              📍 {{ $location }}
          </p>

          <p class="font-bold text-lg mt-2">
              {{ $formattedPrice }}
          </p>

      </div>
  </div>
</a>
