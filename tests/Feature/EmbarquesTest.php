<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Presentation;
use App\Models\Shipment;
use App\Models\Store;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmbarquesTest extends TestCase
{
    use RefreshDatabase;

    private Store $store;
    private Store $otherStore;
    private Warehouse $warehouse;
    private Vehicle $vehicle;

    protected function setUp(): void
    {
        parent::setUp();

        $this->store = Store::create(['code' => 'TDE-EMB', 'name' => 'Tienda Embarque']);
        $this->otherStore = Store::create(['code' => 'TDE-EMB2', 'name' => 'Tienda Embarque 2']);
        $this->warehouse = Warehouse::create(['code' => 'ALM-EMB', 'name' => 'Almacén Central']);
        $this->vehicle = Vehicle::create([
            'code' => 'VEH-EMB',
            'plate' => 'XYZ-000',
            'driver_name' => 'Pedro Díaz',
            'capacity' => 1000,
            'active' => true,
        ]);
    }

    private function makePreparedOrder(Store $store): Order
    {
        $presentation = Presentation::create([
            'name' => 'Sopa Maruchan',
            'presentation_type' => 'vaso',
            'flavor' => 'Pollo',
            'sku' => 'MAR-EMB-'.rand(1000, 9999),
        ]);
        $creator = User::factory()->create(['role' => 'ventas']);

        $order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'store_id' => $store->id,
            'user_id' => $creator->id,
            'warehouse_id' => $this->warehouse->id,
            'status' => Order::STATUS_PREPARADO,
            'ordered_at' => now()->subDay(),
            'received_at' => now()->subDay(),
            'prepared_at' => now(),
        ]);
        $order->items()->create([
            'presentation_id' => $presentation->id,
            'quantity_requested' => 10,
            'quantity_received' => 10,
            'quantity_prepared' => 10,
        ]);

        return $order;
    }

    public function test_logistica_can_view_index(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'logistica']));

        $this->get(route('embarques.index'))->assertOk();
    }

    public function test_create_shipment_assigns_prepared_orders(): void
    {
        $order = $this->makePreparedOrder($this->store);

        $this->actingAs(User::factory()->create(['role' => 'logistica']));

        $response = $this->post(route('embarques.store'), [
            'origin_warehouse_id' => $this->warehouse->id,
            'destination_store_id' => $this->store->id,
            'vehicle_id' => $this->vehicle->id,
            'driver_name' => 'Pedro Díaz',
            'order_ids' => [$order->id],
        ]);

        $shipment = Shipment::first();

        $response->assertRedirect(route('embarques.show', $shipment));
        $this->assertTrue($shipment->shipment_number !== '');
        $this->assertDatabaseHas('shipment_items', [
            'shipment_id' => $shipment->id,
            'order_id' => $order->id,
        ]);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => Order::STATUS_ASIGNADO_EMBARQUE,
        ]);
        $this->assertDatabaseHas('movements', [
            'trackable_type' => Order::class,
            'trackable_id' => $order->id,
            'state' => Order::STATUS_ASIGNADO_EMBARQUE,
        ]);
        $this->assertDatabaseHas('movements', [
            'trackable_type' => Shipment::class,
            'trackable_id' => $shipment->id,
        ]);
    }

    public function test_shipment_requires_at_least_one_order(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'logistica']));

        $response = $this->from(route('embarques.create'))->post(route('embarques.store'), [
            'origin_warehouse_id' => $this->warehouse->id,
            'destination_store_id' => $this->store->id,
            'vehicle_id' => $this->vehicle->id,
            'order_ids' => [],
        ]);

        $response->assertSessionHasErrors('order_ids');
        $this->assertDatabaseCount('shipments', 0);
    }

    public function test_cannot_assign_an_order_not_prepared(): void
    {
        $presentation = Presentation::create([
            'name' => 'Sopa Maruchan',
            'presentation_type' => 'vaso',
            'flavor' => 'Pollo',
            'sku' => 'MAR-EMB-NP-01',
        ]);
        $creator = User::factory()->create(['role' => 'ventas']);
        $order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'store_id' => $this->store->id,
            'user_id' => $creator->id,
            'warehouse_id' => $this->warehouse->id,
            'status' => Order::STATUS_CLASIFICADO,
            'ordered_at' => now()->subDay(),
            'received_at' => now()->subDay(),
        ]);
        $order->items()->create([
            'presentation_id' => $presentation->id,
            'quantity_requested' => 10,
            'quantity_received' => 10,
        ]);

        $this->actingAs(User::factory()->create(['role' => 'logistica']));

        $this->from(route('embarques.create'))->post(route('embarques.store'), [
            'origin_warehouse_id' => $this->warehouse->id,
            'destination_store_id' => $this->store->id,
            'vehicle_id' => $this->vehicle->id,
            'order_ids' => [$order->id],
        ])->assertSessionHasErrors();

        $this->assertDatabaseCount('shipments', 0);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => Order::STATUS_CLASIFICADO,
        ]);
    }

    public function test_cannot_assign_an_order_from_another_store(): void
    {
        $order = $this->makePreparedOrder($this->otherStore);

        $this->actingAs(User::factory()->create(['role' => 'logistica']));

        $this->from(route('embarques.create'))->post(route('embarques.store'), [
            'origin_warehouse_id' => $this->warehouse->id,
            'destination_store_id' => $this->store->id,
            'vehicle_id' => $this->vehicle->id,
            'order_ids' => [$order->id],
        ])->assertSessionHasErrors();

        $this->assertDatabaseCount('shipments', 0);
    }

    public function test_load_shipment_marks_orders_as_cargado(): void
    {
        $order = $this->makePreparedOrder($this->store);
        $shipment = Shipment::create([
            'shipment_number' => 'EMB-TEST-01',
            'origin_warehouse_id' => $this->warehouse->id,
            'destination_store_id' => $this->store->id,
            'vehicle_id' => $this->vehicle->id,
            'status' => Shipment::STATUS_PREPARADO,
        ]);
        $shipment->orders()->attach($order->id);
        $order->update(['status' => Order::STATUS_ASIGNADO_EMBARQUE]);

        $this->actingAs(User::factory()->create(['role' => 'logistica']));

        $this->post(route('embarques.cargar', $shipment))
            ->assertRedirect(route('embarques.show', $shipment));

        $this->assertDatabaseHas('shipments', [
            'id' => $shipment->id,
            'status' => Shipment::STATUS_CARGADO,
        ]);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => Order::STATUS_CARGADO,
        ]);
        $this->assertDatabaseHas('movements', [
            'trackable_type' => Shipment::class,
            'trackable_id' => $shipment->id,
            'state' => Shipment::STATUS_CARGADO,
        ]);
    }

    public function test_cannot_load_a_shipment_not_preparado(): void
    {
        $shipment = Shipment::create([
            'shipment_number' => 'EMB-TEST-02',
            'origin_warehouse_id' => $this->warehouse->id,
            'destination_store_id' => $this->store->id,
            'vehicle_id' => $this->vehicle->id,
            'status' => Shipment::STATUS_CARGADO,
        ]);

        $this->actingAs(User::factory()->create(['role' => 'logistica']));

        $this->from(route('embarques.show', $shipment))->post(route('embarques.cargar', $shipment))
            ->assertSessionHasErrors();
    }

    public function test_show_shipment_displays_orders(): void
    {
        $order = $this->makePreparedOrder($this->store);
        $shipment = Shipment::create([
            'shipment_number' => 'EMB-TEST-03',
            'origin_warehouse_id' => $this->warehouse->id,
            'destination_store_id' => $this->store->id,
            'vehicle_id' => $this->vehicle->id,
            'status' => Shipment::STATUS_PREPARADO,
        ]);
        $shipment->orders()->attach($order->id);

        $this->actingAs(User::factory()->create(['role' => 'logistica']));

        $this->get(route('embarques.show', $shipment))
            ->assertOk()
            ->assertSee($shipment->shipment_number)
            ->assertSee($order->order_number);
    }

    public function test_access_control_embarques(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'ventas']));

        $this->get(route('embarques.index'))->assertForbidden();
        $this->get(route('embarques.create'))->assertForbidden();
    }

    public function test_admin_can_create_shipment(): void
    {
        $order = $this->makePreparedOrder($this->store);

        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->post(route('embarques.store'), [
            'origin_warehouse_id' => $this->warehouse->id,
            'destination_store_id' => $this->store->id,
            'vehicle_id' => $this->vehicle->id,
            'order_ids' => [$order->id],
        ])->assertRedirect();

        $this->assertDatabaseCount('shipments', 1);
    }
}