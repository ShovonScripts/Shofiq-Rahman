<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_link_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_link_id')->constrained()->cascadeOnDelete();
            $table->string('ip', 45)->nullable();        // IPv4/IPv6
            $table->string('user_agent')->nullable();
            $table->string('referer')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->timestamps();

            $table->index(['book_id', 'book_link_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_link_clicks');
    }
};
