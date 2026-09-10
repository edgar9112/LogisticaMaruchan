<?php

namespace Tests\Feature;

use App\Models\Incident;
use App\Models\Movement;
use App\Models\Order;
use App\Models\Presentation;
use App\Models\Shipment;
use App\Models\Store;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportesTest extends TestCase
{
    use RefreshDatabase;

    private Store $store;
    private Warehouse $warehouse;
    private Vehicle $vehicle;
    private Presentation $presentation;
    private User $creator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->store = Store::create(['code' => 'TDE-REP', 'name' => 'Tienda Reportes']);
        $this->warehouse = Warehouse::create(['code' => 'ALM-REP', 'name' => 'Almacén Central']);
        $this->vehicle = Vehicle::create([
            'code' => 'VEH-REP',
            'plate' => 'REP-001',
            'driver_name' => 'Roberto Díaz',
            'capacity' => 1000,
            'active' => true,
        ]);
        $this->presentation = Presentation::create([
            'name' => 'Sopa Maruchan',
            'presentation_type' => 'vaso',
            'flavor' => 'Pollo',
            'sku' => 'MAR-REP-01',
        ]);
        $this->creator = User::factory()->create(['role' => 'ventas']);
    }

    private function makeOrder(string $status, ?string $orderedAt = null): Order
    {
        $order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'store_id' => $this->store->id,
            'user_id' => $this->creator->id,
            'warehouse_id' => $this->warehouse->id,
            'status' => $status,
            'ordered_at' => $orderedAt ?? now()->format('Y-m-d H:i:s'),
        ]);
        $order->items()->create([
            'presentation_id' => $this->presentation->id,
            'quantity_requested' => 10,
            'quantity_received' => 10,
            'quantity_prepared' => 10,
        ]);

        return $order;
    }

    private function makeShipment(string $status): Shipment
    {
        $order = $this->makeOrder(Order::STATUS_EN_TRANSITO);

        $shipment = Shipment::create([
            'shipment_number' => 'EMB-REP-'.rand(1000, 9999),
            'origin_warehouse_id' => $this->warehouse->id,
            'destination_store_id' => $this->store->id,
            'vehicle_id' => $this->vehicle->id,
            'status' => $status,
        ]);
        $shipment->orders()->attach($order->id);

        return $shipment;
    }

    private function admin()
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_open_reportes_index(): void
    {
        $this->actingAs($this->admin());

        $this->get(route('reportes.index'))
            ->assertOk()
            ->assertSee('Reportes')
            ->assertSee('Pedidos')
            ->assertSee('Embarques')
            ->assertSee('Incidencias')
            ->assertSee('Movimientos');
    }

    public function test_reportes_pedidos_shows_orders(): void
    {
        $order = $this->makeOrder(Order::STATUS_CREADO);

        $this->actingAs($this->admin());

        $this->get(route('reportes.show', 'pedidos'))
            ->assertOk()
            ->assertSee('Reporte de pedidos')
            ->assertSee($order->order_number)
            ->assertSee('Tienda Reportes');
    }

    public function test_reportes_embarques_shows_shipments(): void
    {
        $shipment = $this->makeShipment(Shipment::STATUS_EN_TRANSITO);

        $this->actingAs($this->admin());

        $this->get(route('reportes.show', 'embarques'))
            ->assertOk()
            ->assertSee('Reporte de embarques')
            ->assertSee($shipment->shipment_number)
            ->assertSee('REP-001');
    }

    public function test_reportes_incidencias_shows_incidents(): void
    {
        $shipment = $this->makeShipment(Shipment::STATUS_EN_TRANSITO);

        Incident::create([
            'shipment_id' => $shipment->id,
            'user_id' => User::factory()->create(['role' => 'transporte'])->id,
            'type' => Incident::TYPE_PERCANCE,
            'description' => 'Poncha llanta trasera',
            'occurred_at' => now(),
        ]);

        $this->actingAs($this->admin());

        $this->get(route('reportes.show', 'incidencias'))
            ->assertOk()
            ->assertSee('Reporte de incidencias')
            ->assertSee('Percance')
            ->assertSee('Poncha llanta trasera');
    }

    public function test_reportes_movimientos_shows_movements(): void
    {
        $order = $this->makeOrder(Order::STATUS_CREADO);
        $order->recordMovement(Order::STATUS_CREADO, 'Pedido creado', 'Pedido capturado por Ventas', [], $this->creator);

        $this->actingAs($this->admin());

        $this->get(route('reportes.show', 'movimientos'))
            ->assertOk()
            ->assertSee('Reporte de movimientos')
            ->assertSee('Pedido creado')
            ->assertSee($order->order_number);
    }

    public function test_reportes_pedidos_filters_by_status(): void
    {
        $creado = $this->makeOrder(Order::STATUS_CREADO);
        $cerrado = $this->makeOrder(Order::STATUS_CERRADO);

        $this->actingAs($this->admin());

        $this->get(route('reportes.show', ['reporte' => 'pedidos', 'estado' => Order::STATUS_CERRADO]))
            ->assertOk()
            ->assertSee($cerrado->order_number)
            ->assertDontSee($creado->order_number);
    }

    public function test_reportes_pedidos_filters_by_date_range(): void
    {
        $hoy = $this->makeOrder(Order::STATUS_CREADO, now()->format('Y-m-d H:i:s'));
        $viejo = $this->makeOrder(Order::STATUS_CREADO, now()->subDays(10)->format('Y-m-d H:i:s'));

        $this->actingAs($this->admin());

        $this->get(route('reportes.show', ['reporte' => 'pedidos', 'desde' => today()->format('Y-m-d')]))
            ->assertOk()
            ->assertSee($hoy->order_number)
            ->assertDontSee($viejo->order_number);
    }

    public function test_reportes_incidencias_filters_by_type(): void
    {
        $shipment = $this->makeShipment(Shipment::STATUS_EN_TRANSITO);
        $user = User::factory()->create(['role' => 'transporte']);

        $percance = Incident::create([
            'shipment_id' => $shipment->id,
            'user_id' => $user->id,
            'type' => Incident::TYPE_PERCANCE,
            'description' => 'Llanta ponchada',
            'occurred_at' => now(),
        ]);
        Incident::create([
            'shipment_id' => $shipment->id,
            'user_id' => $user->id,
            'type' => Incident::TYPE_PARADA,
            'description' => 'Carga de combustible',
            'occurred_at' => now(),
        ]);

        $this->actingAs($this->admin());

        $this->get(route('reportes.show', ['reporte' => 'incidencias', 'tipo' => Incident::TYPE_PERCANCE]))
            ->assertOk()
            ->assertSee($percance->created_at->format('d/m/Y'))
            ->assertSee('Llanta ponchada')
            ->assertDontSee('Carga de combustible');
    }

    public function test_reportes_pdf_download(): void
    {
        $this->makeOrder(Order::STATUS_CREADO);

        $this->actingAs($this->admin());

        $this->get(route('reportes.pdf', 'pedidos'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_reportes_excel_download(): void
    {
        $this->makeOrder(Order::STATUS_CREADO);

        $this->actingAs($this->admin());

        $this->get(route('reportes.excel', 'pedidos'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_reportes_no_admin_forbidden(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'almacen']));

        $this->get(route('reportes.index'))->assertForbidden();
        $this->get(route('reportes.show', 'pedidos'))->assertForbidden();
        $this->get(route('reportes.pdf', 'pedidos'))->assertForbidden();
        $this->get(route('reportes.excel', 'pedidos'))->assertForbidden();
    }

    public function test_reportes_invalid_type_returns_404(): void
    {
        $this->actingAs($this->admin());

        $this->get(route('reportes.show', 'invalido'))->assertNotFound();
    }
}