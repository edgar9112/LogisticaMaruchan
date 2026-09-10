<?php

namespace Tests\Feature;

use App\Mail\IncidentAlert;
use App\Mail\OrderDeliveredAlert;
use App\Mail\OrderReceivedAlert;
use App\Mail\ShipmentDepartedAlert;
use App\Models\Incident;
use App\Models\Order;
use App\Models\Presentation;
use App\Models\Shipment;
use App\Models\Store;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AlertsTest extends TestCase
{
    use RefreshDatabase;

    private Store $store;
    private Warehouse $warehouse;
    private Vehicle $vehicle;

    protected function setUp(): void
    {
        parent::setUp();

        $this->store = Store::create(['code' => 'TDE-ALR', 'name' => 'Tienda Alertas']);
        $this->warehouse = Warehouse::create(['code' => 'ALM-ALR', 'name' => 'Almacén Central']);
        $this->vehicle = Vehicle::create([
            'code' => 'VEH-ALR',
            'plate' => 'ALR-001',
            'driver_name' => 'Luisa Gómez',
            'capacity' => 1000,
            'active' => true,
        ]);
    }

    private function makeOrder(string $status): Order
    {
        $presentation = Presentation::create([
            'name' => 'Sopa Maruchan',
            'presentation_type' => 'vaso',
            'flavor' => 'Pollo',
            'sku' => 'MAR-ALR-'.rand(1000, 9999),
        ]);
        $creator = User::factory()->create(['role' => 'ventas']);

        $order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'store_id' => $this->store->id,
            'user_id' => $creator->id,
            'warehouse_id' => $this->warehouse->id,
            'status' => $status,
            'ordered_at' => now()->subDay(),
        ]);

$order->items()->create([
            'presentation_id' => $presentation->id,
            'quantity_requested' => 10,
            'quantity_received' => 10,
            'quantity_prepared' => 10,
        ]);

        return $order;
    }

    private function makeShipment(string $status, Order $order): Shipment
    {
        $shipment = Shipment::create([
            'shipment_number' => 'EMB-ALR-'.rand(1000, 9999),
            'origin_warehouse_id' => $this->warehouse->id,
            'destination_store_id' => $this->store->id,
            'vehicle_id' => $this->vehicle->id,
            'status' => $status,
        ]);
        $shipment->orders()->attach($order->id);

        return $shipment;
    }

    public function test_entrada_almacen_envia_alerta_al_admin(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $order = $this->makeOrder(Order::STATUS_CONFIRMADO);

        $this->actingAs(User::factory()->create(['role' => 'almacen']))
            ->post(route('almacen.pedidos.entrada', $order), [
                'quantities' => [$order->items->first()->id => 10],
            ])
            ->assertRedirect();

        Mail::assertSent(OrderReceivedAlert::class, fn (OrderReceivedAlert $mail) => $mail->hasTo($admin->email));
    }

    public function test_salida_a_ruta_envia_alerta_al_admin(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $order = $this->makeOrder(Order::STATUS_CARGADO);
        $shipment = $this->makeShipment(Shipment::STATUS_CARGADO, $order);

        $this->actingAs(User::factory()->create(['role' => 'transporte']))
            ->post(route('viajes.salir', $shipment))
            ->assertRedirect();

        Mail::assertSent(ShipmentDepartedAlert::class, fn (ShipmentDepartedAlert $mail) => $mail->hasTo($admin->email));
    }

    public function test_entrega_en_tienda_envia_alerta_al_admin(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $order = $this->makeOrder(Order::STATUS_EN_TRANSITO);
        $shipment = $this->makeShipment(Shipment::STATUS_EN_TRANSITO, $order);

        $this->actingAs(User::factory()->create(['role' => 'transporte']))
            ->post(route('viajes.llegar', $shipment))
            ->assertRedirect();

        Mail::assertSent(OrderDeliveredAlert::class, fn (OrderDeliveredAlert $mail) => $mail->hasTo($admin->email));
    }

    public function test_incidencia_en_ruta_envia_alerta_al_admin(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $order = $this->makeOrder(Order::STATUS_EN_TRANSITO);
        $shipment = $this->makeShipment(Shipment::STATUS_EN_TRANSITO, $order);

        $this->actingAs(User::factory()->create(['role' => 'transporte']))
            ->post(route('viajes.incidencia', $shipment), [
                'type' => Incident::TYPE_PERCANCE,
                'description' => 'Derrumbe de la carretera',
                'occurred_at' => now(),
            ])
            ->assertRedirect();

        Mail::assertSent(IncidentAlert::class, fn (IncidentAlert $mail) => $mail->hasTo($admin->email));
    }

    public function test_alertas_se_envian_solo_a_administradores(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $almacen = User::factory()->create(['role' => 'almacen']);
        $order = $this->makeOrder(Order::STATUS_CONFIRMADO);

        $this->actingAs($almacen)
            ->post(route('almacen.pedidos.entrada', $order), [
                'quantities' => [$order->items->first()->id => 10],
            ])
            ->assertRedirect();

        Mail::assertSent(OrderReceivedAlert::class, function (OrderReceivedAlert $mail) use ($admin, $almacen) {
            return $mail->hasTo($admin->email) && ! $mail->hasTo($almacen->email);
        });
    }

    public function test_sin_administradores_no_se_envia_ningun_correo(): void
    {
        Mail::fake();
        $order = $this->makeOrder(Order::STATUS_CONFIRMADO);

        $this->actingAs(User::factory()->create(['role' => 'almacen']))
            ->post(route('almacen.pedidos.entrada', $order), [
                'quantities' => [$order->items->first()->id => 10],
            ])
            ->assertRedirect();

        Mail::assertNothingSent();
    }
}
