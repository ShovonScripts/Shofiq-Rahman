<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1) নতুন কলাম যোগ (nullable) যাতে ব্যাকফিল করা যায়
        Schema::table('book_link_clicks', function (Blueprint $table) {
            if (!Schema::hasColumn('book_link_clicks', 'book_purchase_link_id')) {
                $table->unsignedBigInteger('book_purchase_link_id')
                      ->nullable()
                      ->after('book_id');
            }
        });

        // 2) পুরনো কলাম থাকলে সেখান থেকে ভ্যালু কপি (ব্যাকফিল)
        if (Schema::hasColumn('book_link_clicks', 'book_link_id')) {
            DB::statement("
                UPDATE `book_link_clicks`
                SET `book_purchase_link_id` = `book_link_id`
                WHERE `book_purchase_link_id` IS NULL
            ");
        }

        // 3) পুরনো FK/ইনডেক্স থাকলে ডায়নামিকভাবে ড্রপ (Schema::table এর বাইরে, যাতে ইমিডিয়েট try/catch কাজ করে)
        if (Schema::hasColumn('book_link_clicks', 'book_link_id')) {
            try {
                // পুরনো FK নাম বের করা
                $fk = DB::selectOne("
                    SELECT CONSTRAINT_NAME
                    FROM information_schema.KEY_COLUMN_USAGE
                    WHERE TABLE_SCHEMA = DATABASE()
                      AND TABLE_NAME = 'book_link_clicks'
                      AND COLUMN_NAME = 'book_link_id'
                      AND REFERENCED_TABLE_NAME IS NOT NULL
                    LIMIT 1
                ");
                if ($fk && !empty($fk->CONSTRAINT_NAME)) {
                    DB::statement('ALTER TABLE `book_link_clicks` DROP FOREIGN KEY `'.$fk->CONSTRAINT_NAME.'`');
                }

                // (ঐচ্ছিক) পুরনো নন-প্রাইমারি ইনডেক্স থাকলে ড্রপ
                $idx = DB::selectOne("
                    SELECT INDEX_NAME
                    FROM information_schema.STATISTICS
                    WHERE TABLE_SCHEMA = DATABASE()
                      AND TABLE_NAME = 'book_link_clicks'
                      AND COLUMN_NAME = 'book_link_id'
                      AND INDEX_NAME <> 'PRIMARY'
                    LIMIT 1
                ");
                if ($idx && !empty($idx->INDEX_NAME)) {
                    DB::statement('ALTER TABLE `book_link_clicks` DROP INDEX `'.$idx->INDEX_NAME.'`');
                }
            } catch (\Throwable $e) {
                // ইচ্ছা করলে লগ করতে পারো
                // \Log::warning('Skipping old FK/Index drop', ['error' => $e->getMessage()]);
            }
        }

        // 4) নতুন ইনডেক্স + FK যোগ করা (আগে থেকে থাকলে স্কিপ)
        // 4.a) ইনডেক্স চেক
        $hasIndex = DB::selectOne("
            SELECT 1 AS ok
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'book_link_clicks'
              AND COLUMN_NAME = 'book_purchase_link_id'
              AND INDEX_NAME <> 'PRIMARY'
            LIMIT 1
        ");
        if (!$hasIndex) {
            try { DB::statement('ALTER TABLE `book_link_clicks` ADD INDEX (`book_purchase_link_id`)'); } catch (\Throwable $e) {}
        }

        // 4.b) FK চেক
        $hasNewFk = DB::selectOne("
            SELECT 1 AS ok
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'book_link_clicks'
              AND COLUMN_NAME = 'book_purchase_link_id'
              AND REFERENCED_TABLE_NAME = 'book_purchase_links'
            LIMIT 1
        ");
        if (!$hasNewFk) {
            try {
                Schema::table('book_link_clicks', function (Blueprint $table) {
                    $table->foreign('book_purchase_link_id')
                          ->references('id')->on('book_purchase_links')
                          ->onDelete('cascade');
                });
            } catch (\Throwable $e) {
                // যদি এখানে নাম-ডুপ্লিকেট ইত্যাদি লাগে, উপরের চেক সাধারণত রক্ষা করবে
            }
        }

        // 5) NOT NULL করা (DBAL না থাকলেও কাজ করবে)
        try {
            Schema::table('book_link_clicks', function (Blueprint $table) {
                $table->unsignedBigInteger('book_purchase_link_id')->nullable(false)->change();
            });
        } catch (\Throwable $e) {
            DB::statement('ALTER TABLE `book_link_clicks` MODIFY `book_purchase_link_id` BIGINT UNSIGNED NOT NULL');
        }

        // 6) পুরনো কলাম ড্রপ (থাকলে)
        Schema::table('book_link_clicks', function (Blueprint $table) {
            if (Schema::hasColumn('book_link_clicks', 'book_link_id')) {
                $table->dropColumn('book_link_id');
            }
        });
    }

    public function down(): void
    {
        // 1) পুরনো কলাম ফিরিয়ে আনা (nullable)
        Schema::table('book_link_clicks', function (Blueprint $table) {
            if (!Schema::hasColumn('book_link_clicks', 'book_link_id')) {
                $table->unsignedBigInteger('book_link_id')->nullable()->after('book_id');
            }
        });

        // 2) ব্যাকফিল: নতুন কলাম থেকে পুরনোতে কপি
        if (Schema::hasColumn('book_link_clicks', 'book_purchase_link_id')) {
            DB::statement("
                UPDATE `book_link_clicks`
                SET `book_link_id` = `book_purchase_link_id`
                WHERE `book_link_id` IS NULL
            ");
        }

        // 3) নতুন FK/ইনডেক্স ড্রপ (থাকলে)
        try {
            // নতুন FK নাম বের করা
            $fk = DB::selectOne("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'book_link_clicks'
                  AND COLUMN_NAME = 'book_purchase_link_id'
                  AND REFERENCED_TABLE_NAME = 'book_purchase_links'
                LIMIT 1
            ");
            if ($fk && !empty($fk->CONSTRAINT_NAME)) {
                DB::statement('ALTER TABLE `book_link_clicks` DROP FOREIGN KEY `'.$fk->CONSTRAINT_NAME.'`');
            }

            // নতুন ইনডেক্স ড্রপ (থাকলে)
            $idx = DB::selectOne("
                SELECT INDEX_NAME
                FROM information_schema.STATISTICS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'book_link_clicks'
                  AND COLUMN_NAME = 'book_purchase_link_id'
                  AND INDEX_NAME <> 'PRIMARY'
                LIMIT 1
            ");
            if ($idx && !empty($idx->INDEX_NAME)) {
                DB::statement('ALTER TABLE `book_link_clicks` DROP INDEX `'.$idx->INDEX_NAME.'`');
            }
        } catch (\Throwable $e) {
            // স্কিপ
        }

        // 4) নতুন কলাম ড্রপ
        Schema::table('book_link_clicks', function (Blueprint $table) {
            if (Schema::hasColumn('book_link_clicks', 'book_purchase_link_id')) {
                $table->dropColumn('book_purchase_link_id');
            }
        });

        // 5) পুরনো FK ফিরিয়ে আনা
        try {
            Schema::table('book_link_clicks', function (Blueprint $table) {
                $table->foreign('book_link_id')
                      ->references('id')->on('book_links')
                      ->onDelete('cascade');
            });
        } catch (\Throwable $e) {
            // স্কিপ
        }
    }
};
