<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutomationAutomationSendLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::connection('crafty_automation_mysql')->hasTable('automation_send_logs')) {
            return;
        }

        Schema::connection('crafty_automation_mysql')->create('automation_send_logs', function (Blueprint $table) {
            $table->id('id');
            $table->string('campaign_name');
            $table->longText('user_ids')->nullable();
            $table->integer('select_users_type')->nullable();
            $table->integer('total');
            $table->integer('sent')->default(0);
            $table->integer('failed')->default(0);
            $table->string('status');
            $table->string('log_id');
            $table->integer('user_id');
            $table->string('email')->nullable();
            $table->string('contact_no')->nullable();
            $table->text('error_message');
            $table->string('type');
            $table->integer('send_type')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // Indexes
            $table->index(['type', 'status'], 'automation_send_logs_type_status_index');
            $table->index(['send_type', 'created_at'], 'automation_send_logs_send_type_created_at_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('crafty_automation_mysql')->dropIfExists('automation_send_logs');
    }
}

