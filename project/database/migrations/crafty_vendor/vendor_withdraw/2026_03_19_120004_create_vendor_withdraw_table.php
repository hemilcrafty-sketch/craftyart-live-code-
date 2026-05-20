<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'crafty_vendor_mysql';

    public function up(): void
    {
        $schema = Schema::connection($this->connection);

        if ($schema->hasTable('vendor_withdraw')) {
            return;
        }

        $schema->create('vendor_withdraw', function (Blueprint $table) {
            $table->id();
            $table->string('string_id');
            $table->string('user_id');
            $table->string('bank_details_id');
            $table->integer('amount')->default(0);
            $table->string('razorpay_payout_id')->nullable();
            $table->string('utr')->nullable();
            $table->string('currency');
            $table->enum('vendor_type', ['affiliate', 'freelancer']);
            $table->enum('status', ['processing', 'pending', 'completed', 'failed', 'rejected'])->default('pending');
            $table->timestamps();
            $table->timestamp('completed_at')->nullable();
            $table->text('failure_reason')->nullable();

            $table->index('user_id');
            $table->index('string_id');
            $table->index('bank_details_id');
            $table->index(['status', 'vendor_type']);
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('vendor_withdraw');
    }
};
