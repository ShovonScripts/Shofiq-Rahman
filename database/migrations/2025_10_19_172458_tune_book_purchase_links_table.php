<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('book_purchase_links', function (Blueprint $table) {
            $table->string('url', 2048)->change();

            // স্পষ্ট নাম দাও
            $table->index(['book_id', 'sort_order'], 'book_purchase_links_book_id_sort_order_index');
            $table->index(['book_id', 'is_primary'], 'book_purchase_links_book_id_is_primary_index');
        });
    }

    public function down(): void
    {
        Schema::table('book_purchase_links', function (Blueprint $table) {
            $table->string('url', 255)->change();

            // নাম দিয়ে ড্রপ
            $table->dropIndex('book_purchase_links_book_id_sort_order_index');
            $table->dropIndex('book_purchase_links_book_id_is_primary_index');
        });
    }
};
