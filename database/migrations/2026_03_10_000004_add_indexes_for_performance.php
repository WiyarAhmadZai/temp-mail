<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('emails', function (Blueprint $table) {
            $table->index('expires_at');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->index('email_id');
        });
    }

    public function down(): void
    {
        Schema::table('emails', function (Blueprint $table) {
            $table->dropIndex(['expires_at']);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['email_id']);
        });
    }
};
