<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('designation_options', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80)->unique();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Seed the default options
        DB::table('designation_options')->insert([
            ['name' => 'Court of Examiner', 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Panel Head',         'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Other',              'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('designation_options');
    }
};
