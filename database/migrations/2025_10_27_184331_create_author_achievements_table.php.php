<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('author_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_profile_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('year')->nullable();         // keep as string to allow ranges like "2019–2021"
            $table->text('description_md')->nullable();
            $table->string('icon')->nullable();         // e.g. 'trophy', 'award' (for UI), optional
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('author_achievements');
    }
};
