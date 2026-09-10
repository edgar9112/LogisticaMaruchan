<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create(['role' => 'ventas']);
        $this->actingAs($user);

        $this->post(route('logout'))
            ->assertRedirect('/');

        $this->assertGuest();
    }

    public function test_guest_redirected_to_login_on_logout(): void
    {
        $this->post(route('logout'))
            ->assertRedirect(route('login'));
    }
}