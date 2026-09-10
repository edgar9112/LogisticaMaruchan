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

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private Store $store;
    private Warehouse $warehouse;
    private Presentation $presentation;
    private User $creator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->store = Store::create(['code' => 'TDE-DASH', 'name' => 'Tienda Dashboard']);
        $this->warehouse = Warehouse::create(['code' => 'ALM-DASH', 'name' => 'Almacén Central']);
        $this->presentation = Presentation::create([
            'name' => 'Sopa Maruchan',
            'presentation_type' => 'vaso',
            'flavor' => 'Pollo',
            'sku' => 'MAR-DASH-01',
        ]);
        $this->creator = User::factory()->create(['role' => 'ventas']);
    }

    private function makeOrder(string $status, ?array $extra = []): Order
    {
        $order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'store_id' => $this->store->id,
            'user_id' => $this->creator->id,
            'warehouse_id' => $this->warehouse->id,
            'status' => $status,
            'ordered_at' => $extra['ordered_at'] ?? now(),
            'received_at' => $extra['received_at'] ?? null,
            'prepared_at' => $extra['prepared_at'] ?? null,
            'shipped_at' => $extra['shipped_at'] ?? null,
            'completed_at' => $extra['completed_at'] ?? null,
        ]);

        $order->items()->create([
            'presentation_id' => $this->presentation->id,
            'quantity_requested' => $extra['requested'] ?? 10,
            'quantity_received' => $extra['received_qty'] ?? null,
            'quantity_prepared' => $extra['prepared'] ?? 0,
        ]);

        return $order;
    }

    public function test_admin_can_view_dashboard(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Dashboard operativo');
    }

    public function test_dashboard_counts_received_today_and_delivered(): void
    {
        $this->makeOrder(Order::STATUS_EN_ALMACEN, ['received_at' => now(), 'received_qty' => 10]);
        $this->makeOrder(Order::STATUS_CERRADO, ['received_at' => now()->subDay(), 'shipped_at' => now()->subDay()]);

        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $response = $this->get(route('dashboard'));

        $response->assertSee('Mercancía recibida hoy');
        $response->assertSee('Entregas realizadas');
    }

    public function test_dashboard_calculates_warehouse_stock(): void
    {
        // 10 recibidas - 4 preparadas = 6 en almacén
        $this->makeOrder(Order::STATUS_PREPARADO, [
            'received_at' => now(),
            'received_qty' => 10,
            'prepared' => 4,
        ]);
        // pedido cerrado: no cuenta
        $this->makeOrder(Order::STATUS_CERRADO, [
            'received_qty' => 50,
            'requested' => 50,
        ]);

        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->get(route('dashboard'))->assertSee('6');
    }

    public function test_dashboard_shows_active_shipments(): void
    {
        $this->makeOrder(Order::STATUS_EN_TRANSITO);

        $shipment = Shipment::create([
            'shipment_number' => 'EMB-DASH-01',
            'origin_warehouse_id' => $this->warehouse->id,
            'destination_store_id' => $this->store->id,
            'status' => Shipment::STATUS_EN_TRANSITO,
        ]);
        $shipment->orders()->attach(Order::first()->id);

        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->get(route('dashboard'))->assertSee('Traslados activos');
    }

    public function test_dashboard_lists_orders_by_store(): void
    {
        $otherStore = Store::create(['code' => 'TDE-2', 'name' => 'Tienda Dos']);
        $this->makeOrder(Order::STATUS_CREADO);
        $this->makeOrder(Order::STATUS_CREADO);
        $this->makeOrder(Order::STATUS_CREADO);

        $order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'store_id' => $otherStore->id,
            'user_id' => $this->creator->id,
            'warehouse_id' => $this->warehouse->id,
            'status' => Order::STATUS_CREADO,
            'ordered_at' => now(),
        ]);
        $order->items()->create([
            'presentation_id' => $this->presentation->id,
            'quantity_requested' => 1,
        ]);

        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Pedidos por tienda')
            ->assertSee('Tienda Dashboard')
            ->assertSee('Tienda Dos');
    }

    public function test_dashboard_includes_chart_data_for_orders_by_status(): void
    {
        $this->makeOrder(Order::STATUS_CREADO);
        $this->makeOrder(Order::STATUS_CARGADO);
        $this->makeOrder(Order::STATUS_CERRADO);

        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Pedidos por estado')
            ->assertSee('CERRADO')
            ->assertSee('window.dashboardData', false);
    }

    public function test_dashboard_includes_chart_data_for_shipments_by_status(): void
    {
        Shipment::create([
            'shipment_number' => 'EMB-DASH-2',
            'origin_warehouse_id' => $this->warehouse->id,
            'destination_store_id' => $this->store->id,
            'status' => Shipment::STATUS_EN_TRANSITO,
        ]);

        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Embarques por estado');
    }

    public function test_dashboard_includes_chart_data_for_incidents_by_type(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Incidencias por tipo');
    }

    public function test_dashboard_includes_orders_last_14_days_series(): void
    {
        $this->makeOrder(Order::STATUS_CREADO, ['ordered_at' => today()]);
        $this->makeOrder(Order::STATUS_CREADO, ['ordered_at' => today()]);

        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Pedidos por día')
            ->assertSee(str_replace('/', '\/', today()->format('d/m')));
    }

    public function test_only_admin_can_view_dashboard(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'almacen']));

        $this->get(route('dashboard'))->assertForbidden();
    }

    public function test_dashboard_shows_average_time_by_stage(): void
    {
        // Ciclo total: pedido hace 10 días, cerrado hoy → 10 días.
        $this->makeOrder(Order::STATUS_CERRADO, [
            'ordered_at' => now()->subDays(10),
            'received_at' => now()->subDays(8),
            'prepared_at' => now()->subDays(7),
            'shipped_at' => now()->subDays(5),
            'completed_at' => now(),
            'received_qty' => 10,
            'prepared' => 10,
        ]);

        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Tiempos por etapa')
            ->assertSee('Ciclo total (pedido → cerrado)')
            ->assertSee('10.00');
    }

    public function test_dashboard_shows_stock_rotation(): void
    {
        // Stock: 10 recibidas - 4 preparadas = 6. Salidas 30 días: 4 → rotación 0.67.
        $this->makeOrder(Order::STATUS_PREPARADO, [
            'received_at' => now()->subDays(2),
            'prepared_at' => now()->subDay(),
            'received_qty' => 10,
            'prepared' => 4,
        ]);

        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Rotación de inventario')
            ->assertSee('Salidas 30 días')
            ->assertSee('0.67');
    }
}