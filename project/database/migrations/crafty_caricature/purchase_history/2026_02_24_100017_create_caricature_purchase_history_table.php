<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCaricaturePurchaseHistoryTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    if (Schema::connection('crafty_caricature_mysql')->hasTable('purchase_history')) {
      return;
    }

    Schema::connection('crafty_caricature_mysql')->create('purchase_history', function (Blueprint $table) {
      // Primary Key - id (bigint, auto_increment)
      $table->bigInteger('id')->autoIncrement()->unsigned();

      // User Information
      $table->text('user_id');
      $table->text('contact_no')->nullable();

      // Product Details
      $table->text('product_id');
      $table->integer('product_type');

      // Transaction Details
      $table->text('order_id')->nullable();
      $table->string('transaction_id');
      $table->text('payment_id');

      // PhonePe AutoPay fields
      $table->string('phonepe_merchant_order_id')->nullable();
      $table->string('phonepe_subscription_id')->nullable();
      $table->string('phonepe_order_id')->nullable();
      $table->string('phonepe_transaction_id')->nullable();
      $table->boolean('is_autopay_enabled')->default(false);
      $table->string('autopay_status')->nullable(); // PENDING, ACTIVE, CANCELLED
      $table->timestamp('autopay_activated_at')->nullable();
      $table->timestamp('next_autopay_date')->nullable();
      $table->integer('autopay_count')->default(0);

      // Payment Information
      $table->text('currency_code');
      $table->text('amount');
      $table->text('paid_amount')->nullable();
      $table->float('net_amount')->default(0);
      $table->integer('promo_code_id')->default(0);
      $table->text('payment_method');

      // Source Tracking
      $table->text('from_where');
      $table->string('fbc', 512)->nullable();
      $table->string('gclid', 512)->nullable();

      // Status Flags
      $table->integer('isManual')->default(0);
      $table->integer('payment_status')->default(1);
      $table->integer('status');
      $table->integer('used')->default(0);

      // Timestamps
      $table->timestamp('created_at')->useCurrent();
      $table->timestamp('updated_at')->useCurrent();

      // Indexes - exactly as per SQL file
      $table->index('fbc', 'fbc');
      $table->index('gclid', 'gclid');

      // PhonePe AutoPay indexes
      $table->index('phonepe_merchant_order_id', 'phonepe_merchant_order_id');
      $table->index('phonepe_subscription_id', 'phonepe_subscription_id');
      $table->index('phonepe_order_id', 'phonepe_order_id');
      $table->index('is_autopay_enabled', 'is_autopay_enabled');
      $table->index('autopay_status', 'autopay_status');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::connection('crafty_caricature_mysql')->dropIfExists('purchase_history');
  }
}
