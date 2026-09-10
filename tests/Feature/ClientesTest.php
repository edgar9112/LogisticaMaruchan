<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['role' => 'admin']));
    }

    private function customerData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Comercializadora Fénix',
            'email' => 'contacto@fenix.mx',
            'phone' => '555-9090',
            'address' => 'Av. Industrial 4',
            'active' => 1,
        ], $overrides);
    }

    public function test_admin_can_list_customers(): void
    {
        Customer::create($this->customerData());

        $this->get(route('clientes.index'))
            ->assertOk()
            ->assertSee('Comercializadora Fénix');
    }

    public function test_admin_can_search_customers(): void
    {
        Customer::create($this->customerData(['name' => 'Cliente Bus']));
        Customer::create($this->customerData(['name' => 'Otro Cliente']));

        $this->get(route('clientes.index', ['search' => 'Cliente Bus']))
            ->assertOk()
            ->assertSee('Cliente Bus')
            ->assertDontSee('Otro Cliente');
    }

    public function test_admin_can_create_customer(): void
    {
        $this->post(route('clientes.store'), $this->customerData())
            ->assertRedirect(route('clientes.index'));

        $this->assertDatabaseHas('customers', ['name' => 'Comercializadora Fénix']);
    }

    public function test_customer_name_required(): void
    {
        $this->post(route('clientes.store'), $this->customerData(['name' => '']))
            ->assertSessionHasErrors('name');
    }

    public function test_customer_email_must_be_valid(): void
    {
        $this->post(route('clientes.store'), $this->customerData(['email' => 'no-es-correo']))
            ->assertSessionHasErrors('email');
    }

    public function test_admin_can_update_customer(): void
    {
        $customer = Customer::create($this->customerData());

        $this->put(
            route('clientes.update', $customer),
            $this->customerData(['phone' => '555-1111']),
        )->assertRedirect(route('clientes.index'));

        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'phone' => '555-1111']);
    }

    public function test_admin_can_deactivate_customer(): void
    {
        $customer = Customer::create($this->customerData());

        $this->delete(route('clientes.destroy', $customer))
            ->assertRedirect(route('clientes.index'));

        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'active' => false]);
    }

    public function test_non_admin_cannot_access_customers(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'ventas']));

        $this->get(route('clientes.index'))->assertForbidden();
        $this->post(route('clientes.store'), $this->customerData())->assertForbidden();
    }
}