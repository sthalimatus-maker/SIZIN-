<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('notification_logs', 'channel')) {
            Schema::table('notification_logs', function (Blueprint $table) {
                $table->string('channel')->default('whatsapp')->after('leave_request_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('notification_logs', 'channel')) {
            Schema::table('notification_logs', function (Blueprint $table) {
                $table->dropColumn('channel');
            });
        }
    }
};
