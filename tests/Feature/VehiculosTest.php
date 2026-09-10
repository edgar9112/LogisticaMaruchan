<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehiculosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['role' => 'admin']));
    }

    private function vehicleData(array $overrides = []): array
    {
        return array_merge([
            'code' => 'VEH-01',
            'plate' => 'ABC-123',
            'driver_name' => 'Luis Ramírez',
            'capacity' => 1200,
            'active' => 1,
        ], $overrides);
    }

    public function test_admin_can_list_vehicles(): void
    {
        Vehicle::create($this->vehicleData());

        $this->get(route('vehiculos.index'))
            ->assertOk()
            ->assertSee('VEH-01')
            ->assertSee('ABC-123');
    }

    public function test_admin_can_create_vehicle(): void
    {
        $this->post(route('vehiculos.store'), $this->vehicleData())
            ->assertRedirect(route('vehiculos.index'));

        $this->assertDatabaseHas('vehicles', ['code' => 'VEH-01']);
    }

    public function test_vehicle_code_must_be_unique(): void
    {
        Vehicle::create($this->vehicleData());

        $this->post(route('vehiculos.store'), $this->vehicleData())
            ->assertSessionHasErrors('code');
    }

    public function test_vehicle_code_required(): void
    {
        $this->post(route('vehiculos.store'), $this->vehicleData(['code' => '']))
            ->assertSessionHasErrors('code');
    }

    public function test_admin_can_view_create_form(): void
    {
        $this->get(route('vehiculos.create'))
            ->assertOk()
            ->assertSee('Nuevo vehículo');
    }

    public function test_admin_can_update_vehicle(): void
    {
        $vehicle = Vehicle::create($this->vehicleData());

        $this->put(
            route('vehiculos.update', $vehicle),
            $this->vehicleData(['driver_name' => 'Marta Díaz']),
        )->assertRedirect(route('vehiculos.index'));

        $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id, 'driver_name' => 'Marta Díaz']);
    }

    public function test_admin_can_deactivate_vehicle(): void
    {
        $vehicle = Vehicle::create($this->vehicleData());

        $this->delete(route('vehiculos.destroy', $vehicle))
            ->assertRedirect(route('vehiculos.index'));

        $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id, 'active' => false]);
    }

    public function test_non_admin_cannot_access_vehicles(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'transporte']));

        $this->get(route('vehiculos.index'))->assertForbidden();
        $this->post(route('vehiculos.store'), $this->vehicleData())->assertForbidden();
    }
}