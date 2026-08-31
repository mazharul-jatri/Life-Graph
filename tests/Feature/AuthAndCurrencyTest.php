<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAndCurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_renders_successfully_for_guests(): void
    {
        $this->seed();
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_authenticated_user_is_redirected_from_landing_page_to_simulator(): void
    {
        $this->seed();
        $user = User::first();
        $this->actingAs($user);

        $response = $this->get('/');
        $response->assertRedirect('/simulator');
    }

    public function test_user_can_register_with_currency_and_age(): void
    {
        $response = $this->post('/register', [
            'name' => 'Tanvir Ahmed',
            'email' => 'tanvir@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'current_age' => 26.0,
            'currency' => 'BDT',
        ]);

        $response->assertRedirect('/simulator');
        $this->assertAuthenticated();

        $user = User::where('email', 'tanvir@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNotNull($user->profile);
        $this->assertEquals(26.0, $user->profile->current_age);
        $this->assertEquals('BDT', $user->profile->currency);
    }

    public function test_demo_login_authenticates_user(): void
    {
        $this->seed();

        $response = $this->get('/demo.login');
        if ($response->status() === 404) {
            $response = $this->get('/demo-login');
        }

        $response->assertRedirect('/simulator');
        $this->assertAuthenticated();
    }

    public function test_user_can_update_currency_via_api(): void
    {
        $this->seed();
        $user = User::first();
        $this->actingAs($user);

        $response = $this->postJson('/api/profile/update', [
            'currency' => 'EUR',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('EUR', $user->fresh()->profile->currency);
    }
}
