<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_events', function (Blueprint $table) {
            $table->text('url')->nullable()->change();
            $table->text('useragent')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('email_events', function (Blueprint $table) {
            $table->string('url')->nullable()->change();
            $table->string('useragent')->nullable()->change();
        });
    }
};