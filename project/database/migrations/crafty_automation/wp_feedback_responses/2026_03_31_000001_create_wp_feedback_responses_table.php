<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWpFeedbackResponsesTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    if (Schema::connection('crafty_automation_mysql')->hasTable('wp_feedback_responses')) {
      return;
    }

    Schema::connection('crafty_automation_mysql')->create('wp_feedback_responses', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('wp_feedback_request_id')->index();
      $table->string('user_id', 64)->index();
      $table->unsignedBigInteger('purchase_id')->index();
      $table->tinyInteger('rating')->index()->comment('1-5 stars');
      $table->text('feedback_text')->nullable();
      $table->text('suggestions')->nullable();
      $table->string('ip_address', 45)->nullable();
      $table->text('user_agent')->nullable();
      $table->timestamp('submitted_at')->useCurrent()->useCurrentOnUpdate();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::connection('crafty_automation_mysql')->dropIfExists('wp_feedback_responses');
  }
}
