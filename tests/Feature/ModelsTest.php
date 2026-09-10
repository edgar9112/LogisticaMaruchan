<?php

namespace Tests\Feature;

use App\Models\Incident;
use App\Models\Movement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Package;
use App\Models\Presentation;
use App\Models\Shipment;
use App\Models\Store;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelsTest extends TestCase
{
    use RefreshDatabase;

    private static int $storeCounter = 0;

    private static int $warehouseCounter = 0;

    private function makeWarehouse(): Warehouse
    {
        self::$warehouseCounter++;

        return Warehouse::create([
            'code' => 'ALM-'.str_pad((string) self::$warehouseCounter, 3, '0', STR_PAD_LEFT),
            'name' => 'Almacén Central',
        ]);
    }

    private function makeStore(): Store
    {
        self::$storeCounter++;

        return Store::create([
            'code' => 'TIENDA-'.str_pad((string) self::$storeCounter, 3, '0', STR_PAD_LEFT),
            'name' => 'Tienda Centro',
        ]);
    }

    private function makePresentation(): Presentation
    {
        return Presentation::create([
            'name' => 'Sopa Maruchan',
            'presentation_type' => 'vaso',
            'flavor' => 'Pollo',
            'pieces_per_box' => 24,
            'sku' => 'MAR-VAS-POL-24',
        ]);
    }

    private static int $orderCounter = 0;

    private function makeOrder(): Order
    {
        self::$orderCounter++;
        $store = $this->makeStore();
        $warehouse = $this->makeWarehouse();
        $user = User::factory()->create(['role' => 'ventas']);

        return Order::create([
            'order_number' => 'PED-'.str_pad((string) self::$orderCounter, 5, '0', STR_PAD_LEFT),
            'store_id' => $store->id,
            'user_id' => $user->id,
            'warehouse_id' => $warehouse->id,
            'status' => Order::STATUS_CREADO,
            'ordered_at' => now(),
        ]);
    }

    public function test_order_has_all_major_relationships(): void
    {
        $store = $this->makeStore();
        $warehouse = $this->makeWarehouse();
        $user = User::factory()->create(['role' => 'ventas']);
        $presentation = $this->makePresentation();

        $order = Order::create([
            'order_number' => 'PED-00002',
            'store_id' => $store->id,
            'user_id' => $user->id,
            'warehouse_id' => $warehouse->id,
            'status' => Order::STATUS_CREADO,
            'ordered_at' => now(),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'presentation_id' => $presentation->id,
            'quantity_requested' => 10,
        ]);

        $this->assertTrue($order->store->is($store));
        $this->assertTrue($order->creator->is($user));
        $this->assertTrue($order->warehouse->is($warehouse));
        $this->assertCount(1, $order->items);
        $this->assertEquals($presentation->id, $order->items->first()->presentation->id);
    }

    public function test_order_movements_are_polymorphic(): void
    {
        $order = $this->makeOrder();

        $order->movements()->create([
            'user_id' => $order->user_id,
            'state' => Order::STATUS_CREADO,
            'action' => 'creado',
        ]);

        $movement = Movement::first();
        $this->assertTrue($movement->trackable->is($order));
        $this->assertInstanceOf(Order::class, $movement->trackable);
        $this->assertCount(1, $order->movements);
    }

    public function test_package_movements_are_polymorphic(): void
    {
        $order = $this->makeOrder();

        $package = Package::create([
            'order_id' => $order->id,
            'package_number' => 'BULTO-001',
            'barcode' => 'BAR-001',
            'status' => 'CREADO',
        ]);

        $package->movements()->create([
            'user_id' => $order->user_id,
            'state' => 'PREPARADO',
            'action' => 'preparado',
        ]);

        $movement = Movement::first();
        $this->assertTrue($movement->trackable->is($package));
        $this->assertInstanceOf(Package::class, $movement->trackable);
    }

    public function test_shipment_belongs_to_many_orders(): void
    {
        $store = $this->makeStore();
        $warehouse = $this->makeWarehouse();
        $order1 = $this->makeOrder();
        $order2 = $this->makeOrder();

        $vehicle = Vehicle::create([
            'code' => 'VEH-01',
            'plate' => 'ABC-123',
            'driver_name' => 'Juan Pérez',
        ]);

        $shipment = Shipment::create([
            'shipment_number' => 'EMB-00001',
            'origin_warehouse_id' => $warehouse->id,
            'destination_store_id' => $store->id,
            'vehicle_id' => $vehicle->id,
            'driver_name' => 'Juan Pérez',
            'status' => Shipment::STATUS_PREPARADO,
        ]);

        $shipment->orders()->attach([$order1->id, $order2->id]);

        $this->assertCount(2, $shipment->orders);
        $this->assertEquals(1, $order1->shipments()->count());
        $this->assertTrue($shipment->originWarehouse->is($warehouse));
        $this->assertTrue($shipment->destinationStore->is($store));
        $this->assertTrue($shipment->vehicle->is($vehicle));
    }

    public function test_incident_relationships(): void
    {
        $order = $this->makeOrder();
        $user = User::factory()->create(['role' => 'almacen']);

        $incident = Incident::create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'type' => Incident::TYPE_FALTANTE,
            'description' => 'Faltan 2 piezas',
            'occurred_at' => now(),
        ]);

        $this->assertTrue($incident->order->is($order));
        $this->assertTrue($incident->user->is($user));
        $this->assertCount(1, $order->incidents);
    }

    public function test_user_relationships_and_roles(): void
    {
        $user = User::factory()->create([
            'role' => 'almacen',
            'store_id' => $this->makeStore()->id,
        ]);

        $this->assertFalse($user->isAdmin());
        $this->assertTrue($user->hasRole('almacen'));
        $this->assertFalse($user->hasRole('admin')); // un no-admin no tiene rol admin

        $admin = User::factory()->create(['role' => 'admin']);
        $this->assertTrue($admin->isAdmin());
        $this->assertTrue($admin->hasRole('ventas')); // admin pasa cualquier rol
    }

    public function test_order_status_constants_match_document(): void
    {
        $this->assertEquals([
            'CREADO',
            'CONFIRMADO',
            'EN_ALMACEN',
            'RECIBIDO',
            'CLASIFICADO',
            'PREPARADO',
            'ASIGNADO_EMBARQUE',
            'CARGADO',
            'EN_TRANSITO',
            'RECIBIDO_TIENDA',
            'CERRADO',
        ], Order::STATUSES);
    }

    public function test_presentation_is_instantiable_with_sku(): void
    {
        $presentation = $this->makePresentation();
        $this->assertEquals('Sopa Maruchan', $presentation->name);
        $this->assertEquals(24, $presentation->pieces_per_box);
    }
}