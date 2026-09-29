<?php

namespace Tests\Feature;

use App\Models\AuthorProfile;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AuthorManagementRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_achievement_lists_and_editors_receive_matching_props_and_accept_numeric_years(): void
    {
        $this->actingAs($this->authorUser());
        $this->post(route('dashboard.about.achievements.store'), [
            'title' => 'Literary award', 'year' => 2026, 'sort_order' => 0,
        ])->assertSessionHasNoErrors()->assertRedirect();
        $achievement = AuthorProfile::firstOrFail()->achievements()->firstOrFail();

        $this->get(route('dashboard.about.achievements.index'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->has('achievements', 1)->where('achievements.0.title', 'Literary award')
        );
        $this->get(route('dashboard.about.achievements.edit', $achievement))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('achievement.id', $achievement->id)->where('achievement.title', 'Literary award')
        );
    }

    public function test_event_update_injects_a_concrete_request_and_accepts_multipart_images(): void
    {
        Storage::fake('public');
        $this->actingAs($this->authorUser());
        $profile = AuthorProfile::create(['name' => 'Writer']);
        $event = $profile->events()->create(['headline' => 'Original event', 'happened_at' => '2025-01-02', 'is_published' => true]);

        $this->get(route('dashboard.about.events.index'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->has('events', 1)->where('events.0.id', $event->id)
        );
        $this->get(route('dashboard.about.events.edit', $event))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('event.headline', 'Original event')->where('event.happened_at', '2025-01-02')
        );
        $this->post(route('dashboard.about.events.update', $event), [
            '_method' => 'put', 'headline' => 'Updated event', 'happened_at' => '2026-09-29',
            'image' => UploadedFile::fake()->image('event.png'), 'is_published' => true,
        ])->assertSessionHasNoErrors()->assertRedirect(route('dashboard.about.events.index'));
        $this->assertSame('Updated event', $event->fresh()->headline);
        Storage::disk('public')->assertExists($event->fresh()->image_path);

        $this->put(route('dashboard.about.events.update', $event), ['headline' => ''])
            ->assertSessionHasErrors('headline');
    }

    public function test_author_profile_date_is_formatted_for_a_date_input(): void
    {
        AuthorProfile::create(['name' => 'Writer', 'date_of_birth' => '1990-03-12']);
        $this->actingAs($this->authorUser())->get(route('dashboard.about.edit'))->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('profile.date_of_birth', '1990-03-12'));
    }

    public function test_seo_analysis_works_with_sqlite_and_unicode_content(): void
    {
        Post::factory()->published()->create(['body' => str_repeat('ক', 299)]);
        Post::factory()->published()->create(['body' => str_repeat('ক', 300)]);
        $this->actingAs($this->adminUser())->get(route('dashboard.seo.analyze'))->assertOk()
            ->assertInertia(fn (Assert $page) => $page->has('shortPosts', 1));
    }
}
