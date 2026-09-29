<?php

namespace Tests\Feature;

use App\Models\Review;
use App\Models\SupportTicket;
use App\Models\User;
use App\Notifications\TicketCustomerNotification;
use App\Notifications\TicketStaffNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HelpDeskTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('admin', 'web'));

        return $user;
    }

    private function openTicket(): SupportTicket
    {
        $this->post(route('support.tickets.store'), [
            'name' => 'Karim',
            'email' => 'karim@example.com',
            'category' => 'installation',
            'priority' => 'high',
            'subject' => 'Installer stops at PHP step',
            'message' => 'The installer exits with an error while installing PHP-FPM.',
            'server_os' => 'Ubuntu 24.04',
        ])->assertRedirect();

        return SupportTicket::firstOrFail();
    }

    public function test_customer_opens_a_ticket_and_both_sides_are_emailed(): void
    {
        Notification::fake();

        $ticket = $this->openTicket();

        $this->assertMatchesRegularExpression('/^DP-[A-Z0-9]{6}$/', $ticket->reference);
        $this->assertSame('open', $ticket->status);

        Notification::assertSentTo(new AnonymousNotifiable, TicketCustomerNotification::class,
            fn ($n, $channels, $notifiable) => $notifiable->routes['mail'] === 'karim@example.com');
        Notification::assertSentTo(new AnonymousNotifiable, TicketStaffNotification::class,
            fn ($n, $channels, $notifiable) => $notifiable->routes['mail'] === config('site.support_email'));
    }

    public function test_ticket_page_requires_the_private_token(): void
    {
        Notification::fake();
        $ticket = $this->openTicket();

        $this->get(route('support.tickets.show', ['reference' => $ticket->reference]))->assertNotFound();
        $this->get(route('support.tickets.show', ['reference' => $ticket->reference, 'token' => 'wrong']))->assertNotFound();

        $this->get($ticket->publicUrl())
            ->assertOk()
            ->assertSee('Installer stops at PHP step')
            ->assertSee('noindex, nofollow', false);
    }

    public function test_customer_and_staff_can_reply(): void
    {
        Notification::fake();
        $ticket = $this->openTicket();

        $this->actingAs($this->admin())
            ->post(route('admin.tickets.reply', $ticket), ['body' => 'Please run sudo dpanel doctor.', 'status' => 'answered'])
            ->assertRedirect();

        $this->assertSame('answered', $ticket->fresh()->status);
        Notification::assertSentTo(new AnonymousNotifiable, TicketCustomerNotification::class, fn ($n) => $n->reply !== null);

        auth()->logout();

        $this->post(route('support.tickets.reply', ['reference' => $ticket->reference]), [
            'token' => $ticket->access_token,
            'body' => 'Doctor says PHP 8.3 is missing.',
        ])->assertRedirect();

        $this->assertSame('open', $ticket->fresh()->status);
        $this->assertSame(2, $ticket->replies()->count());

        $this->get($ticket->publicUrl())
            ->assertSee('Please run sudo dpanel doctor.')
            ->assertSee('Doctor says PHP 8.3 is missing.');
    }

    public function test_closed_ticket_rejects_customer_replies(): void
    {
        Notification::fake();
        $ticket = $this->openTicket();
        $ticket->update(['status' => 'closed']);

        $this->post(route('support.tickets.reply', ['reference' => $ticket->reference]), [
            'token' => $ticket->access_token,
            'body' => 'One more thing',
        ])->assertForbidden();
    }

    public function test_help_desk_is_admin_only(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->get(route('dashboard'))->assertForbidden();

        $admin = $this->admin();
        foreach (['dashboard', 'admin.tickets.index', 'admin.reviews.index', 'admin.feedback.index'] as $name) {
            $this->actingAs($admin)->get(route($name))->assertOk();
        }
    }

    public function test_admin_moderates_reviews(): void
    {
        $this->post(route('reviews.store'), [
            'name' => 'Nadia',
            'email' => 'nadia@example.com',
            'rating' => 4,
            'title' => 'Solid and free',
            'body' => 'Migrated three sites from cPanel without trouble.',
        ]);

        $review = Review::firstOrFail();

        $this->actingAs($this->admin())
            ->patch(route('admin.reviews.update', $review), ['status' => 'approved'])
            ->assertRedirect();

        $this->assertNotNull($review->fresh()->approved_at);
        $this->get(route('home'))->assertSee('Solid and free');
    }
}
