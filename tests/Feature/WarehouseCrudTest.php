<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarehouseCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['role' => 'admin']));
    }

    private function warehouseData(array $overrides = []): array
    {
        return array_merge([
            'code' => 'ALM-TEST',
            'name' => 'Almacén Prueba',
            'address' => 'Parque Industrial 9',
            'active' => 1,
        ], $overrides);
    }

    public function test_admin_can_list_warehouses(): void
    {
        Warehouse::create($this->warehouseData());

        $this->get(route('almacenes.index'))
            ->assertOk()
            ->assertSee('Almacén Prueba');
    }

    public function test_admin_can_create_warehouse(): void
    {
        $this->post(route('almacenes.store'), $this->warehouseData())
            ->assertRedirect(route('almacenes.index'));

        $this->assertDatabaseHas('warehouses', ['code' => 'ALM-TEST']);
    }

    public function test_warehouse_code_must_be_unique(): void
    {
        Warehouse::create($this->warehouseData());

        $this->post(route('almacenes.store'), $this->warehouseData())
            ->assertSessionHasErrors('code');
    }

    public function test_admin_can_update_warehouse(): void
    {
        $warehouse = Warehouse::create($this->warehouseData());

        $this->put(
            route('almacenes.update', $warehouse),
            $this->warehouseData(['name' => 'Almacén Norte']),
        )->assertRedirect(route('almacenes.edit', $warehouse));

        $this->assertDatabaseHas('warehouses', ['id' => $warehouse->id, 'name' => 'Almacén Norte']);
    }

    public function test_admin_can_deactivate_warehouse(): void
    {
        $warehouse = Warehouse::create($this->warehouseData());

        $this->delete(route('almacenes.destroy', $warehouse))
            ->assertRedirect(route('almacenes.index'));

        $this->assertDatabaseHas('warehouses', ['id' => $warehouse->id, 'active' => false]);
    }

    public function test_admin_can_add_and_remove_locations(): void
    {
        $warehouse = Warehouse::create($this->warehouseData());

        $this->post(route('almacenes.locations.store', $warehouse), [
            'code' => 'ZONA-X',
            'description' => 'Zona de prueba',
        ])->assertRedirect();

        $this->assertDatabaseHas('warehouse_locations', [
            'warehouse_id' => $warehouse->id,
            'code' => 'ZONA-X',
        ]);

        $location = $warehouse->locations()->where('code', 'ZONA-X')->firstOrFail();

        $this->delete(route('almacenes.locations.destroy', $location))
            ->assertRedirect();

        $this->assertDatabaseMissing('warehouse_locations', ['id' => $location->id]);
    }

    public function test_location_code_required(): void
    {
        $warehouse = Warehouse::create($this->warehouseData());

        $this->post(route('almacenes.locations.store', $warehouse), ['code' => ''])
            ->assertSessionHasErrors('code');
    }

    public function test_non_admin_cannot_access_warehouses(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'almacen']));

        $this->get(route('almacenes.index'))->assertForbidden();
    }
}