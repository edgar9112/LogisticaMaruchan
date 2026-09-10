<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Presentation;
use App\Models\Shipment;
use App\Models\Store;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CierreTiendaTest extends TestCase
{
    use RefreshDatabase;

    private Store $store;
    private Store $otherStore;
    private Warehouse $warehouse;

    protected function setUp(): void
    {
        parent::setUp();

        $this->store = Store::create(['code' => 'TDE-CIE', 'name' => 'Tienda Cierre']);
        $this->otherStore = Store::create(['code' => 'TDE-CIE2', 'name' => 'Tienda Cierre 2']);
        $this->warehouse = Warehouse::create(['code' => 'ALM-CIE', 'name' => 'Almacén Central']);
    }

    private function makeDeliveredOrder(Store $store, ?Shipment $shipment = null): Order
    {
        $presentation = Presentation::create([
            'name' => 'Sopa Maruchan',
            'presentation_type' => 'vaso',
            'flavor' => 'Pollo',
            'sku' => 'MAR-CIE-'.rand(1000, 9999),
        ]);
        $creator = User::factory()->create(['role' => 'ventas']);

        $order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'store_id' => $store->id,
            'user_id' => $creator->id,
            'warehouse_id' => $this->warehouse->id,
            'status' => Order::STATUS_RECIBIDO_TIENDA,
            'ordered_at' => now()->subDay(),
            'received_at' => now()->subDay(),
            'prepared_at' => now()->subDay(),
            'shipped_at' => now(),
        ]);
        $order->items()->create([
            'presentation_id' => $presentation->id,
            'quantity_requested' => 10,
            'quantity_received' => 10,
            'quantity_prepared' => 10,
        ]);

        if ($shipment) {
            $shipment->orders()->attach($order->id);
        }

        return $order;
    }

    private function makeEntregadoShipment(): Shipment
    {
        return Shipment::create([
            'shipment_number' => 'EMB-CIE-'.rand(1000, 9999),
            'origin_warehouse_id' => $this->warehouse->id,
            'destination_store_id' => $this->store->id,
            'status' => Shipment::STATUS_ENTREGADO,
        ]);
    }

    private function actingAsTienda(Store $store): void
    {
        $this->actingAs(User::factory()->create([
            'role' => 'tienda',
            'store_id' => $store->id,
        ]));
    }

    public function test_tienda_sees_orders_to_confirm(): void
    {
        $order = $this->makeDeliveredOrder($this->store);
        $this->actingAsTienda($this->store);

        $this->get(route('tienda.recepciones'))
            ->assertOk()
            ->assertSee($order->order_number);
    }

    public function test_tienda_only_sees_its_own_store_orders(): void
    {
        $this->makeDeliveredOrder($this->otherStore);
        $this->actingAsTienda($this->store);

        $response = $this->get(route('tienda.recepciones'));

        $response->assertOk();
        foreach (Order::where('store_id', $this->otherStore->id)->get() as $order) {
            $response->assertDontSee($order->order_number);
        }
    }

    public function test_confirmar_closes_the_order(): void
    {
        $order = $this->makeDeliveredOrder($this->store);
        $this->actingAsTienda($this->store);

        $this->post(route('tienda.recepciones.confirmar', $order))
            ->assertRedirect(route('tienda.recepciones'));

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => Order::STATUS_CERRADO,
        ]);
        $this->assertNotNull($order->fresh()->completed_at);
        $this->assertDatabaseHas('movements', [
            'trackable_type' => Order::class,
            'trackable_id' => $order->id,
            'state' => Order::STATUS_CERRADO,
            'action' => 'Pedido recibido y cerrado en tienda',
        ]);
    }

    public function test_cannot_confirm_an_order_not_delivered(): void
    {
        $order = $this->makeDeliveredOrder($this->store);
        $order->update(['status' => Order::STATUS_EN_TRANSITO]);
        $this->actingAsTienda($this->store);

        $this->from(route('tienda.recepciones'))
            ->post(route('tienda.recepciones.confirmar', $order->fresh()))
            ->assertSessionHasErrors();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => Order::STATUS_EN_TRANSITO,
        ]);
    }

    public function test_tienda_cannot_confirm_other_stores_order(): void
    {
        $order = $this->makeDeliveredOrder($this->otherStore);
        $this->actingAsTienda($this->store);

        $this->from(route('tienda.recepciones'))
            ->post(route('tienda.recepciones.confirmar', $order))
            ->assertSessionHasErrors();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => Order::STATUS_RECIBIDO_TIENDA,
        ]);
    }

    public function test_shipment_closes_when_all_orders_closed(): void
    {
        $shipment = $this->makeEntregadoShipment();
        $order1 = $this->makeDeliveredOrder($this->store, $shipment);
        $order2 = $this->makeDeliveredOrder($this->store, $shipment);
        $this->actingAsTienda($this->store);

        $this->post(route('tienda.recepciones.confirmar', $order1));
        $this->post(route('tienda.recepciones.confirmar', $order2));

        $this->assertDatabaseHas('shipments', [
            'id' => $shipment->id,
            'status' => Shipment::STATUS_CERRADO,
        ]);
        $this->assertDatabaseHas('movements', [
            'trackable_type' => Shipment::class,
            'trackable_id' => $shipment->id,
            'state' => Shipment::STATUS_CERRADO,
        ]);
    }

    public function test_shipment_remains_open_until_last_order_closed(): void
    {
        $shipment = $this->makeEntregadoShipment();
        $order1 = $this->makeDeliveredOrder($this->store, $shipment);
        $order2 = $this->makeDeliveredOrder($this->store, $shipment);
        $this->actingAsTienda($this->store);

        $this->post(route('tienda.recepciones.confirmar', $order1));

        $this->assertDatabaseHas('shipments', [
            'id' => $shipment->id,
            'status' => Shipment::STATUS_ENTREGADO,
        ]);
    }

    public function test_access_control_cierre(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'ventas']));

        $this->get(route('tienda.recepciones'))->assertForbidden();
    }

    public function test_admin_can_confirm_an_order(): void
    {
        $order = $this->makeDeliveredOrder($this->store);
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->post(route('tienda.recepciones.confirmar', $order))
            ->assertRedirect(route('tienda.recepciones'));

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => Order::STATUS_CERRADO,
        ]);
    }
}