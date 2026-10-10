<?php

namespace Tests\Feature;

use App\Mail\PanelMessage;
use App\Models\Comment;
use App\Models\DocSync;
use App\Models\Donation;
use App\Models\DonationMethod;
use App\Models\OutboundMail;
use App\Models\Review;
use App\Models\Role;
use App\Models\Setting;
use App\Models\SupportTicket;
use App\Models\User;
use App\Support\AiAssistant;
use App\Support\Docs;
use App\Support\MailSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Mockery\MockInterface;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_roles_limit_what_staff_can_open(): void
    {
        $support = User::factory()->role('support')->create();
        $editor = User::factory()->role('editor')->create();

        $this->actingAs($support)->get(route('admin.tickets.index'))->assertOk();
        $this->actingAs($support)->get(route('admin.comments.index'))->assertOk();
        $this->actingAs($support)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($support)->get(route('admin.settings.edit'))->assertForbidden();
        $this->actingAs($support)->get(route('admin.donations.index'))->assertForbidden();

        $this->actingAs($editor)->get(route('dashboard'))->assertOk();
        $this->actingAs($editor)->get(route('admin.docs.index'))->assertOk();
        $this->actingAs($editor)->get(route('admin.tickets.index'))->assertForbidden();

        $this->actingAs(User::factory()->create())->get(route('dashboard'))->assertForbidden();
    }

    public function test_every_admin_page_renders_for_an_administrator(): void
    {
        $admin = $this->admin();

        foreach ([
            'dashboard', 'admin.tickets.index', 'admin.comments.index', 'admin.reviews.index', 'admin.feedback.index',
            'admin.docs.index', 'admin.mail.index', 'admin.donations.index', 'admin.users.index', 'admin.roles.index',
            'admin.settings.edit',
        ] as $name) {
            $this->actingAs($admin)->get(route($name))->assertOk();
        }
    }

    public function test_admin_creates_a_role_and_assigns_it(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.roles.store'), [
            'name' => 'Donations team',
            'permissions' => ['donations.manage'],
        ])->assertRedirect();

        $role = Role::where('slug', 'donations-team')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Rafi',
            'email' => 'rafi@example.com',
            'password' => 'a-strong-password-1',
            'role_id' => $role->id,
        ])->assertRedirect();

        $rafi = User::where('email', 'rafi@example.com')->firstOrFail();

        $this->actingAs($rafi)->get(route('admin.donations.index'))->assertOk();
        $this->actingAs($rafi)->get(route('admin.tickets.index'))->assertForbidden();

        $this->actingAs($admin)->delete(route('admin.roles.destroy', $role))->assertSessionHasErrors('role');
        $this->assertModelExists($role);
    }

    public function test_unknown_permissions_are_rejected_and_admin_role_keeps_everything(): void
    {
        $admin = $this->admin();
        $adminRole = Role::where('slug', 'admin')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.roles.store'), [
            'name' => 'Hacker',
            'permissions' => ['everything.forever'],
        ])->assertSessionHasErrors('permissions.0');

        $this->actingAs($admin)->put(route('admin.roles.update', $adminRole), [
            'name' => 'Administrator',
            'permissions' => [],
        ])->assertRedirect();

        $this->assertSame(['*'], $adminRole->fresh()->permissions);
        $this->actingAs($admin)->delete(route('admin.roles.destroy', $adminRole))->assertSessionHasErrors('role');
    }

    public function test_the_last_administrator_cannot_be_demoted_or_deleted(): void
    {
        $admin = $this->admin();
        $other = User::factory()->admin()->create();

        $this->actingAs($admin)->delete(route('admin.users.destroy', $admin))->assertSessionHasErrors('user');

        $this->actingAs($admin)->delete(route('admin.users.destroy', $other))->assertRedirect();
        $this->assertModelMissing($other);

        $this->actingAs($admin)->put(route('admin.users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'role_id' => Role::where('slug', 'support')->value('id'),
        ])->assertSessionHasErrors('role_id');

        $this->assertTrue($admin->fresh()->hasRole('admin'));
    }

    public function test_docs_sync_downloads_changed_pages_from_github_and_records_the_run(): void
    {
        $original = File::get(Docs::path('installation'));

        try {
            Http::fake([
                'raw.githubusercontent.com/*/docs/installation.md' => Http::response("# Installation\n\nFresh content from GitHub.\n"),
                'raw.githubusercontent.com/*' => Http::response('Not Found', 404),
            ]);

            $admin = $this->admin();

            $this->actingAs($admin)->post(route('admin.docs.sync'))->assertRedirect()->assertSessionHas('status');

            $sync = DocSync::latest('id')->firstOrFail();
            $this->assertSame('partial', $sync->status);
            $this->assertSame(['installation'], $sync->changed);
            $this->assertSame($admin->id, $sync->user_id);
            $this->assertStringContainsString('Fresh content from GitHub.', File::get(Docs::path('installation')));

            Http::assertSent(fn ($request) => str_contains($request->url(), 'raw.githubusercontent.com/mdsazzad0002/dpanel/main/docs/installation.md'));
        } finally {
            File::put(Docs::path('installation'), $original);
        }
    }

    public function test_visitor_comment_is_moderated_and_answered_by_email(): void
    {
        Mail::fake();

        $this->post(route('docs.comments.store'), [
            'page' => 'installation',
            'name' => 'Tanvir',
            'email' => 'tanvir@example.com',
            'body' => 'Does the installer support Debian 12?',
        ])->assertRedirect();

        $comment = Comment::firstOrFail();
        $this->assertSame('pending', $comment->status);
        $this->get(route('docs.show', 'installation'))->assertDontSee('Does the installer support Debian 12?');

        $this->actingAs($this->admin())->post(route('admin.comments.reply', $comment), [
            'body' => 'Yes, Debian 12 is supported.',
            'notify' => true,
        ])->assertRedirect();

        $this->assertSame('approved', $comment->fresh()->status);
        Mail::assertSent(PanelMessage::class, fn ($mail) => $mail->hasTo('tanvir@example.com') && $mail->body === 'Yes, Debian 12 is supported.');
        $this->assertDatabaseHas('outbound_mails', ['to_email' => 'tanvir@example.com', 'status' => 'sent']);

        auth()->logout();
        $this->get(route('docs.show', 'installation'))
            ->assertSee('Does the installer support Debian 12?')
            ->assertSee('Yes, Debian 12 is supported.');
    }

    public function test_comment_honeypot_blocks_bots(): void
    {
        $this->post(route('docs.comments.store'), [
            'page' => 'installation',
            'name' => 'Bot',
            'email' => 'bot@example.com',
            'body' => 'Buy cheap things',
            'website' => 'spam.example',
        ])->assertSessionHasErrors('website');

        $this->assertDatabaseCount('comments', 0);
    }

    public function test_admin_publicly_responds_to_a_review(): void
    {
        Mail::fake();

        $review = Review::create([
            'name' => 'Nadia', 'email' => 'nadia@example.com', 'rating' => 5, 'title' => 'Great panel',
            'body' => 'Migrated three sites without trouble.', 'status' => 'approved', 'approved_at' => now(),
        ]);

        $this->actingAs($this->admin())->post(route('admin.reviews.reply', $review), [
            'reply' => 'Thanks Nadia, glad it went smoothly!',
            'notify' => true,
        ])->assertRedirect();

        $this->assertNotNull($review->fresh()->replied_at);
        Mail::assertSent(PanelMessage::class, fn ($mail) => $mail->hasTo('nadia@example.com'));

        $this->get(route('reviews.index'))->assertSee('Thanks Nadia, glad it went smoothly!');
    }

    public function test_donation_is_reported_verified_and_counted_towards_the_goal(): void
    {
        Mail::fake();

        $method = DonationMethod::create([
            'type' => 'bank', 'label' => 'City Bank', 'bank_name' => 'City Bank PLC',
            'account_name' => 'Md Sazzad', 'account_number' => '1234567890', 'is_active' => true,
        ]);

        $this->get(route('donate.index'))->assertOk()->assertSee('1234567890')->assertSee('Buy me a laptop');

        $this->post(route('donate.store'), [
            'name' => 'Karim',
            'email' => 'karim@example.com',
            'amount' => 5000,
            'donation_method_id' => $method->id,
            'transaction_id' => 'TRX-998877',
            'message' => 'Keep going!',
            'is_public' => '1',
        ])->assertRedirect();

        $donation = Donation::firstOrFail();
        $this->assertSame('pending', $donation->status);
        $this->assertSame('BDT', $donation->currency);

        $this->actingAs($this->admin())->patch(route('admin.donations.update', $donation), [
            'status' => 'verified',
            'notify' => true,
        ])->assertRedirect();

        Mail::assertSent(PanelMessage::class, fn ($mail) => $mail->hasTo('karim@example.com'));

        auth()->logout();
        $this->get(route('donate.index'))->assertSee('BDT 5,000')->assertSee('Karim')->assertSee('Keep going!');
    }

    public function test_donate_page_can_be_hidden(): void
    {
        Setting::put(['donation.enabled' => false]);

        $this->get(route('donate.index'))->assertNotFound();
        $this->get(route('home'))->assertDontSee(route('donate.index'));
        $this->get(route('donate.pc', array_key_first(config('site.pcs'))))->assertNotFound();
    }

    public function test_smtp_settings_override_the_env_mailer_and_keep_the_password_secret(): void
    {
        $this->actingAs($this->admin())->put(route('admin.settings.mail'), [
            'enabled' => true,
            'host' => 'smtp.example.com',
            'port' => 465,
            'encryption' => 'ssl',
            'username' => 'mailer@example.com',
            'password' => 'secret-pass',
            'from_address' => 'support@example.com',
            'from_name' => 'dPanel Support',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertNotSame('secret-pass', Setting::query()->find('mail.password')->value);
        $this->assertSame('secret-pass', Setting::get('mail.password'));

        MailSettings::apply();

        $this->assertSame('smtp', config('mail.default'));
        $this->assertSame('smtp.example.com', config('mail.mailers.smtp.host'));
        $this->assertSame('smtps', config('mail.mailers.smtp.scheme'));
        $this->assertSame(465, config('mail.mailers.smtp.port'));
        $this->assertSame('secret-pass', config('mail.mailers.smtp.password'));
        $this->assertSame('support@example.com', config('mail.from.address'));

        // Leaving the password blank keeps the saved one.
        $this->put(route('admin.settings.mail'), [
            'enabled' => true, 'host' => 'smtp.example.com', 'port' => 465, 'encryption' => 'ssl',
            'from_address' => 'support@example.com', 'from_name' => 'dPanel Support',
        ]);
        $this->assertSame('secret-pass', Setting::get('mail.password'));

        $this->get(route('admin.settings.edit'))->assertDontSee('secret-pass');
    }

    public function test_compose_sends_and_logs_email(): void
    {
        Mail::fake();

        $this->actingAs($this->admin())->post(route('admin.mail.store'), [
            'to_email' => 'client@example.com',
            'subject' => 'Your migration',
            'body' => 'Your sites were moved.',
        ])->assertRedirect();

        Mail::assertSent(PanelMessage::class, fn ($mail) => $mail->hasTo('client@example.com') && $mail->subjectLine === 'Your migration');
        $this->assertSame('sent', OutboundMail::firstOrFail()->status);
    }

    public function test_ai_drafts_a_ticket_reply_from_the_conversation(): void
    {
        $ticket = SupportTicket::create([
            'name' => 'Karim', 'email' => 'karim@example.com', 'category' => 'installation', 'priority' => 'high',
            'subject' => 'Installer stops at PHP step', 'message' => 'PHP-FPM fails to install.',
        ]);

        Setting::put(['ai.api_key' => 'sk-ant-test']);

        $this->mock(AiAssistant::class, function (MockInterface $mock) {
            $mock->shouldReceive('isEnabled')->andReturnTrue();
            $mock->shouldReceive('draftReply')
                ->withArgs(fn ($kind, $conversation, $guidance) => $kind === 'support ticket'
                    && str_contains($conversation, 'PHP-FPM fails to install.')
                    && $guidance === 'Ask for the OS')
                ->andReturn('Which OS are you using?');
        });

        $this->actingAs($this->admin())
            ->postJson(route('admin.ai.draft'), ['type' => 'ticket', 'id' => $ticket->id, 'guidance' => 'Ask for the OS'])
            ->assertOk()
            ->assertJson(['text' => 'Which OS are you using?']);
    }

    public function test_ai_draft_respects_permissions_and_setup(): void
    {
        $editor = User::factory()->role('editor')->create();

        $this->actingAs($editor)
            ->postJson(route('admin.ai.draft'), ['type' => 'mail', 'guidance' => 'hello'])
            ->assertForbidden();

        $this->actingAs($this->admin())
            ->postJson(route('admin.ai.draft'), ['type' => 'mail', 'guidance' => 'hello'])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'The AI assistant is turned off or has no API key. Set it up in Settings.');
    }

    public function test_helpdesk_admin_command_creates_an_administrator(): void
    {
        $this->artisan('helpdesk:admin', ['email' => 'boss@example.com'])->assertSuccessful();

        $this->assertTrue(User::where('email', 'boss@example.com')->firstOrFail()->hasRole('admin'));
    }
}
