<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('comments', function (Blueprint $table) {
            // শুধুমাত্র তখনই যোগ করবে যদি deleted_at না থাকে
            if (!Schema::hasColumn('comments', 'deleted_at')) {
                $table->softDeletes(); // adds deleted_at column
            }
        });
    }

    public function down(): void {
        Schema::table('comments', function (Blueprint $table) {
            if (Schema::hasColumn('comments', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
