<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFollowupColumnsToWpFeedbackRequestsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    if (!Schema::connection('crafty_automation_mysql')->hasTable('wp_feedback_requests')) {
      return;
    }

    Schema::connection('crafty_automation_mysql')->table('wp_feedback_requests', function (Blueprint $table) {
      if (!Schema::connection('crafty_automation_mysql')->hasColumn('wp_feedback_requests', 'followup_call')) {
        $table->boolean('followup_call')->default(false)->after('days_after_purchase');
      }
      if (!Schema::connection('crafty_automation_mysql')->hasColumn('wp_feedback_requests', 'followup_note')) {
        $table->text('followup_note')->nullable()->after('followup_call');
      }
      if (!Schema::connection('crafty_automation_mysql')->hasColumn('wp_feedback_requests', 'followup_label')) {
        $table->string('followup_label', 64)->nullable()->after('followup_note');
      }
      if (!Schema::connection('crafty_automation_mysql')->hasColumn('wp_feedback_requests', 'emp_id')) {
        $table->unsignedBigInteger('emp_id')->nullable()->index()->after('followup_label');
      }
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    $conn = 'crafty_automation_mysql';
    if (!Schema::connection($conn)->hasTable('wp_feedback_requests')) {
      return;
    }

    $toDrop = array_values(array_filter(
      ['followup_call', 'followup_note', 'followup_label', 'emp_id'],
      function ($col) use ($conn) {
        return Schema::connection($conn)->hasColumn('wp_feedback_requests', $col);
      }
    ));

    if ($toDrop !== []) {
      Schema::connection($conn)->table('wp_feedback_requests', function (Blueprint $table) use ($toDrop) {
        $table->dropColumn($toDrop);
      });
    }
  }
}
