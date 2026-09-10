<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Presentation;
use App\Models\Store;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClasificacionTest extends TestCase
{
    use RefreshDatabase;

    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();
        $store = Store::create(['code' => 'TDE-CLA', 'name' => 'Tienda Clasificación']);
        $warehouse = Warehouse::create(['code' => 'ALM-CLA', 'name' => 'Almacén Central']);
        $presentation = Presentation::create([
            'name' => 'Sopa Maruchan',
            'presentation_type' => 'vaso',
            'flavor' => 'Pollo',
            'sku' => 'MAR-CLA-01',
        ]);
        $creator = User::factory()->create(['role' => 'ventas']);

        $this->order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'store_id' => $store->id,
            'user_id' => $creator->id,
            'warehouse_id' => $warehouse->id,
            'status' => Order::STATUS_RECIBIDO,
            'ordered_at' => now()->subDay(),
            'received_at' => now()->subDay(),
        ]);
        $this->order->items()->create([
            'presentation_id' => $presentation->id,
            'quantity_requested' => 10,
            'quantity_received' => 10,
        ]);
    }

    public function test_almacen_can_classify_an_order(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'almacen']));

        $response = $this->post(route('almacen.pedidos.clasificar.guardar', $this->order), [
            'location' => 'Zona A, Pasillo 3',
        ]);

        $response->assertRedirect(route('almacen.pendientes'));

        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'status' => Order::STATUS_CLASIFICADO,
        ]);
        $this->assertDatabaseHas('movements', [
            'trackable_type' => Order::class,
            'trackable_id' => $this->order->id,
            'state' => Order::STATUS_CLASIFICADO,
            'action' => 'Mercancía clasificada y ubicada en almacén',
        ]);
    }

    public function test_cannot_classify_an_order_not_received(): void
    {
        $this->order->update(['status' => Order::STATUS_EN_ALMACEN]);

        $this->actingAs(User::factory()->create(['role' => 'almacen']));

        $this->post(route('almacen.pedidos.clasificar.guardar', $this->order), [
            'location' => 'Zona A',
        ])->assertRedirect(route('almacen.pendientes'));

        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'status' => Order::STATUS_EN_ALMACEN,
        ]);
        $this->assertDatabaseMissing('movements', [
            'trackable_type' => Order::class,
            'trackable_id' => $this->order->id,
            'state' => Order::STATUS_CLASIFICADO,
        ]);
    }

    public function test_location_is_required(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'almacen']));

        $this->post(route('almacen.pedidos.clasificar.guardar', $this->order), [
            'location' => '',
        ])->assertSessionHasErrors('location');

        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'status' => Order::STATUS_RECIBIDO,
        ]);
    }

    public function test_classification_view_shows_order(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'almacen']));

        $this->get(route('almacen.pedidos.clasificar', $this->order))
            ->assertOk()
            ->assertSee($this->order->order_number)
            ->assertSee('Ubicación en almacén');
    }

    public function test_only_almacen_and_admin_can_classify(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'ventas']));

        $this->get(route('almacen.pedidos.clasificar', $this->order))->assertForbidden();
        $this->post(route('almacen.pedidos.clasificar.guardar', $this->order), [
            'location' => 'Zona A',
        ])->assertForbidden();
    }

    public function test_admin_can_classify_an_order(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->post(route('almacen.pedidos.clasificar.guardar', $this->order), [
            'location' => 'Zona B, Pasillo 1',
        ])->assertRedirect(route('almacen.pendientes'));

        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'status' => Order::STATUS_CLASIFICADO,
        ]);
    }
}