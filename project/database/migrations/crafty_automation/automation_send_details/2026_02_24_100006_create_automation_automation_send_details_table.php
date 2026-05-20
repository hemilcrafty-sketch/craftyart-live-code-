<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutomationAutomationSendDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::connection('crafty_automation_mysql')->hasTable('automation_send_details')) {
            return;
        }

        Schema::connection('crafty_automation_mysql')->create('automation_send_details', function (Blueprint $table) {
            $table->id('id');
            $table->bigInteger('log_id')->unsigned();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->enum('type', ['email', 'whatsapp']);
            $table->integer('send_type');
            $table->string('email')->nullable();
            $table->string('contact_number')->nullable();
            $table->enum('status', ['sent', 'failed']);
            $table->text('error_message')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            // Indexes
            $table->index(['log_id', 'type'], 'automation_send_details_log_id_type_index');
            $table->index(['user_id', 'type'], 'automation_send_details_user_id_type_index');
            $table->index(['status', 'created_at'], 'automation_send_details_status_created_at_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('crafty_automation_mysql')->dropIfExists('automation_send_details');
    }
}

