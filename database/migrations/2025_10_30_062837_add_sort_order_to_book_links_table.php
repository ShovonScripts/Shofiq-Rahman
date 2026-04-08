<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('book_links', function (Blueprint $table) {
            // ✅ যদি sort_order কলাম না থাকে তাহলে তৈরি করো
            if (!Schema::hasColumn('book_links', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('is_primary');
            }
        });

        // ✅ পুরনো প্রোজেক্টে যদি 'position' কলাম থাকে, সেটা থেকে ডাটা কপি করো
        if (Schema::hasColumn('book_links', 'position')) {
            DB::statement('
                UPDATE book_links
                SET sort_order = position
                WHERE (sort_order = 0 OR sort_order IS NULL)
                  AND position IS NOT NULL
            ');
        }
    }

    public function down(): void
    {
        Schema::table('book_links', function (Blueprint $table) {
            // ✅ rollback করলে sort_order ডিলিট করবে
            if (Schema::hasColumn('book_links', 'sort_order')) {
                $table->dropColumn('sort_order');
            }
        });
    }
};
