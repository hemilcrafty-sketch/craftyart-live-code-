<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ConsolidatedUnifiedSupportUpdates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $connection = 'crafty_automation_mysql';

        // 1. Add WP Feedback columns if they don't exist
        Schema::connection($connection)->table('unified_support', function (Blueprint $table) use ($connection) {
            if (!Schema::connection($connection)->hasColumn('unified_support', 'wp_feedback_followup_call')) {
                $table->tinyInteger('wp_feedback_followup_call')->default(0)->after('expire_wp_sent');
            }
            if (!Schema::connection($connection)->hasColumn('unified_support', 'wp_feedback_followup_note')) {
                $table->text('wp_feedback_followup_note')->nullable()->after('wp_feedback_followup_call');
            }
            if (!Schema::connection($connection)->hasColumn('unified_support', 'wp_feedback_followup_label')) {
                $table->string('wp_feedback_followup_label', 100)->nullable()->index()->after('wp_feedback_followup_note');
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
        Schema::connection('crafty_automation_mysql')->table('unified_support', function (Blueprint $table) {
            $table->dropColumn(['wp_feedback_followup_call', 'wp_feedback_followup_note', 'wp_feedback_followup_label']);
        });
    }
}
