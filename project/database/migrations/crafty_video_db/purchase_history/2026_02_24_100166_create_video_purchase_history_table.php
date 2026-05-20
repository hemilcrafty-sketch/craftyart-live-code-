<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVideoPurchaseHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::connection('crafty_video_mysql')->hasTable('purchase_history')) {
            return;
        }
        
        Schema::connection('crafty_video_mysql')->create('purchase_history', function (Blueprint $table) {
            $table->id('id');
            $table->text('user_id');
            $table->text('contact_no')->nullable();
            $table->text('product_id');
            $table->integer('product_type');
            $table->text('order_id')->nullable();
            $table->string('transaction_id');
            $table->text('payment_id');
            $table->text('currency_code');
            $table->text('amount');
            $table->text('paid_amount')->nullable();
            $table->float('net_amount')->default(0);
            $table->integer('promo_code_id')->default(0);
            $table->text('payment_method');
            $table->text('from_where');
            $table->string('fbc', 512)->nullable();
            $table->string('gclid', 512)->nullable();
            $table->integer('isManual')->nullable()->default(0);
            $table->integer('payment_status')->default(1);
            $table->integer('status');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('crafty_video_mysql')->dropIfExists('purchase_history');
    }
}

