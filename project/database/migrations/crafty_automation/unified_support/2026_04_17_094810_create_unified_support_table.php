<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUnifiedSupportTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::connection('crafty_automation_mysql')->hasTable('unified_support')) {
            return;
        }

        Schema::connection('crafty_automation_mysql')->create('unified_support', function (Blueprint $table) {
            $table->id();
            
            // Foreign key to purchase_history table (in crafty_revenue database)
            $table->unsignedBigInteger('purchase_history_id')->index();
            
            // Employee and user information
            $table->integer('emp_id')->nullable()->index();
            $table->string('user_id', 255)->index();
            
            // Subscription followup fields
            $table->tinyInteger('subscription_followup_call')->default(0)->comment('0=pending, 1=completed');
            $table->text('subscription_followup_note')->nullable();
            $table->string('subscription_followup_label', 100)->nullable()->index();
            
            // Expiry followup fields
            $table->tinyInteger('expire_followup_call')->default(0)->comment('0=pending, 1=completed');
            $table->text('expire_followup_note')->nullable();
            $table->string('expire_followup_label', 100)->nullable()->index();
            
            // Communication tracking for expired users
            $table->integer('expire_email_sent')->default(0);
            $table->integer('expire_wp_sent')->default(0);
            
            $table->timestamps();
            
            // Composite indexes for better query performance with custom names
            $table->index(['purchase_history_id', 'emp_id'], 'us_purchase_emp_idx');
            $table->index(['subscription_followup_call', 'subscription_followup_label'], 'us_sub_followup_idx');
            $table->index(['expire_followup_call', 'expire_followup_label'], 'us_exp_followup_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('crafty_automation_mysql')->dropIfExists('unified_support');
    }
}
