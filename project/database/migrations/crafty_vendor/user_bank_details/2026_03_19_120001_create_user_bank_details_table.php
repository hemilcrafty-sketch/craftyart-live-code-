<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'crafty_vendor_mysql';

    public function up(): void
    {
        $this->createDatabaseIfNotExists();

        $schema = Schema::connection($this->connection);

        if ($schema->hasTable('user_bank_details')) {
            return;
        }

        $schema->create('user_bank_details', function (Blueprint $table) {
            $table->id();
            $table->string('string_id');
            $table->string('user_id');
            $table->string('razorpay_bank_account_id')->nullable();
            $table->integer('withdraw_type')->default(0)->comment('0 For Bank Transfer and 1 For UPI');
            $table->string('bank_name')->nullable();
            $table->string('bank_holder_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('ifsc_code')->nullable();
            $table->string('status');
            $table->string('upi')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('string_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('user_bank_details');
    }

    private function createDatabaseIfNotExists(): void
    {
        $database = env('CRAFTY_VENDOR_DB_DATABASE', 'crafty_vendor');
        $charset = config('database.connections.crafty_vendor_mysql.charset', 'utf8mb4');
        $collation = config('database.connections.crafty_vendor_mysql.collation', 'utf8mb4_unicode_ci');

        $query = "CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET {$charset} COLLATE {$collation}";

        try {
            DB::connection('mysql')->statement($query);
        } catch (\Exception $e) {
            // Database might already exist or user lacks permission; continue
        }
    }
};
