<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Most indexes already exist from FK constraints and unique slugs.
     * Only 4 composite/single indexes were missing:
     * - posts.[user_id, created_at] — dashboard: user's posts ordered
     * - books.[user_id, created_at] — dashboard: user's books ordered
     * - comments.created_at — dashboard ordering
     * - comments.is_approved — approval filtering
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->index(['user_id', 'created_at']);
        });

        Schema::table('books', function (Blueprint $table) {
            $table->index(['user_id', 'created_at']);
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->index('created_at');
            $table->index('is_approved');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'created_at']);
        });

        Schema::table('books', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'created_at']);
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['is_approved']);
        });
    }
};
