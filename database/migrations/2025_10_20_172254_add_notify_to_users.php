<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // যদি কলাম না থাকে, তাহলে যোগ করো
            if (!Schema::hasColumn('users', 'notify_new_comments')) {
                $table->boolean('notify_new_comments')->default(true)->after('email');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // কলাম থাকলে drop করো
            if (Schema::hasColumn('users', 'notify_new_comments')) {
                $table->dropColumn('notify_new_comments');
            }
        });
    }
};
