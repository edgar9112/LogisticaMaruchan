<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['role' => 'admin']));
    }

    private function storeData(array $overrides = []): array
    {
        return array_merge([
            'code' => 'TDE-TEST',
            'name' => 'Tienda Prueba',
            'address' => 'Av. Prueba 123',
            'phone' => '555-1234',
            'contact_name' => 'María López',
            'opening_hours' => 'Lun–Sáb 9:00–18:00',
            'active' => 1,
        ], $overrides);
    }

    public function test_admin_can_list_stores(): void
    {
        Store::create($this->storeData());

        $this->get(route('tiendas.index'))
            ->assertOk()
            ->assertSee('Tienda Prueba');
    }

    public function test_admin_can_search_stores(): void
    {
        Store::create($this->storeData(['code' => 'TDE-BUS', 'name' => 'Buscar Me']));
        Store::create($this->storeData(['code' => 'TDE-OTR', 'name' => 'Otro']));

        $this->get(route('tiendas.index', ['search' => 'Buscar Me']))
            ->assertOk()
            ->assertSee('TDE-BUS')
            ->assertDontSee('TDE-OTR');
    }

    public function test_admin_can_create_store(): void
    {
        $this->post(route('tiendas.store'), $this->storeData())
            ->assertRedirect(route('tiendas.index'));

        $this->assertDatabaseHas('stores', ['code' => 'TDE-TEST']);
    }

    public function test_store_code_must_be_unique(): void
    {
        Store::create($this->storeData());

        $this->post(route('tiendas.store'), $this->storeData())
            ->assertSessionHasErrors('code');
    }

    public function test_admin_can_create_store_with_contact_and_hours(): void
    {
        $this->post(route('tiendas.store'), $this->storeData())
            ->assertRedirect(route('tiendas.index'));

        $this->assertDatabaseHas('stores', [
            'code' => 'TDE-TEST',
            'contact_name' => 'María López',
            'opening_hours' => 'Lun–Sáb 9:00–18:00',
        ]);
    }

    public function test_admin_can_update_store(): void
    {
        $store = Store::create($this->storeData());

        $this->put(route('tiendas.update', $store), $this->storeData(['name' => 'Tienda Renombrada']))
            ->assertRedirect(route('tiendas.index'));

        $this->assertDatabaseHas('stores', [
            'id' => $store->id,
            'name' => 'Tienda Renombrada',
        ]);
    }

    public function test_admin_can_deactivate_store(): void
    {
        $store = Store::create($this->storeData());

        $this->delete(route('tiendas.destroy', $store))
            ->assertRedirect(route('tiendas.index'));

        $this->assertDatabaseHas('stores', ['id' => $store->id, 'active' => false]);
    }

    public function test_non_admin_cannot_access_stores(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'ventas']));

        $this->get(route('tiendas.index'))->assertForbidden();
        $this->post(route('tiendas.store'), $this->storeData())->assertForbidden();
    }
}