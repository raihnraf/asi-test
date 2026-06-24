<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerifiedAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_unverified_user_is_redirected_from_business_routes(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get('/products')
            ->assertRedirect(route('verification.notice', absolute: false));

        $this->actingAs($user)
            ->get('/sales-orders')
            ->assertRedirect(route('verification.notice', absolute: false));

        $this->actingAs($user)
            ->get('/sales-orders/create')
            ->assertRedirect(route('verification.notice', absolute: false));
    }
}
