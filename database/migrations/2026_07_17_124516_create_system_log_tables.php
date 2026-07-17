<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('login_logs', function (Blueprint $table) {
            $table->id();
            // users.id is a legacy "int unsigned" column, not Laravel's
            // default bigint — foreignId() would mismatch types.
            $table->unsignedInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->string('name')->nullable();   // snapshot — survives the user being deleted later
            $table->string('email')->nullable();
            $table->integer('role_type')->nullable();
            $table->string('ip_address', 64)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('logged_in_at');
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->string('user_name')->nullable();
            $table->string('action', 20); // created | updated | deleted
            $table->string('model_type');
            $table->unsignedBigInteger('model_id')->nullable();
            $table->text('summary')->nullable();  // short human-readable description
            $table->json('changes')->nullable();  // {field: [old, new]} for updates
            $table->timestamps();
            $table->index(['model_type', 'model_id']);
        });

        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->string('to_address');
            $table->string('subject')->nullable();
            $table->string('mailable')->nullable();
            $table->timestamp('sent_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('login_logs');
    }
};
