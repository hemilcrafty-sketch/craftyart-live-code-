<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWpFeedbackRequestsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    if (Schema::connection('crafty_automation_mysql')->hasTable('wp_feedback_requests')) {
      return;
    }

    Schema::connection('crafty_automation_mysql')->create('wp_feedback_requests', function (Blueprint $table) {
      $table->id();
      $table->string('string_id', 20)->unique()->nullable();
      $table->string('user_id', 64)->index();
      $table->string('contact_no', 20)->nullable();
      $table->unsignedBigInteger('purchase_id')->index();
      $table->enum('status', ['pending', 'completed', 'expired'])->default('pending');
      $table->timestamp('sent_at')->nullable();
      $table->timestamp('expires_at')->nullable();
      $table->timestamp('completed_at')->nullable();
      $table->integer('days_after_purchase')->default(5);
      $table->timestamps();

      // Composite index for status and expires_at
      $table->index(['status', 'expires_at']);
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::connection('crafty_automation_mysql')->dropIfExists('wp_feedback_requests');
  }
}
