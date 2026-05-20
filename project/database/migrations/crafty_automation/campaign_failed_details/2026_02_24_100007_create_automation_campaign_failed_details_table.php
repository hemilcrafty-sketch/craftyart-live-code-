<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutomationCampaignFailedDetailsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    if (Schema::connection('crafty_automation_mysql')->hasTable('campaign_failed_details')) {
      return;
    }

    Schema::connection('crafty_automation_mysql')->create('campaign_failed_details', function (Blueprint $table) {
      $table->id('id');
      $table->string('log_id');
      $table->integer('user_id');
      $table->string('email')->nullable();
      $table->string('contact_no')->nullable();
      $table->string('status');
      $table->text('error_message');
      $table->string('type');
      $table->integer('send_type')->default(1);
      $table->timestamp('created_at')->useCurrent();
      $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::connection('crafty_automation_mysql')->dropIfExists('campaign_failed_details');
  }
}
