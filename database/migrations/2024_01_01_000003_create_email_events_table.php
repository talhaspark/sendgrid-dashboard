<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_id')->nullable()->constrained('emails')->nullOnDelete();
            $table->string('sg_message_id')->nullable(); // SendGrid message ID
            $table->string('event_type'); // processed, delivered, open, click, bounce, dropped, deferred, spam_report, unsubscribe
            $table->string('email_address')->nullable();
            $table->timestamp('event_timestamp');
            $table->string('smtp_id')->nullable();
            $table->string('category')->nullable();
            $table->string('sg_event_id')->nullable();
            $table->text('reason')->nullable();
            $table->string('status')->nullable();
            $table->text('response')->nullable();
            $table->integer('attempt')->nullable();
            $table->string('url')->nullable(); // for click events
            $table->string('useragent')->nullable();
            $table->string('ip')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->index('email_id');
            $table->index('event_type');
            $table->index('event_timestamp');
            $table->index('sg_message_id');
            $table->index('email_address');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_events');
    }
};
