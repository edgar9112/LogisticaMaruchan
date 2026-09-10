<?php

namespace Tests\Feature;

use App\Models\Presentation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PresentationCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['role' => 'admin']));
    }

    private function presentationData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Sopa Maruchan',
            'presentation_type' => 'vaso',
            'flavor' => 'Pollo',
            'pieces_per_box' => 24,
            'sku' => 'MAR-TEST-001',
            'active' => 1,
        ], $overrides);
    }

    public function test_admin_can_list_presentations(): void
    {
        Presentation::create($this->presentationData());

        $this->get(route('presentaciones.index'))
            ->assertOk()
            ->assertSee('MAR-TEST-001');
    }

    public function test_admin_can_search_presentations(): void
    {
        Presentation::create($this->presentationData(['sku' => 'MAR-BUS-001', 'flavor' => 'Camarón']));
        Presentation::create($this->presentationData(['sku' => 'MAR-OUT-001', 'flavor' => 'Res']));

        $this->get(route('presentaciones.index', ['search' => 'camarón']))
            ->assertOk()
            ->assertSee('MAR-BUS-001')
            ->assertDontSee('MAR-OUT-001');
    }

    public function test_admin_can_create_presentation(): void
    {
        $this->post(route('presentaciones.store'), $this->presentationData())
            ->assertRedirect(route('presentaciones.index'));

        $this->assertDatabaseHas('presentations', ['sku' => 'MAR-TEST-001']);
    }

    public function test_presentation_requires_valid_data(): void
    {
        $this->post(route('presentaciones.store'), $this->presentationData(['sku' => '']))
            ->assertSessionHasErrors('sku');

        $this->post(route('presentaciones.store'), $this->presentationData(['presentation_type' => 'tarro']))
            ->assertSessionHasErrors('presentation_type');

        $this->post(route('presentaciones.store'), $this->presentationData(['pieces_per_box' => -5]))
            ->assertSessionHasErrors('pieces_per_box');
    }

    public function test_presentation_sku_must_be_unique(): void
    {
        Presentation::create($this->presentationData());

        $this->post(route('presentaciones.store'), $this->presentationData())
            ->assertSessionHasErrors('sku');
    }

    public function test_admin_can_update_presentation(): void
    {
        $presentation = Presentation::create($this->presentationData());

        $this->put(route('presentaciones.update', $presentation), [
            ...$this->presentationData(['flavor' => 'Res', 'sku' => 'MAR-TEST-002']),
        ])->assertRedirect(route('presentaciones.index'));

        $this->assertDatabaseHas('presentations', [
            'id' => $presentation->id,
            'flavor' => 'Res',
            'sku' => 'MAR-TEST-002',
        ]);
    }

    public function test_admin_can_deactivate_presentation(): void
    {
        $presentation = Presentation::create($this->presentationData());

        $this->delete(route('presentaciones.destroy', $presentation))
            ->assertRedirect(route('presentaciones.index'));

        $this->assertDatabaseHas('presentations', [
            'id' => $presentation->id,
            'active' => false,
        ]);
    }

    public function test_non_admin_cannot_access_catalog(): void
    {
        $user = User::factory()->create(['role' => 'ventas']);
        $this->actingAs($user);

        $this->get(route('presentaciones.index'))->assertForbidden();
        $this->get(route('presentaciones.create'))->assertForbidden();
        $this->post(route('presentaciones.store'), $this->presentationData())->assertForbidden();
    }
}