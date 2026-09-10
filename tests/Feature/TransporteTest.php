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

class TransporteTest extends TestCase
{
    use RefreshDatabase;

    private Store $store;
    private Warehouse $warehouse;
    private Vehicle $vehicle;

    protected function setUp(): void
    {
        parent::setUp();

        $this->store = Store::create(['code' => 'TDE-TRA', 'name' => 'Tienda Transporte']);
        $this->warehouse = Warehouse::create(['code' => 'ALM-TRA', 'name' => 'Almacén Central']);
        $this->vehicle = Vehicle::create([
            'code' => 'VEH-TRA',
            'plate' => 'ABC-999',
            'driver_name' => 'Luis Ramírez',
            'capacity' => 1000,
            'active' => true,
        ]);
    }

    private function makeShipment(string $status): array
    {
        $presentation = Presentation::create([
            'name' => 'Sopa Maruchan',
            'presentation_type' => 'vaso',
            'flavor' => 'Pollo',
            'sku' => 'MAR-TRA-'.rand(1000, 9999),
        ]);
        $creator = User::factory()->create(['role' => 'ventas']);

        $order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'store_id' => $this->store->id,
            'user_id' => $creator->id,
            'warehouse_id' => $this->warehouse->id,
            'status' => Order::STATUS_CARGADO,
            'ordered_at' => now()->subDay(),
            'received_at' => now()->subDay(),
            'prepared_at' => now()->subDay(),
        ]);
        $order->items()->create([
            'presentation_id' => $presentation->id,
            'quantity_requested' => 10,
            'quantity_received' => 10,
            'quantity_prepared' => 10,
        ]);

        $shipment = Shipment::create([
            'shipment_number' => 'EMB-TRA-'.rand(1000, 9999),
            'origin_warehouse_id' => $this->warehouse->id,
            'destination_store_id' => $this->store->id,
            'vehicle_id' => $this->vehicle->id,
            'status' => $status,
        ]);
        $shipment->orders()->attach($order->id);

        return [$shipment, $order];
    }

    public function test_transporte_sees_active_shipments(): void
    {
        [$shipment] = $this->makeShipment(Shipment::STATUS_CARGADO);

        $this->actingAs(User::factory()->create(['role' => 'transporte']));

        $this->get(route('viajes.index'))
            ->assertOk()
            ->assertSee($shipment->shipment_number);
    }

    public function test_departure_marks_order_and_shipment_en_transito(): void
    {
        [$shipment, $order] = $this->makeShipment(Shipment::STATUS_CARGADO);

        $this->actingAs(User::factory()->create(['role' => 'transporte']));

        $this->post(route('viajes.salir', $shipment))
            ->assertRedirect(route('viajes.show', $shipment));

        $this->assertDatabaseHas('shipments', [
            'id' => $shipment->id,
            'status' => Shipment::STATUS_EN_TRANSITO,
        ]);
        $this->assertNotNull($shipment->fresh()->departed_at);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => Order::STATUS_EN_TRANSITO,
        ]);
        $this->assertNotNull($order->fresh()->shipped_at);
        $this->assertDatabaseHas('movements', [
            'trackable_type' => Shipment::class,
            'trackable_id' => $shipment->id,
            'state' => Shipment::STATUS_EN_TRANSITO,
        ]);
        $this->assertDatabaseHas('movements', [
            'trackable_type' => Order::class,
            'trackable_id' => $order->id,
            'state' => Order::STATUS_EN_TRANSITO,
        ]);
    }

    public function test_cannot_depart_a_shipment_not_cargado(): void
    {
        [$shipment] = $this->makeShipment(Shipment::STATUS_PREPARADO);

        $this->actingAs(User::factory()->create(['role' => 'transporte']));

        $this->from(route('viajes.show', $shipment))
            ->post(route('viajes.salir', $shipment))
            ->assertSessionHasErrors();

        $this->assertDatabaseHas('shipments', [
            'id' => $shipment->id,
            'status' => Shipment::STATUS_PREPARADO,
        ]);
    }

    public function test_arrival_marks_orders_received_in_store(): void
    {
        [$shipment, $order] = $this->makeShipment(Shipment::STATUS_EN_TRANSITO);
        $shipment->update(['departed_at' => now()->subHours(2)]);
        $order->update(['status' => Order::STATUS_EN_TRANSITO]);

        $this->actingAs(User::factory()->create(['role' => 'transporte']));

        $this->post(route('viajes.llegar', $shipment))
            ->assertRedirect(route('viajes.show', $shipment));

        $this->assertDatabaseHas('shipments', [
            'id' => $shipment->id,
            'status' => Shipment::STATUS_ENTREGADO,
        ]);
        $this->assertNotNull($shipment->fresh()->arrived_at);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => Order::STATUS_RECIBIDO_TIENDA,
        ]);
        $this->assertDatabaseHas('movements', [
            'trackable_type' => Shipment::class,
            'trackable_id' => $shipment->id,
            'state' => Shipment::STATUS_ENTREGADO,
        ]);
    }

    public function test_cannot_arrive_a_shipment_not_in_transito(): void
    {
        [$shipment] = $this->makeShipment(Shipment::STATUS_CARGADO);

        $this->actingAs(User::factory()->create(['role' => 'transporte']));

        $this->from(route('viajes.show', $shipment))
            ->post(route('viajes.llegar', $shipment))
            ->assertSessionHasErrors();

        $this->assertDatabaseHas('shipments', [
            'id' => $shipment->id,
            'status' => Shipment::STATUS_CARGADO,
        ]);
    }

    public function test_transporte_can_view_shipment_detail(): void
    {
        [$shipment, $order] = $this->makeShipment(Shipment::STATUS_EN_TRANSITO);

        $this->actingAs(User::factory()->create(['role' => 'transporte']));

        $this->get(route('viajes.show', $shipment))
            ->assertOk()
            ->assertSee($shipment->shipment_number)
            ->assertSee($order->order_number);
    }

    public function test_access_control_viajes(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'ventas']));

        $this->get(route('viajes.index'))->assertForbidden();
    }

    public function test_admin_can_depart_shipment(): void
    {
        [$shipment] = $this->makeShipment(Shipment::STATUS_CARGADO);

        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->post(route('viajes.salir', $shipment))->assertRedirect();

        $this->assertDatabaseHas('shipments', [
            'id' => $shipment->id,
            'status' => Shipment::STATUS_EN_TRANSITO,
        ]);
    }
}