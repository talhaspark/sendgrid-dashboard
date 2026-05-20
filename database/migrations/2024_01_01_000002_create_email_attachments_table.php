<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_id')->constrained('emails')->cascadeOnDelete();
            $table->string('filename');
            $table->string('original_filename');
            $table->string('mime_type');
            $table->unsignedBigInteger('size'); // bytes
            $table->string('storage_path');
            $table->string('disk')->default('local');
            $table->string('content_id')->nullable(); // for inline images
            $table->string('checksum')->nullable(); // md5/sha256
            $table->boolean('is_inline')->default(false);
            $table->boolean('is_scanned')->default(false);
            $table->boolean('is_clean')->default(true);
            $table->string('scan_result')->nullable();
            $table->timestamps();

            $table->index('email_id');
            $table->index('mime_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_attachments');
    }
};
