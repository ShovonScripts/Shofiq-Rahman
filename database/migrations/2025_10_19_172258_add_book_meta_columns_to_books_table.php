<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            // আগে না থাকলে তবেই add করো
            if (!Schema::hasColumn('books', 'isbn')) {
                $table->string('isbn', 32)->nullable()->after('cover');
            }
            if (!Schema::hasColumn('books', 'language')) {
                $table->string('language', 16)->nullable()->after('isbn');
            }
            if (!Schema::hasColumn('books', 'pages')) {
                $table->unsignedInteger('pages')->nullable()->after('language');
            }
            if (!Schema::hasColumn('books', 'release_date')) {
                $table->date('release_date')->nullable()->after('pages');
            }
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            if (Schema::hasColumn('books', 'release_date')) {
                $table->dropColumn('release_date');
            }
            if (Schema::hasColumn('books', 'pages')) {
                $table->dropColumn('pages');
            }
            if (Schema::hasColumn('books', 'language')) {
                $table->dropColumn('language');
            }
            if (Schema::hasColumn('books', 'isbn')) {
                $table->dropColumn('isbn');
            }
        });
    }
};
