<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\BookLinkClick;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BookLinkMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_id_collisions_are_mapped_by_book_and_url_without_deleting_history(): void
    {
        $canonicalBook = Book::factory()->create();
        $legacyBook = Book::factory()->create();
        $original = $canonicalBook->purchaseLinks()->create(['label' => 'Original', 'url' => 'https://example.com/original']);
        $existingClick = BookLinkClick::create(['book_id' => $canonicalBook->id, 'book_link_id' => $original->id]);

        // Reproduce a legacy identifier that collides with a different canonical book.
        DB::table('book_purchase_links')->insert([
            'id' => $original->id, 'book_id' => $legacyBook->id, 'label' => 'Legacy seller',
            'url' => 'https://example.com/legacy', 'sort_order' => 2,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        Schema::table('book_link_clicks', fn (Blueprint $table) => $table->unsignedBigInteger('book_purchase_link_id')->nullable());
        $legacyClick = DB::table('book_link_clicks')->insertGetId([
            'book_id' => $legacyBook->id, 'book_purchase_link_id' => $original->id,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $migration = require database_path('migrations/2026_09_29_000001_reconcile_book_link_clicks.php');
        $migration->up();
        $mapped = DB::table('book_purchase_links')->where('id', $original->id)->value('canonical_book_link_id');
        $this->assertNotEquals($original->id, $mapped);
        $this->assertDatabaseHas('book_links', ['id' => $mapped, 'book_id' => $legacyBook->id, 'url' => 'https://example.com/legacy']);
        $this->assertDatabaseHas('book_link_clicks', ['id' => $legacyClick, 'book_link_id' => $mapped, 'book_purchase_link_id' => $original->id]);
        $this->assertSame($original->id, (int) $existingClick->fresh()->book_link_id);
        $this->assertDatabaseCount('book_link_clicks', 2);
        $this->assertDatabaseCount('book_purchase_links', 1);
    }

    public function test_ambiguous_and_orphaned_legacy_clicks_are_retained_without_false_attribution(): void
    {
        $book = Book::factory()->create();
        $link = $book->purchaseLinks()->create(['label' => 'Current', 'url' => 'https://example.com/current']);
        DB::table('book_purchase_links')->insert([
            'id' => $link->id, 'book_id' => $book->id, 'label' => 'Different legacy seller',
            'url' => 'https://example.com/different', 'sort_order' => 0,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        Schema::table('book_link_clicks', fn (Blueprint $table) => $table->unsignedBigInteger('book_purchase_link_id')->nullable());
        foreach ([$link->id, 999999] as $legacyId) {
            DB::table('book_link_clicks')->insert([
                'book_id' => $book->id, 'book_purchase_link_id' => $legacyId,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        $migration = require database_path('migrations/2026_09_29_000001_reconcile_book_link_clicks.php');
        $migration->up();
        $this->assertDatabaseCount('book_link_clicks', 2);
        $this->assertSame(2, DB::table('book_link_clicks')->whereNull('book_link_id')->count());
        $this->assertDatabaseHas('book_link_clicks', ['book_purchase_link_id' => 999999]);
        $this->assertModelExists($link);
    }

    public function test_a_legacy_only_click_table_is_reconciled_and_accepts_new_clicks(): void
    {
        $book = Book::factory()->create();
        DB::table('book_purchase_links')->insert([
            'id' => 700, 'book_id' => $book->id, 'label' => 'Legacy seller',
            'url' => 'https://example.com/legacy-only', 'sort_order' => 0,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        // Equivalent column/FK layout left by the old MySQL-only migration.
        Schema::drop('book_link_clicks');
        Schema::create('book_link_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_purchase_link_id')->constrained('book_purchase_links')->cascadeOnDelete();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('referer')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->timestamps();
        });
        $clickId = DB::table('book_link_clicks')->insertGetId([
            'book_id' => $book->id, 'book_purchase_link_id' => 700,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $migration = require database_path('migrations/2026_09_29_000001_reconcile_book_link_clicks.php');
        $migration->up();
        $linkId = DB::table('book_purchase_links')->where('id', 700)->value('canonical_book_link_id');
        $this->assertDatabaseHas('book_link_clicks', ['id' => $clickId, 'book_link_id' => $linkId, 'book_purchase_link_id' => 700]);
        BookLinkClick::create(['book_id' => $book->id, 'book_link_id' => $linkId]);
        $this->assertDatabaseCount('book_link_clicks', 2);
    }
}
