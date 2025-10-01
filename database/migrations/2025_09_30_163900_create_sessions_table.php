<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->bigIncrements('session_id');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('refresh_token');
            $table->dateTime('expires_at');
            $table->string('user_agent', 255)->nullable();
            $table->string('ip_address', 100)->nullable();
            $table->dateTime('revoked_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
