<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolesTest extends TestCase
{
    use RefreshDatabase;

    private static int $counter = 0;

    private function loginAs(string $role): User
    {
        self::$counter++;
        $store = Store::create([
            'code' => 'TIENDA-ROL-'.self::$counter,
            'name' => 'Tienda',
        ]);

        $user = User::factory()->create([
            'role' => $role,
            'store_id' => $store->id,
        ]);

        $this->actingAs($user);

        return $user;
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
        $this->get('/ventas/pedidos')->assertRedirect(route('login'));
    }

    public function test_admin_can_access_all_areas(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->get(route('dashboard'))->assertOk();
        $this->get(route('ventas.pedidos'))->assertOk();
        $this->get(route('almacen.pendientes'))->assertOk();
        $this->get(route('embarques.index'))->assertOk();
        $this->get(route('viajes.index'))->assertOk();
        $this->get(route('tienda.recepciones'))->assertOk();
    }

    public function test_each_role_lands_on_its_own_module(): void
    {
        $this->loginAs('ventas');
        $this->get(route('ventas.pedidos'))->assertOk();

        $this->loginAs('almacen');
        $this->get(route('almacen.pendientes'))->assertOk();

        $this->loginAs('logistica');
        $this->get(route('embarques.index'))->assertOk();

        $this->loginAs('transporte');
        $this->get(route('viajes.index'))->assertOk();

        $this->loginAs('tienda');
        $this->get(route('tienda.recepciones'))->assertOk();
    }

    public function test_roles_cannot_access_other_areas(): void
    {
        $this->loginAs('ventas');

        $this->get(route('almacen.pendientes'))->assertForbidden();
        $this->get(route('embarques.index'))->assertForbidden();
        $this->get(route('viajes.index'))->assertForbidden();
        $this->get(route('tienda.recepciones'))->assertForbidden();
        $this->get(route('dashboard'))->assertForbidden();
    }

    public function test_dashboard_module_is_admin_only(): void
    {
        $this->loginAs('almacen');
        $this->get(route('dashboard'))->assertForbidden();
    }

    public function test_login_redirects_each_role_to_its_module(): void
    {
        $store = Store::create(['code' => 'TIENDA-X', 'name' => 'Tienda X']);
        $user = User::factory()->create([
            'role' => 'almacen',
            'store_id' => $store->id,
            'password' => bcrypt('password'),
        ]);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('almacen.pendientes'));

        $this->assertAuthenticatedAs($user);
    }
}