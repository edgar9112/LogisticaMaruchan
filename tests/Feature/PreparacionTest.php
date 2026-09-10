<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Presentation;
use App\Models\Store;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PreparacionTest extends TestCase
{
    use RefreshDatabase;

    private Order $order;
    private $item;

    protected function setUp(): void
    {
        parent::setUp();
        $store = Store::create(['code' => 'TDE-PRE', 'name' => 'Tienda Preparación']);
        $warehouse = Warehouse::create(['code' => 'ALM-PRE', 'name' => 'Almacén Central']);
        $presentation = Presentation::create([
            'name' => 'Sopa Maruchan',
            'presentation_type' => 'vaso',
            'flavor' => 'Pollo',
            'sku' => 'MAR-PRE-01',
        ]);
        $creator = User::factory()->create(['role' => 'ventas']);

        $this->order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'store_id' => $store->id,
            'user_id' => $creator->id,
            'warehouse_id' => $warehouse->id,
            'status' => Order::STATUS_CLASIFICADO,
            'ordered_at' => now()->subDay(),
            'received_at' => now()->subDay(),
        ]);
        $this->item = $this->order->items()->create([
            'presentation_id' => $presentation->id,
            'quantity_requested' => 10,
            'quantity_received' => 10,
        ]);
    }

    public function test_almacen_can_prepare_an_order(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'almacen']));

        $response = $this->post(route('almacen.pedidos.preparar.guardar', $this->order), [
            'quantities' => [$this->item->id => 8],
        ]);

        $response->assertRedirect(route('almacen.pendientes'));

        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'status' => Order::STATUS_PREPARADO,
        ]);
        $this->assertDatabaseHas('order_items', [
            'id' => $this->item->id,
            'quantity_prepared' => 8,
        ]);
        $this->assertDatabaseHas('movements', [
            'trackable_type' => Order::class,
            'trackable_id' => $this->order->id,
            'state' => Order::STATUS_PREPARADO,
            'action' => 'Pedido preparado y listo para embarque',
        ]);
    }

    public function test_cannot_prepare_an_order_not_classified(): void
    {
        $this->order->update(['status' => Order::STATUS_RECIBIDO]);

        $this->actingAs(User::factory()->create(['role' => 'almacen']));

        $this->post(route('almacen.pedidos.preparar.guardar', $this->order), [
            'quantities' => [$this->item->id => 8],
        ])->assertRedirect(route('almacen.pendientes'));

        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'status' => Order::STATUS_RECIBIDO,
        ]);
        $this->assertDatabaseMissing('movements', [
            'trackable_type' => Order::class,
            'trackable_id' => $this->order->id,
            'state' => Order::STATUS_PREPARADO,
        ]);
    }

    public function test_quantities_are_required_and_nonnegative(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'almacen']));

        $this->post(route('almacen.pedidos.preparar.guardar', $this->order), [
            'quantities' => [$this->item->id => -1],
        ])->assertSessionHasErrors('quantities.' . $this->item->id);

        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'status' => Order::STATUS_CLASIFICADO,
        ]);
    }

    public function test_prepare_view_shows_items(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'almacen']));

        $this->get(route('almacen.pedidos.preparar', $this->order))
            ->assertOk()
            ->assertSee($this->order->order_number)
            ->assertSee('Productos a preparar');
    }

    public function test_admin_can_prepare_an_order(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->post(route('almacen.pedidos.preparar.guardar', $this->order), [
            'quantities' => [$this->item->id => 10],
        ])->assertRedirect(route('almacen.pendientes'));

        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'status' => Order::STATUS_PREPARADO,
        ]);
    }

    public function test_transporte_cannot_prepare(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'transporte']));

        $this->get(route('almacen.pedidos.preparar', $this->order))->assertForbidden();
    }
}