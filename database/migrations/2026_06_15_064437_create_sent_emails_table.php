<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sent_emails', function (Blueprint $table) {
            $table->id();
            $table->string('sg_message_id')->unique();

            $table->string('from_email')->nullable();

            $table->string('to_email')->nullable();

            $table->string('subject')->nullable();

            $table->string('status')->nullable();

            $table->timestamp('sent_at')->nullable();

            $table->integer('opens')->default(0);

            $table->integer('clicks')->default(0);

            $table->integer('bounces')->default(0);

            $table->integer('blocks')->default(0);

            $table->integer('deferred')->default(0);

            $table->integer('spam_reports')->default(0);

            $table->integer('unsubscribes')->default(0);

            $table->json('categories')->nullable();

            $table->json('custom_args')->nullable();

            $table->json('raw_payload')->nullable();
            $table->timestamps();
            
            $table->index('sent_at');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sent_emails');
    }
};
