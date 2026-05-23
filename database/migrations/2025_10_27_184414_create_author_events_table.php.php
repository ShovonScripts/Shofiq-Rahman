<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('author_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_profile_id')->constrained()->cascadeOnDelete();
            $table->date('happened_at')->nullable();
            $table->string('headline');
            $table->text('description_md')->nullable();
            $table->string('image_path')->nullable();
            $table->string('location')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('author_events');
    }
};
