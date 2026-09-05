<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\AdminUpdateNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class MemberNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_read_notification_is_removed_from_bell_list(): void
    {
        $member = User::factory()->create(['is_admin' => false]);
        $member->notify(new AdminUpdateNotification(
            'Kemas kini order',
            'Order anda telah dikemaskini.',
            route('member.dashboard'),
        ));
        $notification = $member->notifications()->latest()->firstOrFail();

        $this->actingAs($member)
            ->get(route('member.dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('memberNotificationUnreadCount', 1)
                ->has('memberNotifications', 1)
                ->where('memberNotifications.0.id', (string) $notification->id)
            );

        $this->actingAs($member)
            ->post(route('member.notifications.read', $notification->id))
            ->assertRedirect(route('member.dashboard'));

        $this->assertNotNull($notification->fresh()->read_at);
        $this->actingAs($member)
            ->get(route('member.dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('memberNotificationUnreadCount', 0)
                ->has('memberNotifications', 0)
            );
    }

    public function test_read_all_notifications_are_removed_from_bell_list(): void
    {
        $member = User::factory()->create(['is_admin' => false]);
        $member->notify(new AdminUpdateNotification('Kemas kini pertama', 'Mesej pertama.', route('member.dashboard')));
        $member->notify(new AdminUpdateNotification('Kemas kini kedua', 'Mesej kedua.', route('member.dashboard')));

        $this->from(route('member.dashboard'))
            ->actingAs($member)
            ->post(route('member.notifications.read-all'))
            ->assertRedirect(route('member.dashboard'));

        $this->actingAs($member)
            ->get(route('member.dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('memberNotificationUnreadCount', 0)
                ->has('memberNotifications', 0)
            );
    }
}
