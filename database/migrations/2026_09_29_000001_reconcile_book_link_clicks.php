<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasCanonicalKey = Schema::hasColumn('book_link_clicks', 'book_link_id');
        $hasLegacyKey = Schema::hasColumn('book_link_clicks', 'book_purchase_link_id');
        $hasLegacyLinks = Schema::hasTable('book_purchase_links');

        // Snapshot before importing legacy links: independently allocated IDs can collide.
        $originalLinks = DB::table('book_links')->get(['id', 'book_id', 'url'])->keyBy('id');

        foreach (Schema::getForeignKeys('book_link_clicks') as $foreign) {
            if (array_intersect($foreign['columns'], ['book_link_id', 'book_purchase_link_id'])) {
                Schema::table('book_link_clicks', function (Blueprint $table) use ($foreign) {
                    $table->dropForeign(DB::getDriverName() === 'sqlite' ? $foreign['columns'] : $foreign['name']);
                });
            }
        }

        Schema::table('book_link_clicks', function (Blueprint $table) use ($hasCanonicalKey, $hasLegacyKey) {
            $canonical = $table->unsignedBigInteger('book_link_id')->nullable();
            if ($hasCanonicalKey) {
                $canonical->change();
            }
            if ($hasLegacyKey) {
                // Retain the original identifier as evidence, but allow new canonical writes.
                $table->unsignedBigInteger('book_purchase_link_id')->nullable()->change();
            }
            $table->string('user_agent', 500)->nullable()->change();
            $table->string('referer', 1000)->nullable()->change();
        });

        Schema::table('book_links', function (Blueprint $table) {
            $table->string('url', 2048)->change();
        });

        if ($hasLegacyLinks && ! Schema::hasColumn('book_purchase_links', 'canonical_book_link_id')) {
            Schema::table('book_purchase_links', function (Blueprint $table) {
                $table->foreignId('canonical_book_link_id')->nullable()
                    ->constrained('book_links')->nullOnDelete();
            });
        }

        $unresolved = 0;
        DB::transaction(function () use ($hasLegacyLinks, $hasLegacyKey, $originalLinks, &$unresolved) {
            $legacyLinks = $hasLegacyLinks
                ? DB::table('book_purchase_links')->orderBy('id')->get()->keyBy('id')
                : collect();

            foreach ($legacyLinks as $legacy) {
                if (! DB::table('books')->where('id', $legacy->book_id)->exists()) {
                    continue;
                }

                $canonicalId = $legacy->canonical_book_link_id;
                if (! $canonicalId || ! DB::table('book_links')->where('id', $canonicalId)->where('book_id', $legacy->book_id)->exists()) {
                    $canonicalId = DB::table('book_links')->where('book_id', $legacy->book_id)
                        ->where('url', $legacy->url)->orderBy('id')->value('id');
                }
                if (! $canonicalId) {
                    $canonicalId = DB::table('book_links')->insertGetId([
                        'book_id' => $legacy->book_id,
                        'label' => $legacy->label,
                        'url' => $legacy->url,
                        'is_primary' => $legacy->is_primary && ! DB::table('book_links')
                            ->where('book_id', $legacy->book_id)->where('is_primary', true)->exists(),
                        'position' => $legacy->sort_order,
                        'sort_order' => $legacy->sort_order,
                        'created_at' => $legacy->created_at,
                        'updated_at' => $legacy->updated_at,
                    ]);
                }

                $legacy->canonical_book_link_id = $canonicalId;
                DB::table('book_purchase_links')->where('id', $legacy->id)
                    ->update(['canonical_book_link_id' => $canonicalId]);
            }

            if (! $hasLegacyKey) {
                return;
            }

            DB::table('book_link_clicks')->whereNull('book_link_id')
                ->whereNotNull('book_purchase_link_id')
                ->chunkById(500, function ($clicks) use ($originalLinks, $legacyLinks, &$unresolved) {
                    foreach ($clicks as $click) {
                        $legacy = $legacyLinks->get($click->book_purchase_link_id);
                        $original = $originalLinks->get($click->book_purchase_link_id);
                        $legacy = $legacy && (int) $legacy->book_id === (int) $click->book_id ? $legacy : null;
                        $original = $original && (int) $original->book_id === (int) $click->book_id ? $original : null;

                        // The old MySQL migration copied IDs between unrelated tables.
                        // If both interpretations are possible, retain the row for review
                        // rather than inventing an attribution and corrupting analytics.
                        $ambiguous = $legacy && $original && $legacy->url !== $original->url;
                        $canonicalId = $ambiguous ? null : ($original->id ?? $legacy->canonical_book_link_id ?? null);

                        if ($canonicalId) {
                            DB::table('book_link_clicks')->where('id', $click->id)
                                ->update(['book_link_id' => $canonicalId]);
                        } else {
                            $unresolved++;
                        }
                    }
                });
        });

        Schema::table('book_link_clicks', function (Blueprint $table) {
            // Deleting a seller link must not erase the book's historical click records.
            $table->foreign('book_link_id')->references('id')->on('book_links')->nullOnDelete();
        });
        if (! Schema::hasIndex('book_link_clicks', ['book_link_id'])) {
            Schema::table('book_link_clicks', fn (Blueprint $table) => $table->index('book_link_id'));
        }

        if ($unresolved > 0) {
            Log::warning('Legacy book-link clicks need manual attribution; no history was deleted.', [
                'unresolved_clicks' => $unresolved,
            ]);
        }
    }

    public function down(): void
    {
        // The two old ID spaces cannot be merged backwards without losing information.
        throw new RuntimeException('This reconciliation is forward-only. Restore a database backup to reverse it.');
    }
};
