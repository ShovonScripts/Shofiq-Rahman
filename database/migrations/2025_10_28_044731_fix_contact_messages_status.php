<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Fix status values
        DB::table('contact_messages')
            ->where('status', 'new')
            ->update(['status' => 'unread']);

        // Add missing columns if they don't exist
        Schema::table('contact_messages', function (Blueprint $table) {
            if (! Schema::hasColumn('contact_messages', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
            if (! Schema::hasColumn('contact_messages', 'ip_address')) {
                $table->string('ip_address')->nullable()->after('status');
            }
        });
    }

    public function down()
    {
        // Revert status values
        DB::table('contact_messages')
            ->where('status', 'unread')
            ->update(['status' => 'new']);
    }
};
