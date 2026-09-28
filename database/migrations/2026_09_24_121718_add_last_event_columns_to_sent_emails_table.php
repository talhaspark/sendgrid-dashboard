<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sent_emails', function (Blueprint $table) {
            $table->string('last_event')->nullable()->after('status');
            $table->timestamp('last_event_at')->nullable()->after('last_event');

            $table->index('last_event');
            $table->index('last_event_at');
        });
    }

    public function down(): void
    {
        Schema::table('sent_emails', function (Blueprint $table) {
            $table->dropIndex(['last_event']);
            $table->dropIndex(['last_event_at']);
            $table->dropColumn(['last_event', 'last_event_at']);
        });
    }
};