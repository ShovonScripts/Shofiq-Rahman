<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('author_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Author Name');
            $table->string('pen_name')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('location')->nullable();

            $table->string('email_public')->nullable();
            $table->string('website')->nullable();

            $table->text('bio_md')->nullable();                 // Markdown bio
            $table->text('achievements_summary_md')->nullable();// Optional long summary top of page

            $table->string('avatar_path')->nullable();
            $table->string('cover_path')->nullable();

            $table->json('social_links')->nullable();           // {facebook, twitter, youtube, instagram, ...}

            $table->boolean('is_active')->default(true);        // single site-wide author use-case
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('author_profiles');
    }
};
