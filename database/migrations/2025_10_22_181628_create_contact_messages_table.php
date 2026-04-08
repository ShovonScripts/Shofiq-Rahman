<?php

// database/migrations/2025_10_22_000000_create_contact_messages_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('email', 160);
            $table->string('subject', 180)->nullable();
            $table->text('message');
            $table->string('status', 20)->default('new'); // new|read|replied
            $table->timestamp('replied_at')->nullable();
            $table->string('reply_subject')->nullable();
            $table->longText('reply_body')->nullable();
            $table->timestamps();
            $table->index(['status','created_at']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('contact_messages');
    }
};
