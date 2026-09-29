<?php

namespace Tests\Feature;

use App\Models\Review;
use App\Support\Docs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_static_pages_render(): void
    {
        foreach (['/', '/docs', '/support', '/reviews', '/privacy', '/terms'] as $uri) {
            $this->get($uri)->assertOk()->assertSee('<link rel="canonical"', false);
        }
    }

    public function test_every_configured_doc_page_renders_with_seo_metadata(): void
    {
        $this->assertNotEmpty(Docs::all());

        foreach (Docs::all() as $page) {
            $this->get(route('docs.show', $page['slug']))
                ->assertOk()
                ->assertSee('<title>'.e($page['title']).' — dPanel Documentation</title>', false)
                ->assertSee('"@type":"TechArticle"', false)
                ->assertSee('"@type":"BreadcrumbList"', false);
        }
    }

    public function test_repository_links_are_rewritten_to_site_pages(): void
    {
        $this->get(route('docs.show', 'installation'))
            ->assertOk()
            ->assertSee('href="/docs/operations"', false)
            ->assertDontSee('operations.md', false)
            ->assertSee('id="install-dpanel"', false);
    }

    public function test_unknown_doc_returns_404(): void
    {
        $this->get('/docs/does-not-exist')->assertNotFound()->assertSee('Page not found');
    }

    public function test_search_finds_pages(): void
    {
        $this->get(route('docs.search', ['q' => 'installer']))
            ->assertOk()
            ->assertSee('Installation')
            ->assertSee('noindex', false);
    }

    public function test_sitemap_and_robots(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee(route('docs.show', 'installation'), false)
            ->assertSee(route('reviews.index'), false);

        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Disallow: /admin')
            ->assertSee('Sitemap: '.route('sitemap'));
    }

    public function test_version_management_and_registration_are_gone(): void
    {
        $this->get('/api/v1/versions/dpanel')->assertNotFound();
        $this->get('/documentation')->assertNotFound();
        $this->get('/register')->assertNotFound();
    }

    public function test_review_is_held_for_moderation(): void
    {
        $this->post(route('reviews.store'), [
            'name' => 'Rahim',
            'email' => 'rahim@example.com',
            'rating' => 5,
            'title' => 'Great panel',
            'body' => 'Installed on Ubuntu in ten minutes and it just works.',
        ])->assertRedirect();

        $this->assertDatabaseHas('reviews', ['email' => 'rahim@example.com', 'status' => 'pending']);
        $this->get(route('reviews.index'))->assertDontSee('Great panel');

        Review::first()->update(['status' => 'approved', 'approved_at' => now()]);

        $this->get(route('reviews.index'))
            ->assertSee('Great panel')
            ->assertDontSee('rahim@example.com')
            ->assertSee('"@type":"AggregateRating"', false);
    }

    public function test_honeypot_blocks_bots(): void
    {
        $this->post(route('reviews.store'), [
            'name' => 'Bot',
            'email' => 'bot@example.com',
            'rating' => 5,
            'title' => 'Spam',
            'body' => 'Buy cheap things at my website right now please.',
            'website' => 'http://spam.example',
        ])->assertSessionHasErrors('website');

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_page_feedback_is_recorded(): void
    {
        $this->postJson(route('docs.feedback'), ['page' => 'installation', 'helpful' => 0, 'comment' => 'Add Debian steps'])
            ->assertOk();

        $this->assertDatabaseHas('page_feedback', ['page' => 'installation', 'helpful' => false, 'comment' => 'Add Debian steps']);

        $this->postJson(route('docs.feedback'), ['page' => 'nope', 'helpful' => 1])->assertUnprocessable();
    }
}
