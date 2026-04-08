<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // author/owner
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();   // Markdown বা plain
            $table->string('cover')->nullable();       // storage path
            $table->string('isbn')->nullable();
            $table->string('language', 10)->nullable(); // e.g., 'bn', 'en'
            $table->unsignedInteger('pages')->nullable();
            $table->date('release_date')->nullable();
            $table->json('meta_json')->nullable();     // SEO meta {title, description}
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('books');
    }
};
