<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Vendor / affiliate wallet configuration (crafty_vendor).
 * Shared by panel (freelancer designer wallet UI) and API (VendorController).
 */
return new class extends Migration {
    protected $connection = 'crafty_vendor_mysql';

    public function up(): void
    {
        $schema = Schema::connection($this->connection);

        if ($schema->hasTable('wallet_settings')) {
            return;
        }

        $schema->create('wallet_settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key')->unique();
            $table->string('setting_name')->nullable();
            $table->text('description')->nullable();
            $table->decimal('min_withdrawal_threshold', 10, 2)->default(500.00);
            $table->decimal('max_withdrawal_limit', 10, 2)->nullable();
            $table->decimal('platform_commission_rate', 5, 2)->default(30.00);
            $table->enum('payment_type', ['manual', 'razorpay'])->default('manual');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
            $table->index('payment_type');
        });

        $conn = DB::connection($this->connection);
        if (!$conn->table('wallet_settings')->where('setting_key', 'default')->exists()) {
            $conn->table('wallet_settings')->insert([
                'setting_key' => 'default',
                'setting_name' => 'Default',
                'description' => null,
                'min_withdrawal_threshold' => 500,
                'max_withdrawal_limit' => null,
                'platform_commission_rate' => 30,
                'payment_type' => 'manual',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('wallet_settings');
    }
};
