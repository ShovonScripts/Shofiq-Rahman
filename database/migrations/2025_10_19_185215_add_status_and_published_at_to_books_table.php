<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            // enum ব্যবহার করি (তোমার Model/Scope অনুযায়ী)
            if (!Schema::hasColumn('books', 'status')) {
                $table->enum('status', ['draft', 'published'])
                      ->default('draft')
                      ->after('cover'); // cover-এর পর রাখলে UI/DDL সুন্দর থাকে
            }

            if (!Schema::hasColumn('books', 'published_at')) {
                $table->timestamp('published_at')->nullable()->after('status');
            }

            // কম্পোজিট ইনডেক্স (নামটা এক্সপ্লিসিট দেই যাতে drop সহজ হয়)
            $table->index(['status', 'published_at'], 'books_status_published_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            // আগে ইনডেক্স ড্রপ, তারপর কলাম ড্রপ
            try {
                $table->dropIndex('books_status_published_at_index');
            } catch (\Throwable $e) {
                // ignore if missing
            }

            if (Schema::hasColumn('books', 'published_at')) {
                $table->dropColumn('published_at');
            }
            if (Schema::hasColumn('books', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};