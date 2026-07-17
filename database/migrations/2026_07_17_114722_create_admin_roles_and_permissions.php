<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            // Super Admin is protected from deletion/rename since AdminMiddleWare
            // and the role-management screen itself depend on someone always
            // holding full access.
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // e.g. "trainees.manage"
            $table->string('module');        // e.g. "Trainees"
            $table->string('label');         // e.g. "Manage trainees"
            $table->timestamps();
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['role_id', 'permission_id']);
        });

        // Nullable: only meaningful for user_type=1 (admin-bucket) accounts.
        // A null role_id on an admin-type account is treated as Super Admin
        // by the permission middleware, so existing admins aren't locked out
        // the moment this ships.
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('user_type')->constrained('roles')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
