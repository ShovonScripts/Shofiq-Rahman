<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();

            // ইউজার সম্পর্ক
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Polymorphic relation (Post / Book)
            $table->morphs('commentable'); // commentable_id, commentable_type

            // কমেন্টের মূল টেক্সট
            $table->text('body');

            // Moderation: approved বা pending
            $table->boolean('is_approved')->default(true);

            // Optional: Soft delete (ভবিষ্যতে moderation বা trash ব্যবহারে সুবিধা)
            $table->softDeletes();

            // Timestamp
            $table->timestamps();

            // Indexes for faster queries
            $table->index(['commentable_type', 'commentable_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
