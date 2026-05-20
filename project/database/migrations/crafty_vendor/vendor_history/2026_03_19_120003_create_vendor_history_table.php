<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'crafty_vendor_mysql';

    public function up(): void
    {
        $schema = Schema::connection($this->connection);

        if ($schema->hasTable('vendor_history')) {
            return;
        }

        $schema->create('vendor_history', function (Blueprint $table) {
            $table->id();
            $table->string('string_id');
            $table->unsignedBigInteger('purchase_id')->nullable()->comment('crafty_revenue.purchase_history.id');
            $table->string('payout_reference', 255)->nullable()->comment('Razorpay payout id etc. when type=withdraw');
            $table->string('user_id');
            $table->integer('vendor_amount')->default(0);
            $table->integer('vendor_percentage');
            $table->string('purchase_user_id')->nullable();
            $table->integer('purchase_amount')->default(0);
            $table->string('currency');
            $table->enum('type', ['old_sub', 'new_sub', 'template', 'caricature', 'video', 'withdraw']);
            $table->enum('vendor_type', ['affiliate', 'freelancer'])->nullable();
            $table->enum('status', ['pending', 'active', 'failed']);
            $table->timestamps();

            $table->index('user_id');
            $table->index('string_id');
            $table->index('purchase_id');
            $table->index('payout_reference');
            $table->index(['status', 'type']);
            $table->index('vendor_type');
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('vendor_history');
    }
};
