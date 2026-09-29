<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\BookLinkClick;
use App\Models\Category;
use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class BookEditingRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_edit_loads_click_counts_and_saving_keeps_link_ids_and_history(): void
    {
        $author = $this->authorUser();
        $book = Book::factory()->published()->create(['user_id' => $author->id]);
        $link = $book->purchaseLinks()->create(['label' => 'Seller', 'url' => 'https://example.com/book', 'is_primary' => true]);
        $click = BookLinkClick::create(['book_id' => $book->id, 'book_link_id' => $link->id]);

        $this->actingAs($author)->get(route('dashboard.books.edit', $book))
            ->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('book.links.0.id', $link->id)->where('book.links.0.click_count', 1)
            );
        $this->put(route('dashboard.books.update', $book), [
            'title' => 'Updated book', 'status' => 'published',
            'links' => [['id' => $link->id, 'label' => 'Updated seller', 'url' => $link->url, 'is_primary' => true]],
        ])->assertSessionHasNoErrors()->assertRedirect(route('dashboard.books.edit', $book));

        $this->assertSame('Updated seller', $link->fresh()->label);
        $this->assertSame(1, $book->purchaseLinks()->count());
        $this->assertSame($link->id, (int) $click->fresh()->book_link_id);
        $this->assertSame(1, $link->clicks()->count());
    }

    public function test_omitted_book_metadata_links_and_taxonomies_are_preserved(): void
    {
        $author = $this->authorUser();
        $book = Book::factory()->published()->create([
            'user_id' => $author->id, 'isbn' => '9780123456789', 'language' => 'bn',
            'pages' => 220, 'release_date' => '2025-01-01',
        ]);
        $link = $book->purchaseLinks()->create(['label' => 'Seller', 'url' => 'https://example.com/book']);
        $book->categories()->attach(Category::create(['name' => 'Essays', 'slug' => 'essays']));
        $book->genres()->attach(Genre::create(['name' => 'Fiction', 'slug' => 'fiction']));
        $publishedAt = $book->published_at->toDateTimeString();

        $this->actingAs($author)->put(route('dashboard.books.update', $book), [
            'title' => 'Edited title', 'status' => 'published',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $fresh = $book->fresh();
        $this->assertSame('9780123456789', $fresh->isbn);
        $this->assertSame('bn', $fresh->language);
        $this->assertSame(220, $fresh->pages);
        $this->assertSame('2025-01-01', $fresh->release_date->toDateString());
        $this->assertSame($publishedAt, $fresh->published_at->toDateTimeString());
        $this->assertSame(1, $fresh->categories()->count());
        $this->assertSame(1, $fresh->genres()->count());
        $this->assertModelExists($link);

        $this->put(route('dashboard.books.update', $book), [
            'title' => 'Edited title', 'status' => 'published', 'isbn' => null, 'pages' => null,
        ])->assertSessionHasNoErrors();
        $this->assertNull($book->fresh()->isbn);
        $this->assertNull($book->fresh()->pages);
    }

    public function test_update_normalizes_and_validates_custom_slugs_before_saving(): void
    {
        $author = $this->authorUser();
        $book = Book::factory()->draft()->create(['user_id' => $author->id]);
        Book::factory()->create(['slug' => 'taken-slug']);
        $this->actingAs($author)->put(route('dashboard.books.update', $book), [
            'title' => $book->title, 'status' => 'draft', 'slug' => 'Taken Slug',
        ])->assertSessionHasErrors('slug');
        $this->put(route('dashboard.books.update', $book), [
            'title' => $book->title, 'status' => 'draft', 'slug' => 'Available Slug',
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('available-slug', $book->fresh()->slug);
    }

    public function test_foreign_link_ids_and_invalid_urls_cannot_replace_existing_links(): void
    {
        $author = $this->authorUser();
        $book = Book::factory()->draft()->create(['user_id' => $author->id]);
        $own = $book->purchaseLinks()->create(['label' => 'Own seller', 'url' => 'https://example.com/own']);
        $foreign = Book::factory()->create()->purchaseLinks()->create(['label' => 'Other seller', 'url' => 'https://example.com/other']);
        $this->actingAs($author)->put(route('dashboard.books.update', $book), [
            'title' => $book->title, 'status' => 'draft',
            'links' => [['id' => $foreign->id, 'label' => 'Attack', 'url' => $foreign->url]],
        ])->assertSessionHasErrors('links.0.id');
        $this->put(route('dashboard.books.update', $book), [
            'title' => 'Must roll back', 'status' => 'draft',
            'links' => [['id' => $own->id, 'label' => 'Bad URL', 'url' => 'javascript:alert(1)']],
        ])->assertSessionHasErrors('links.0.url');
        $this->assertSame($book->title, $book->fresh()->title);
        $this->assertSame('Own seller', $own->fresh()->label);
        $this->assertSame('Other seller', $foreign->fresh()->label);
    }

    public function test_clearing_links_retains_historical_click_rows(): void
    {
        $author = $this->authorUser();
        $book = Book::factory()->draft()->create(['user_id' => $author->id]);
        $link = $book->purchaseLinks()->create(['label' => 'Seller', 'url' => 'https://example.com/book']);
        $click = BookLinkClick::create(['book_id' => $book->id, 'book_link_id' => $link->id]);
        $this->actingAs($author)->put(route('dashboard.books.update', $book), [
            'title' => $book->title, 'status' => 'draft', 'links' => null,
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertModelMissing($link);
        $this->assertModelExists($click);
        $this->assertNull($click->fresh()->book_link_id);
        $this->assertSame(1, $book->clicks()->count());
    }

    public function test_public_purchase_redirect_logs_to_the_canonical_foreign_key(): void
    {
        $book = Book::factory()->published()->create();
        $link = $book->purchaseLinks()->create(['label' => 'Seller', 'url' => 'https://example.com/book']);
        $this->withHeaders(['User-Agent' => str_repeat('a', 400), 'Referer' => 'https://example.com/'.str_repeat('r', 500)])
            ->get(route('books.link.go', $link))->assertRedirect($link->url);
        $this->assertDatabaseHas('book_link_clicks', ['book_id' => $book->id, 'book_link_id' => $link->id]);
        $this->assertSame(1, $link->clicks()->count());
    }
}
