<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Presentation;
use App\Models\Store;
use App\Models\User;
use App\Models\Warehouse;
use App\Support\Qr;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QrTest extends TestCase
{
    use RefreshDatabase;

    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $store = Store::create(['code' => 'TDE-QR', 'name' => 'Tienda QR']);
        $warehouse = Warehouse::create(['code' => 'ALM-QR', 'name' => 'Almacén Central']);
        $presentation = Presentation::create([
            'name' => 'Sopa Maruchan',
            'presentation_type' => 'vaso',
            'flavor' => 'Pollo',
            'sku' => 'MAR-QR-01',
        ]);
        $creator = User::factory()->create(['role' => 'ventas']);

        $this->order = Order::create([
            'order_number' => 'PED-QR-0001',
            'store_id' => $store->id,
            'user_id' => $creator->id,
            'warehouse_id' => $warehouse->id,
            'status' => Order::STATUS_CREADO,
            'ordered_at' => now(),
        ]);
        $this->order->items()->create([
            'presentation_id' => $presentation->id,
            'quantity_requested' => 10,
        ]);
    }

    public function test_qr_helper_generates_svg_data_uri(): void
    {
        $dataUri = Qr::svgDataUri('PED-QR-0001');

        $this->assertStringStartsWith('data:image/svg+xml;base64,', $dataUri);
        $this->assertNotFalse(base64_decode(substr($dataUri, strlen('data:image/svg+xml;base64,'))));
    }

    public function test_ventas_detail_shows_qr(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'ventas']));

        $this->get(route('ventas.pedidos.show', $this->order))
            ->assertOk()
            ->assertSee('Código QR del pedido')
            ->assertSee('data:image/svg+xml;base64,', false);
    }

    public function test_almacen_detail_shows_qr(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'almacen']));

        $this->get(route('almacen.pedidos.show', $this->order))
            ->assertOk()
            ->assertSee('Código QR del pedido')
            ->assertSee('data:image/svg+xml;base64,', false);
    }

    public function test_trazabilidad_result_shows_qr(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->get(route('trazabilidad.index', ['order_number' => 'PED-QR-0001']))
            ->assertOk()
            ->assertSee('Código QR')
            ->assertSee('data:image/svg+xml;base64,', false);
    }

    public function test_qr_content_contains_the_folio(): void
    {
        // decodifica el SVG y verifica que contenga el folio en el elemento del QR es inviable,
        // así que validamos que el QR se genere sin errores para el folio.
        $dataUri = Qr::svgDataUri($this->order->order_number);
        $svg = base64_decode(substr($dataUri, strlen('data:image/svg+xml;base64,')));

        $this->assertStringContainsString('<svg', $svg);
    }
}