<?php

namespace Tests\Feature;

use App\Models\Incident;
use App\Models\Order;
use App\Models\Presentation;
use App\Models\Shipment;
use App\Models\Store;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncidenciasTest extends TestCase
{
    use RefreshDatabase;

    private Store $store;
    private Warehouse $warehouse;
    private Vehicle $vehicle;

    protected function setUp(): void
    {
        parent::setUp();

        $this->store = Store::create(['code' => 'TDE-INC', 'name' => 'Tienda Incidencias']);
        $this->warehouse = Warehouse::create(['code' => 'ALM-INC', 'name' => 'Almacén Central']);
        $this->vehicle = Vehicle::create([
            'code' => 'VEH-INC',
            'plate' => 'INC-001',
            'driver_name' => 'Carlos Pérez',
            'capacity' => 1000,
            'active' => true,
        ]);
    }

    private function makeShipment(string $status): Shipment
    {
        $presentation = Presentation::create([
            'name' => 'Sopa Maruchan',
            'presentation_type' => 'vaso',
            'flavor' => 'Pollo',
            'sku' => 'MAR-INC-'.rand(1000, 9999),
        ]);
        $creator = User::factory()->create(['role' => 'ventas']);

        $order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'store_id' => $this->store->id,
            'user_id' => $creator->id,
            'warehouse_id' => $this->warehouse->id,
            'status' => $status === Shipment::STATUS_EN_TRANSITO ? Order::STATUS_EN_TRANSITO : Order::STATUS_CARGADO,
            'ordered_at' => now()->subDay(),
        ]);
        $order->items()->create([
            'presentation_id' => $presentation->id,
            'quantity_requested' => 10,
            'quantity_received' => 10,
            'quantity_prepared' => 10,
        ]);

        $shipment = Shipment::create([
            'shipment_number' => 'EMB-INC-'.rand(1000, 9999),
            'origin_warehouse_id' => $this->warehouse->id,
            'destination_store_id' => $this->store->id,
            'vehicle_id' => $this->vehicle->id,
            'status' => $status,
        ]);
        $shipment->orders()->attach($order->id);

        return $shipment;
    }

    private function registerIncident(Shipment $shipment, string $type = Incident::TYPE_PERCANCE, string $description = 'Poncha llanta trasera')
    {
        return $this->post(route('viajes.incidencia', $shipment), [
            'type' => $type,
            'description' => $description,
            'occurred_at' => now(),
        ]);
    }

    public function test_transporte_can_register_route_incident(): void
    {
        $shipment = $this->makeShipment(Shipment::STATUS_EN_TRANSITO);

        $user = User::factory()->create(['role' => 'transporte']);
        $this->actingAs($user);

        $this->registerIncident($shipment)
            ->assertRedirect(route('viajes.show', $shipment));

        $this->assertDatabaseHas('incidents', [
            'shipment_id' => $shipment->id,
            'user_id' => $user->id,
            'type' => Incident::TYPE_PERCANCE,
        ]);
        $this->assertDatabaseHas('movements', [
            'trackable_type' => Shipment::class,
            'trackable_id' => $shipment->id,
            'state' => Shipment::STATUS_EN_TRANSITO,
        ]);
    }

    public function test_incident_appears_on_shipment_show(): void
    {
        $shipment = $this->makeShipment(Shipment::STATUS_EN_TRANSITO);

        $this->actingAs(User::factory()->create(['role' => 'transporte']));

        $this->registerIncident($shipment, Incident::TYPE_PARADA, 'Carga de combustible');

        $this->get(route('viajes.show', $shipment))
            ->assertOk()
            ->assertSee('Parada controlada')
            ->assertSee('Carga de combustible');
    }

    public function test_incident_requires_valid_type(): void
    {
        $shipment = $this->makeShipment(Shipment::STATUS_EN_TRANSITO);

        $this->actingAs(User::factory()->create(['role' => 'transporte']));

        $this->from(route('viajes.show', $shipment))
            ->post(route('viajes.incidencia', $shipment), [
                'type' => 'tipo_invalido',
                'description' => 'Razón',
            ])
            ->assertSessionHasErrors('type');

        $this->assertDatabaseCount('incidents', 0);
    }

    public function test_incident_requires_description(): void
    {
        $shipment = $this->makeShipment(Shipment::STATUS_EN_TRANSITO);

        $this->actingAs(User::factory()->create(['role' => 'transporte']));

        $this->from(route('viajes.show', $shipment))
            ->post(route('viajes.incidencia', $shipment), [
                'type' => Incident::TYPE_NOTA,
                'description' => '',
            ])
            ->assertSessionHasErrors('description');

        $this->assertDatabaseCount('incidents', 0);
    }

    public function test_cannot_register_incident_when_not_in_transito(): void
    {
        $shipment = $this->makeShipment(Shipment::STATUS_CARGADO);

        $this->actingAs(User::factory()->create(['role' => 'transporte']));

        $this->from(route('viajes.show', $shipment))
            ->post(route('viajes.incidencia', $shipment), [
                'type' => Incident::TYPE_NOTA,
                'description' => 'No debería registrarse',
            ])
            ->assertSessionHasErrors();

        $this->assertDatabaseCount('incidents', 0);
    }

    public function test_admin_can_register_incident(): void
    {
        $shipment = $this->makeShipment(Shipment::STATUS_EN_TRANSITO);

        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->registerIncident($shipment, Incident::TYPE_RETRASO, 'Bloqueo por derrumbe')
            ->assertRedirect();

        $this->assertDatabaseHas('incidents', [
            'shipment_id' => $shipment->id,
            'type' => Incident::TYPE_RETRASO,
        ]);
    }

    public function test_access_control_incidencias(): void
    {
        $shipment = $this->makeShipment(Shipment::STATUS_EN_TRANSITO);

        $this->actingAs(User::factory()->create(['role' => 'almacen']));

        $this->post(route('viajes.incidencia', $shipment), [
            'type' => Incident::TYPE_NOTA,
            'description' => 'No autorizado',
        ])->assertForbidden();
    }
}
