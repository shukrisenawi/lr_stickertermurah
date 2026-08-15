<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberOrderRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_orders_route_redirects_to_order_form(): void
    {
        $member = User::factory()->create(['is_admin' => false]);

        $this->actingAs($member)
            ->get(route('member.orders.index'))
            ->assertRedirect(route('orders.create'));
    }
}
