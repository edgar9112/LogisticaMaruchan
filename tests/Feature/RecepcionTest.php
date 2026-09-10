<?php

namespace Tests\Feature;

use App\Models\Incident;
use App\Models\Order;
use App\Models\Presentation;
use App\Models\Store;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecepcionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'almacen']);
        $this->actingAs($this->user);

        $this->store = Store::create(['code' => 'TDE-REC', 'name' => 'Tienda Recepción']);
        $warehouse = Warehouse::create(['code' => 'ALM-REC', 'name' => 'Almacén Central']);
        $presentation = Presentation::create([
            'name' => 'Sopa Maruchan',
            'presentation_type' => 'vaso',
            'flavor' => 'Pollo',
            'sku' => 'MAR-REC-01',
        ]);
        $creator = User::factory()->create(['role' => 'ventas']);

        $this->order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'store_id' => $this->store->id,
            'user_id' => $creator->id,
            'warehouse_id' => $warehouse->id,
            'status' => Order::STATUS_CREADO,
            'ordered_at' => now(),
        ]);
        $this->item = $this->order->items()->create([
            'presentation_id' => $presentation->id,
            'quantity_requested' => 20,
        ]);
    }

    public function test_almacen_sees_pending_orders(): void
    {
        $this->get(route('almacen.pendientes'))
            ->assertOk()
            ->assertSee($this->order->order_number);
    }

    public function test_almacen_can_register_reception(): void
    {
        $response = $this->post(route('almacen.pedidos.entrada', $this->order), [
            'quantities' => [$this->item->id => 18],
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'status' => Order::STATUS_EN_ALMACEN,
        ]);
        $this->assertNotNull($this->order->fresh()->received_at);
        $this->assertDatabaseHas('order_items', [
            'id' => $this->item->id,
            'quantity_received' => 18,
        ]);
        $this->assertDatabaseHas('movements', [
            'trackable_type' => Order::class,
            'trackable_id' => $this->order->id,
            'state' => Order::STATUS_EN_ALMACEN,
            'user_id' => $this->user->id,
        ]);

        $response->assertRedirect(route('almacen.pendientes'));
    }

    public function test_almacen_can_register_incident_during_reception(): void
    {
        $this->post(route('almacen.pedidos.entrada', $this->order), [
            'quantities' => [$this->item->id => 15],
            'incident_type' => Incident::TYPE_FALTANTE,
            'incident_description' => 'Faltan 5 piezas',
        ]);

        $this->assertDatabaseHas('incidents', [
            'order_id' => $this->order->id,
            'type' => Incident::TYPE_FALTANTE,
            'description' => 'Faltan 5 piezas',
        ]);
    }

    public function test_almacen_can_confirm_verification(): void
    {
        $this->order->update([
            'status' => Order::STATUS_EN_ALMACEN,
            'received_at' => now(),
        ]);

        $response = $this->post(route('almacen.pedidos.confirmar', $this->order));

        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'status' => Order::STATUS_RECIBIDO,
        ]);
        $this->assertDatabaseHas('movements', [
            'trackable_type' => Order::class,
            'trackable_id' => $this->order->id,
            'state' => Order::STATUS_RECIBIDO,
        ]);

        $response->assertRedirect(route('almacen.pendientes'));
    }

    public function test_cannot_receive_an_order_twice(): void
    {
        $this->order->update(['status' => Order::STATUS_RECIBIDO]);

        $this->post(route('almacen.pedidos.entrada', $this->order), [
            'quantities' => [$this->item->id => 20],
        ])->assertRedirect(route('almacen.pendientes'));

        $this->assertSame(Order::STATUS_RECIBIDO, $this->order->fresh()->status);
    }

    public function test_cannot_confirm_without_being_in_warehouse(): void
    {
        $this->post(route('almacen.pedidos.confirmar', $this->order))
            ->assertRedirect(route('almacen.pendientes'));

        $this->assertSame(Order::STATUS_CREADO, $this->order->fresh()->status);
    }

    public function test_reception_quantities_are_validated(): void
    {
        $this->post(route('almacen.pedidos.entrada', $this->order), [
            'quantities' => [$this->item->id => -1],
        ])->assertSessionHasErrors('quantities.'.$this->item->id);
    }

    public function test_only_almacen_or_admin_can_receive(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'ventas']));

        $this->get(route('almacen.pendientes'))->assertForbidden();
        $this->get(route('almacen.pedidos.recibir', $this->order))->assertForbidden();
        $this->post(route('almacen.pedidos.entrada', $this->order), [
            'quantities' => [$this->item->id => 20],
        ])->assertForbidden();
    }

    public function test_almacen_can_view_order_detail(): void
    {
        $this->order->movements()->create([
            'user_id' => $this->user->id,
            'state' => Order::STATUS_CREADO,
            'action' => 'Pedido creado por ventas',
        ]);

        $this->get(route('almacen.pedidos.show', $this->order))
            ->assertOk()
            ->assertSee($this->order->order_number)
            ->assertSee('Pedido creado por ventas');
    }
}