<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutomationMailSendLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::connection('crafty_automation_mysql')->hasTable('mail_send_logs')) {
            return;
        }
        
        Schema::connection('crafty_automation_mysql')->create('mail_send_logs', function (Blueprint $table) {
            $table->id('id');
            $table->string('subject');
            $table->longText('email_template_id');
            $table->longText('user_ids');
            $table->integer('select_users_type')->default(1);
            $table->integer('auto_pause_count')->default(0);
            $table->integer('plan_id')->default(0);
            $table->integer('promo_code')->default(0);
            $table->integer('total');
            $table->integer('sent')->default(0);
            $table->integer('failed')->default(0);
            $table->string('status')->default('pending');
            $table->integer('stopped')->default(0);
            $table->integer('emails_sent_since_last_pause')->default(0);
            $table->text('last_processed_user_id')->nullable();
            $table->string('pause_type')->nullable();
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
        Schema::connection('crafty_automation_mysql')->dropIfExists('mail_send_logs');
    }
}

