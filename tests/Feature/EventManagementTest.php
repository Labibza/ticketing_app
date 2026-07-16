<?php

namespace Tests\Feature;

use App\Models\DetailOrder;
use App\Models\Event;
use App\Models\Kategori;
use App\Models\Order;
use App\Models\Tiket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EventManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;
    private Kategori $kategori;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin-test@example.com',
            'password' => 'password',
            'role' => 'admin',
        ]);

        $this->user = User::create([
            'name' => 'User Test',
            'email' => 'user-test@example.com',
            'password' => 'password',
            'role' => 'user',
        ]);

        $this->kategori = Kategori::create([
            'nama' => 'Konser',
        ]);
    }

    public function test_admin_can_create_event_with_tickets(): void
    {
        Storage::fake('public');

        $response = $this
            ->actingAs($this->admin)
            ->post(route('admin.events.store'), [
                'judul' => 'Konser Testing',
                'deskripsi' => 'Deskripsi konser testing.',
                'lokasi' => 'Semarang',
                'kategori_id' => $this->kategori->id,
                'tanggal_waktu' => now()
                    ->addDays(7)
                    ->format('Y-m-d H:i:s'),
                'gambar' => UploadedFile::fake()
                    ->image('event.jpg')
                    ->size(500),
                'tikets' => [
                    [
                        'tipe' => 'reguler',
                        'harga' => 100000,
                        'stok' => 100,
                    ],
                    [
                        'tipe' => 'premium',
                        'harga' => 250000,
                        'stok' => 50,
                    ],
                ],
            ]);

        $response
            ->assertRedirect(
                route('admin.events.index')
            )
            ->assertSessionHas('success');

        $this->assertDatabaseHas('events', [
            'judul' => 'Konser Testing',
            'kategori_id' => $this->kategori->id,
        ]);

        $this->assertDatabaseCount('tikets', 2);

        $event = Event::where(
            'judul',
            'Konser Testing'
        )->firstOrFail();

        Storage::disk('public')->assertExists(
            $event->gambar
        );
    }

    public function test_admin_can_search_event(): void
    {
        $this->createEvent(
            'Konser Semarang',
            'Marina'
        );

        $this->createEvent(
            'Seminar Teknologi',
            'Jakarta'
        );

        $response = $this
            ->actingAs($this->admin)
            ->get(
                route('admin.events.index', [
                    'search' => 'Semarang',
                ])
            );

        $response
            ->assertOk()
            ->assertSee('Konser Semarang')
            ->assertDontSee('Seminar Teknologi');
    }

    public function test_admin_can_update_event_and_add_ticket(): void
    {
        $event = $this->createEvent(
            'Event Lama',
            'Semarang'
        );

        $ticket = $event->tikets()->create([
            'tipe' => 'reguler',
            'harga' => 100000,
            'stok' => 100,
        ]);

        $response = $this
            ->actingAs($this->admin)
            ->put(
                route('admin.events.update', $event),
                [
                    'judul' => 'Event Baru',
                    'deskripsi' => 'Deskripsi baru.',
                    'lokasi' => 'Solo',
                    'kategori_id' => $this->kategori->id,
                    'tanggal_waktu' => now()
                        ->addDays(10)
                        ->format('Y-m-d H:i:s'),
                    'tikets' => [
                        [
                            'id' => $ticket->id,
                            'tipe' => 'reguler',
                            'harga' => 125000,
                            'stok' => 80,
                        ],
                        [
                            'tipe' => 'premium',
                            'harga' => 300000,
                            'stok' => 40,
                        ],
                    ],
                ]
            );

        $response
            ->assertRedirect(
                route('admin.events.index')
            )
            ->assertSessionHas('success');

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'judul' => 'Event Baru',
            'lokasi' => 'Solo',
        ]);

        $this->assertDatabaseHas('tikets', [
            'event_id' => $event->id,
            'tipe' => 'premium',
            'harga' => 300000,
        ]);
    }

    public function test_admin_can_delete_event_without_sales(): void
    {
        $event = $this->createEvent(
            'Event Dihapus',
            'Semarang'
        );

        $event->tikets()->create([
            'tipe' => 'reguler',
            'harga' => 100000,
            'stok' => 100,
        ]);

        $response = $this
            ->actingAs($this->admin)
            ->delete(
                route('admin.events.destroy', $event)
            );

        $response
            ->assertRedirect(
                route('admin.events.index')
            )
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('events', [
            'id' => $event->id,
        ]);

        $this->assertDatabaseMissing('tikets', [
            'event_id' => $event->id,
        ]);
    }

    public function test_event_with_sales_cannot_be_deleted(): void
    {
        $event = $this->createEvent(
            'Event Terjual',
            'Semarang'
        );

        $ticket = $event->tikets()->create([
            'tipe' => 'reguler',
            'harga' => 100000,
            'stok' => 100,
        ]);

        $order = Order::create([
            'user_id' => $this->user->id,
            'event_id' => $event->id,
            'order_date' => now(),
            'total_harga' => 100000,
        ]);

        DetailOrder::create([
            'order_id' => $order->id,
            'tiket_id' => $ticket->id,
            'jumlah' => 1,
            'subtotal_harga' => 100000,
        ]);

        $response = $this
            ->actingAs($this->admin)
            ->delete(
                route('admin.events.destroy', $event)
            );

        $response
            ->assertRedirect(
                route('admin.events.index')
            )
            ->assertSessionHas('error');

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
        ]);
    }

    private function createEvent(
        string $judul,
        string $lokasi
    ): Event {
        return Event::create([
            'user_id' => $this->admin->id,
            'kategori_id' => $this->kategori->id,
            'judul' => $judul,
            'deskripsi' => 'Deskripsi event testing.',
            'lokasi' => $lokasi,
            'gambar' => 'konser.jpg',
            'tanggal_waktu' => now()->addDays(7),
        ]);
    }
}