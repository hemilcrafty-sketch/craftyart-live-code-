<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutomationCampaignSendLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::connection('crafty_automation_mysql')->hasTable('campaign_send_logs')) {
            return;
        }
        
        Schema::connection('crafty_automation_mysql')->create('campaign_send_logs', function (Blueprint $table) {
            $table->id('id');
            $table->string('subject');
            $table->integer('email_template_id')->default(0);
            $table->integer('wp_template_id')->default(0);
            $table->longText('user_ids');
            $table->integer('promo_code')->default(0);
            $table->integer('plan_id')->default(0);
            $table->integer('select_users_type')->default(1);
            $table->integer('auto_pause_count')->default(0);
            $table->integer('total');
            $table->integer('email_sent')->default(0);
            $table->integer('wp_sent')->default(0);
            $table->integer('email_failed')->default(0);
            $table->integer('wp_failed')->default(0);
            $table->string('status')->default('pending');
            $table->integer('stopped')->default(0);
            $table->integer('total_processed')->default(0);
            $table->integer('sent_since_last_pause')->default(0);
            $table->text('last_processed_user_id')->nullable();
            $table->string('pause_type')->nullable();
            $table->string('type');
            $table->integer('auto_resume')->default(1);
            $table->integer('send_type')->default(1);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('crafty_automation_mysql')->dropIfExists('campaign_send_logs');
    }
}

