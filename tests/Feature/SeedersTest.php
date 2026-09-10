<?php

namespace Tests\Feature;

use App\Models\Presentation;
use App\Models\Store;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeedersTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeders_load_catalog_and_users(): void
    {
        $this->seed();

        $this->assertSame(5, Store::count());

        $this->assertSame(1, Warehouse::count());
        $this->assertSame(4, WarehouseLocation::count());

        $this->assertSame(10, Presentation::count());
        $this->assertNotNull(Presentation::where('sku', 'MAR-VAS-POL-24')->first());

        $this->assertSame(3, Vehicle::count());
    }

    public function test_seeders_create_one_user_per_role(): void
    {
        $this->seed();

        foreach (User::ROLES as $role) {
            $this->assertDatabaseHas('users', [
                'role' => $role,
                'email' => "{$role}@maruchan.test",
            ]);
        }
    }

    public function test_seeded_user_can_login_with_demo_password(): void
    {
        $this->seed();

        $user = User::where('email', 'almacen@maruchan.test')->firstOrFail();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('almacen.pendientes'));
    }

    public function test_seeders_are_idempotent(): void
    {
        $this->seed();
        $this->seed();

        $this->assertSame(5, Store::count());
        $this->assertSame(10, Presentation::count());
        $this->assertSame(6, User::count());
    }
}