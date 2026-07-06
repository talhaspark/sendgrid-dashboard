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
        Schema::table('sent_emails', function (Blueprint $table) {

            $table->text('failure_reason')
                ->nullable()
                ->after('status');

            $table->text('failure_response')
                ->nullable()
                ->after('failure_reason');

        });
    }


    public function down(): void
    {
        Schema::table('sent_emails', function (Blueprint $table) {

            $table->dropColumn([
                'failure_reason',
                'failure_response'
            ]);

        });
    }
};
