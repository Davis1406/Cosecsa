<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_types', function (Blueprint $table) {
            $table->id();
            $table->string('fee_group'); // Fellowship Registration, Annual Subscription, Graduation, Gown, Transcript, Other
            $table->string('name');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('USD');
            // Annual Subscription fee types route into fellow_subscriptions instead of fee_payments.
            $table->boolean('applies_to_subscription')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('fee_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_type_id')->nullable()->constrained('fee_types')->nullOnDelete();
            $table->string('fee_group');   // snapshot, survives fee_type edits/deletes
            $table->string('fee_name');    // snapshot
            $table->enum('payer_type', ['fellow', 'trainee', 'candidate']);
            $table->unsignedBigInteger('payer_id');
            $table->string('payer_name');  // snapshot
            $table->decimal('amount_due', 10, 2);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->enum('status', ['Paid', 'Unpaid', 'Partial'])->default('Unpaid');
            $table->date('date_paid')->nullable();
            $table->string('mode_of_payment')->nullable();
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedInteger('recorded_by')->nullable();
            $table->timestamps();

            $table->index(['payer_type', 'payer_id']);
        });

        $now = now();
        DB::table('fee_types')->insert([
            ['fee_group' => 'Fellowship Registration', 'name' => 'Member Specialist',          'amount' => 300, 'currency' => 'USD', 'applies_to_subscription' => false, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['fee_group' => 'Fellowship Registration', 'name' => 'Fellowship By Election/Reg',  'amount' => 500, 'currency' => 'USD', 'applies_to_subscription' => false, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['fee_group' => 'Annual Subscription',      'name' => 'Fellows',                    'amount' => 100, 'currency' => 'USD', 'applies_to_subscription' => true,  'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['fee_group' => 'Annual Subscription',      'name' => 'Overseas Fellow',             'amount' => 120, 'currency' => 'USD', 'applies_to_subscription' => true,  'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['fee_group' => 'Annual Subscription',      'name' => 'Associate Fellows',           'amount' => 70,  'currency' => 'USD', 'applies_to_subscription' => true,  'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['fee_group' => 'Annual Subscription',      'name' => 'Members',                    'amount' => 50,  'currency' => 'USD', 'applies_to_subscription' => true,  'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['fee_group' => 'Annual Subscription',      'name' => 'Associate Members',           'amount' => 30,  'currency' => 'USD', 'applies_to_subscription' => true,  'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['fee_group' => 'Graduation',                'name' => 'Graduation Fee',              'amount' => 500, 'currency' => 'USD', 'applies_to_subscription' => false, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['fee_group' => 'Gown',                      'name' => 'Hiring Gown',                 'amount' => 50,  'currency' => 'USD', 'applies_to_subscription' => false, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['fee_group' => 'Gown',                      'name' => 'Buying Gown',                 'amount' => 200, 'currency' => 'USD', 'applies_to_subscription' => false, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['fee_group' => 'Transcript',                'name' => 'Transcript',                  'amount' => 30,  'currency' => 'USD', 'applies_to_subscription' => false, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_payments');
        Schema::dropIfExists('fee_types');
    }
};
