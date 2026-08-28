<?php

namespace Tests\Feature;

use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminTestimonialBadgeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_receives_count_of_unapproved_testimonials(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        Testimonial::query()->create([
            'name' => 'Pelanggan Baru',
            'text' => 'Servis sangat baik.',
            'is_approved' => false,
        ]);

        Testimonial::query()->create([
            'name' => 'Pelanggan Lama',
            'text' => 'Rekaan cantik.',
            'is_approved' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.profile.edit'));

        $response->assertInertia(fn (Assert $page) => $page
            ->where('testimonialCounts.adminPending', 1)
            ->where('adminNotifications.0.key', 'testimonials-pending')
            ->where('adminNotifications.0.count', 1)
        );
    }
}
