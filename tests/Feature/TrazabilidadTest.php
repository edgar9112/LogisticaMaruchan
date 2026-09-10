<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Presentation;
use App\Models\Store;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrazabilidadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $store = Store::create(['code' => 'TDE-TRA', 'name' => 'Tienda Trazabilidad']);
        $warehouse = Warehouse::create(['code' => 'ALM-TRA', 'name' => 'Almacén Central']);
        $presentation = Presentation::create([
            'name' => 'Sopa Maruchan',
            'presentation_type' => 'vaso',
            'flavor' => 'Pollo',
            'sku' => 'MAR-TRA-01',
        ]);
        $creator = User::factory()->create(['role' => 'ventas']);

        $this->order = Order::create([
            'order_number' => 'PED-TRA-0001',
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

    public function test_record_movement_helper_creates_movement_with_user(): void
    {
        $user = User::factory()->create(['role' => 'almacen']);

        $movement = $this->order->recordMovement(
            Order::STATUS_CREADO,
            'Pedido creado',
            description: 'Detalle opcional',
            metadata: ['extra' => 1],
            user: $user,
        );

        $this->assertDatabaseHas('movements', [
            'id' => $movement->id,
            'trackable_type' => Order::class,
            'trackable_id' => $this->order->id,
            'user_id' => $user->id,
            'state' => Order::STATUS_CREADO,
            'action' => 'Pedido creado',
        ]);
    }

    public function test_timeline_shows_all_movements_in_order(): void
    {
        $this->order->recordMovement(Order::STATUS_CREADO, 'Pedido creado por ventas');
        $this->order->recordMovement(Order::STATUS_RECIBIDO, 'Entrada verificada en almacén');

        $response = $this->get(route('trazabilidad.index', [
            'order_number' => 'PED-TRA-0001',
        ]));

        $response->assertOk()
            ->assertSee('PED-TRA-0001')
            ->assertSee('Pedido creado por ventas')
            ->assertSee('Entrada verificada en almacén')
            ->assertSee('RECIBIDO');
    }

    public function test_search_by_folio_finds_order(): void
    {
        $this->get(route('trazabilidad.index', ['order_number' => 'PED-TRA-0001']))
            ->assertOk()
            ->assertSee('Tienda Trazabilidad')
            ->assertSee('Almacén Central');
    }

    public function test_search_accepts_partial_and_lowercase(): void
    {
        $this->get(route('trazabilidad.index', ['order_number' => 'ped-tra-00']))
            ->assertOk()
            ->assertSee('PED-TRA-0001');
    }

    public function test_search_without_results_shows_message(): void
    {
        $this->get(route('trazabilidad.index', ['order_number' => 'NO-EXISTE']))
            ->assertOk()
            ->assertSee('No se encontró ningún pedido');
    }

    public function test_trazabilidad_excludes_transporte_and_tienda(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'transporte']));
        $this->get(route('trazabilidad.index'))->assertForbidden();

        $this->actingAs(User::factory()->create(['role' => 'tienda']));
        $this->get(route('trazabilidad.index'))->assertForbidden();
    }

    public function test_status_summary_reflects_last_state(): void
    {
        $this->order->recordMovement(Order::STATUS_CREADO, 'Pedido creado por ventas');
        $this->order->recordMovement(Order::STATUS_EN_ALMACEN, 'Mercancía recibida en almacén');
        $this->order->recordMovement(Order::STATUS_RECIBIDO, 'Entrada verificada');

        $this->get(route('trazabilidad.index', ['order_number' => 'PED-TRA-0001']))
            ->assertOk()
            ->assertSee('RECIBIDO');
    }
}