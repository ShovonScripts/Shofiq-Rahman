<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('book_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->string('label');              // e.g., "Rokomari", "Amazon", "Publisher"
            $table->string('url');                // external product link
            $table->string('price_label')->nullable(); // e.g., "৳350", "$9.99"
            $table->boolean('is_primary')->default(false);
            $table->unsignedInteger('position')->default(0); // for sorting
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('book_links');
    }
};
