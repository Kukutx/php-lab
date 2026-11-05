<?php

namespace Tests\Feature;

use App\Enums\NoticeCategory;
use App\Models\Notice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoticeTest extends TestCase
{
    // Every test runs in a fresh in-memory SQLite database (see phpunit.xml).
    use RefreshDatabase;

    public function test_index_lists_notices(): void
    {
        Notice::factory()->create(['title' => 'Road closure in Via Roma']);

        $this->get('/notices')
            ->assertOk()
            ->assertSee('Road closure in Via Roma');
    }

    public function test_index_filters_by_category(): void
    {
        Notice::factory()->create(['title' => 'Summer festival', 'category' => NoticeCategory::Events]);
        Notice::factory()->create(['title' => 'Pipe works', 'category' => NoticeCategory::Roadworks]);

        $this->get('/notices?category=events')
            ->assertSee('Summer festival')
            ->assertDontSee('Pipe works');
    }

    public function test_a_notice_can_be_created(): void
    {
        $response = $this->post('/notices', [
            'title' => 'New opening hours',
            'body' => 'The registry office opens at 8:30 from next Monday.',
            'category' => 'services',
            'published_at' => '2026-11-01 09:00',
        ]);

        $notice = Notice::firstOrFail();
        $response->assertRedirect(route('notices.show', $notice));
        $this->assertSame('new-opening-hours', $notice->slug);
        $this->assertSame(NoticeCategory::Services, $notice->category);
    }

    public function test_validation_errors_are_returned(): void
    {
        $this->from('/notices/create')
            ->post('/notices', ['title' => 'x', 'body' => '', 'category' => 'aliens'])
            ->assertRedirect('/notices/create')
            ->assertSessionHasErrors(['title', 'body', 'category']);

        $this->assertDatabaseCount('notices', 0);
    }

    public function test_a_notice_can_be_updated_and_deleted(): void
    {
        $notice = Notice::factory()->create();

        $this->put(route('notices.update', $notice), [
            'title' => 'Updated title here',
            'body' => 'Updated body text that is long enough.',
            'category' => 'emergency',
        ])->assertRedirect();

        $this->assertSame('Updated title here', $notice->fresh()->title);

        $this->delete(route('notices.destroy', $notice))->assertRedirect(route('notices.index'));
        $this->assertModelMissing($notice);
    }

    public function test_slugs_are_unique(): void
    {
        $a = Notice::factory()->create(['title' => 'Same title']);
        $b = Notice::factory()->create(['title' => 'Same title']);

        $this->assertSame('same-title', $a->slug);
        $this->assertSame('same-title-2', $b->slug);
    }

    public function test_output_is_escaped(): void
    {
        $notice = Notice::factory()->create(['body' => '<script>alert("xss")</script> and more text']);

        $this->get(route('notices.show', $notice))
            ->assertDontSee('<script>alert("xss")</script>', false)
            ->assertSee('&lt;script&gt;', false);
    }

    public function test_published_scope_hides_drafts(): void
    {
        Notice::factory()->create();
        Notice::factory()->draft()->create();

        $this->assertSame(1, Notice::published()->count());
    }

    public function test_index_hides_drafts_unless_asked(): void
    {
        Notice::factory()->create(['title' => 'Live notice']);
        Notice::factory()->draft()->create(['title' => 'Secret draft']);

        $this->get(route('notices.index'))->assertSee('Live notice')->assertDontSee('Secret draft');
        $this->get(route('notices.index', ['status' => 'drafts']))->assertSee('Secret draft')->assertDontSee('Live notice');
        $this->get(route('notices.index', ['status' => 'all']))->assertSee('Secret draft')->assertSee('Live notice');
    }
}
