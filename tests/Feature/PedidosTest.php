<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Presentation;
use App\Models\Store;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PedidosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'ventas']);
        $this->actingAs($this->user);

        $this->store = Store::create(['code' => 'TDE-PED', 'name' => 'Tienda Pedidos']);
        $this->presentation = Presentation::create([
            'name' => 'Sopa Maruchan',
            'presentation_type' => 'vaso',
            'flavor' => 'Pollo',
            'pieces_per_box' => 24,
            'sku' => 'MAR-PED-01',
        ]);
        Warehouse::create(['code' => 'ALM-PED', 'name' => 'Almacén Central']);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'store_id' => $this->store->id,
            'notes' => 'Entrega urgente',
            'items' => [
                ['presentation_id' => $this->presentation->id, 'quantity' => 10],
            ],
        ], $overrides);
    }

    public function test_ventas_can_list_orders(): void
    {
        $this->post(route('ventas.pedidos.store'), $this->payload());

        $this->get(route('ventas.pedidos'))
            ->assertOk()
            ->assertSee('Tienda Pedidos');
    }

    public function test_ventas_can_create_order_with_folio_and_items(): void
    {
        $response = $this->post(route('ventas.pedidos.store'), $this->payload());

        $order = Order::firstOrFail();
        $this->assertMatchesRegularExpression('/^PED-\d{8}-\d{4}$/', $order->order_number);
        $response->assertRedirect(route('ventas.pedidos.show', $order));

        $this->assertSame(Order::STATUS_CREADO, $order->status);
        $this->assertSame($this->store->id, $order->store_id);
        $this->assertSame($this->user->id, $order->user_id);
        $this->assertCount(1, $order->items);
        $this->assertSame(10, $order->items->first()->quantity_requested);
    }

    public function test_order_creation_registers_initial_movement(): void
    {
        $this->post(route('ventas.pedidos.store'), $this->payload());

        $order = Order::firstOrFail();

        $this->assertSame(1, $order->movements()->count());
        $this->assertSame(Order::STATUS_CREADO, $order->movements()->first()->state);
        $this->assertSame($this->user->id, $order->movements()->first()->user_id);
    }

    public function test_folios_are_sequential_and_unique(): void
    {
        $this->post(route('ventas.pedidos.store'), $this->payload());
        $this->post(route('ventas.pedidos.store'), $this->payload());

        $folios = Order::orderBy('id')->pluck('order_number');

        $this->assertCount(2, $folios->unique());
        $this->assertNotSame($folios[0], $folios[1]);
    }

    public function test_order_requires_store_and_items(): void
    {
        $this->post(route('ventas.pedidos.store'), $this->payload(['store_id' => '']))
            ->assertSessionHasErrors('store_id');

        $this->post(route('ventas.pedidos.store'), $this->payload(['items' => []]))
            ->assertSessionHasErrors('items');
    }

    public function test_order_items_cannot_be_duplicated_or_empty(): void
    {
        $this->post(route('ventas.pedidos.store'), $this->payload([
            'items' => [
                ['presentation_id' => $this->presentation->id, 'quantity' => 5],
                ['presentation_id' => $this->presentation->id, 'quantity' => 3],
            ],
        ]))->assertSessionHasErrors('items.*.presentation_id');

        $this->post(route('ventas.pedidos.store'), $this->payload([
            'items' => [
                ['presentation_id' => $this->presentation->id, 'quantity' => 0],
            ],
        ]))->assertSessionHasErrors('items.*.quantity');
    }

    public function test_order_cannot_be_created_with_inactive_presentation(): void
    {
        $this->presentation->update(['active' => false]);

        $this->post(route('ventas.pedidos.store'), $this->payload())
            ->assertSessionHasErrors('items.*.presentation_id');
    }

    public function test_only_ventas_or_admin_can_manage_orders(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'almacen']));

        $this->get(route('ventas.pedidos'))->assertForbidden();
        $this->get(route('ventas.pedidos.create'))->assertForbidden();
        $this->post(route('ventas.pedidos.store'), $this->payload())->assertForbidden();
    }

    public function test_ventas_can_see_order_detail_with_history(): void
    {
        $order = Order::create([
            'order_number' => 'PED-TEST-0001',
            'store_id' => $this->store->id,
            'user_id' => $this->user->id,
            'warehouse_id' => Warehouse::firstOrFail()->id,
            'status' => Order::STATUS_CREADO,
            'ordered_at' => now(),
        ]);
        $order->items()->create([
            'presentation_id' => $this->presentation->id,
            'quantity_requested' => 8,
        ]);
        $order->movements()->create([
            'user_id' => $this->user->id,
            'state' => Order::STATUS_CREADO,
            'action' => 'Pedido creado por ventas',
        ]);

        $this->get(route('ventas.pedidos.show', $order))
            ->assertOk()
            ->assertSee('PED-TEST-0001')
            ->assertSee('Pedido creado por ventas')
            ->assertSee('8');
    }
}