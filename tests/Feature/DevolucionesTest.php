<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\Presentation;
use App\Models\Store;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DevolucionesTest extends TestCase
{
    use RefreshDatabase;

    private Store $store;
    private Warehouse $warehouse;

    protected function setUp(): void
    {
        parent::setUp();

        $this->store = Store::create(['code' => 'TDE-DEV', 'name' => 'Tienda Devoluciones']);
        $this->warehouse = Warehouse::create(['code' => 'ALM-DEV', 'name' => 'Almacén Central']);
    }

    private function makeDeliveredOrder(string $status = Order::STATUS_RECIBIDO_TIENDA): Order
    {
        $presentation = Presentation::create([
            'name' => 'Sopa Maruchan',
            'presentation_type' => 'vaso',
            'flavor' => 'Pollo',
            'sku' => 'MAR-DEV-'.rand(1000, 9999),
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

    private function tiendaUser(Store $store = null): User
    {
        return User::factory()->create([
            'role' => 'tienda',
            'store_id' => $store?->id ?? $this->store->id,
        ]);
    }

    private function registerReturn(Order $order, array $overrides = []): \Illuminate\Testing\TestResponse
    {
        $item = $order->items->first();

        return $this->post(route('devoluciones.store', $order), array_merge([
            'reason_type' => 'sobrante',
            'note' => 'Sobraron unidades',
            'items' => [$item->id => ['quantity' => 3, 'condition' => 'buena']],
        ], $overrides));
    }

    public function test_tienda_registra_devolucion_con_items_y_movimiento(): void
    {
        $order = $this->makeDeliveredOrder();

        $this->actingAs($this->tiendaUser())
            ->registerReturn($order)
            ->assertRedirect();

        $this->assertDatabaseHas('returns', [
            'order_id' => $order->id,
            'status' => OrderReturn::STATUS_SOLICITADA,
            'reason_type' => 'sobrante',
        ]);

        $devolucion = $order->returns()->first();

        $this->assertDatabaseHas('return_items', [
            'return_id' => $devolucion->id,
            'order_item_id' => $order->items->first()->id,
            'quantity' => 3,
        ]);

        $this->assertDatabaseHas('movements', [
            'trackable_type' => Order::class,
            'trackable_id' => $order->id,
            'action' => 'Devolución solicitada desde tienda',
        ]);
    }

    public function test_cantidad_devuelta_no_excede_lo_recibido(): void
    {
        $order = $this->makeDeliveredOrder();
        $item = $order->items->first();

        $this->actingAs($this->tiendaUser())
            ->from(route('devoluciones.create', $order))
            ->post(route('devoluciones.store', $order), [
                'reason_type' => 'sobrante',
                'items' => [$item->id => ['quantity' => 999, 'condition' => 'buena']],
            ])
            ->assertSessionHasErrors()
            ->assertSessionHas('errors');

        $this->assertDatabaseCount('returns', 0);
        $this->assertDatabaseCount('return_items', 0);
    }

    public function test_devolucion_requiere_motivo_valido(): void
    {
        $order = $this->makeDeliveredOrder();
        $item = $order->items->first();

        $this->actingAs($this->tiendaUser())
            ->from(route('devoluciones.create', $order))
            ->post(route('devoluciones.store', $order), [
                'reason_type' => 'motivo_invalido',
                'items' => [$item->id => ['quantity' => 1]],
            ])
            ->assertSessionHasErrors('reason_type');

        $this->assertDatabaseCount('returns', 0);
    }

    public function test_tienda_no_puede_devolver_pedido_de_otra_tienda(): void
    {
        $otherStore = Store::create(['code' => 'TDE-OTRA', 'name' => 'Otra Tienda']);
        $order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'store_id' => $otherStore->id,
            'user_id' => User::factory()->create(['role' => 'ventas'])->id,
            'warehouse_id' => $this->warehouse->id,
            'status' => Order::STATUS_RECIBIDO_TIENDA,
            'ordered_at' => now(),
        ]);

        $this->actingAs($this->tiendaUser())
            ->from(route('devoluciones.index'))
            ->post(route('devoluciones.store', $order), [
                'reason_type' => 'sobrante',
            ])
            ->assertSessionHasErrors();

        $this->assertDatabaseCount('returns', 0);
    }

    public function test_no_se_puede_devolver_un_pedido_no_entregado(): void
    {
        $order = $this->makeDeliveredOrder(Order::STATUS_EN_TRANSITO);

        $this->actingAs($this->tiendaUser())
            ->from(route('devoluciones.index'))
            ->post(route('devoluciones.store', $order), [
                'reason_type' => 'sobrante',
            ])
            ->assertSessionHasErrors();

        $this->assertDatabaseCount('returns', 0);
    }

    public function test_almacen_recibe_la_devolucion(): void
    {
        $order = $this->makeDeliveredOrder();
        $this->actingAs($this->tiendaUser())->registerReturn($order)->assertRedirect();
        $devolucion = $order->returns()->first();

        $almacen = User::factory()->create(['role' => 'almacen']);

        $this->actingAs($almacen)
            ->post(route('devoluciones.recibir', $devolucion))
            ->assertRedirect();

        $this->assertDatabaseHas('returns', [
            'id' => $devolucion->id,
            'status' => OrderReturn::STATUS_RECIBIDA,
            'received_by' => $almacen->id,
        ]);
        $this->assertNotNull($devolucion->fresh()->received_at);
        $this->assertDatabaseHas('movements', [
            'trackable_type' => Order::class,
            'trackable_id' => $order->id,
            'action' => 'Devolución recibida en almacén',
        ]);
    }

    public function test_admin_puede_registrar_y_recibir_devolucion(): void
    {
        $order = $this->makeDeliveredOrder();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->registerReturn($order)
            ->assertRedirect();

        $devolucion = $order->returns()->first();

        $this->actingAs($admin)
            ->post(route('devoluciones.recibir', $devolucion))
            ->assertRedirect();

        $this->assertDatabaseHas('returns', [
            'id' => $devolucion->id,
            'status' => OrderReturn::STATUS_RECIBIDA,
            'received_by' => $admin->id,
        ]);
    }

    public function test_tienda_solo_ve_devoluciones_de_su_tienda(): void
    {
        $own = $this->makeDeliveredOrder();
        $this->actingAs($this->tiendaUser())->registerReturn($own)->assertRedirect();

        $otherStore = Store::create(['code' => 'TDE-DEV2', 'name' => 'Otra Tienda']);

        $otherOrder = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'store_id' => $otherStore->id,
            'user_id' => User::factory()->create(['role' => 'ventas'])->id,
            'warehouse_id' => $this->warehouse->id,
            'status' => Order::STATUS_CERRADO,
            'ordered_at' => now(),
        ]);
        $otherItem = $otherOrder->items()->create([
            'presentation_id' => Presentation::create([
                'name' => 'Sopa Maruchan',
                'presentation_type' => 'vaso',
                'flavor' => 'Pollo',
                'sku' => 'MAR-DEV-O-'.rand(1000, 9999),
            ])->id,
            'quantity_requested' => 10,
            'quantity_received' => 10,
            'quantity_prepared' => 10,
        ]);
        $otherTienda = $this->tiendaUser($otherStore);
        $this->actingAs($otherTienda)
            ->post(route('devoluciones.store', $otherOrder), [
                'reason_type' => 'sobrante',
                'items' => [$otherItem->id => ['quantity' => 1, 'condition' => 'buena']],
            ])
            ->assertRedirect();

        $this->actingAs($this->tiendaUser())
            ->get(route('devoluciones.index'))
            ->assertOk()
            ->assertSee($own->order_number)
            ->assertDontSee($otherOrder->order_number);
    }

    public function test_acceso_control_devoluciones(): void
    {
        $order = $this->makeDeliveredOrder();
        $this->actingAs($this->tiendaUser())->registerReturn($order)->assertRedirect();
        $devolucion = $order->returns()->first();

        $this->actingAs(User::factory()->create(['role' => 'ventas']))
            ->get(route('devoluciones.index'))
            ->assertForbidden();

        $this->actingAs(User::factory()->create(['role' => 'ventas']))
            ->get(route('devoluciones.create', $order))
            ->assertForbidden();

        $this->actingAs(User::factory()->create(['role' => 'transporte']))
            ->post(route('devoluciones.recibir', $devolucion))
            ->assertForbidden();
    }
}