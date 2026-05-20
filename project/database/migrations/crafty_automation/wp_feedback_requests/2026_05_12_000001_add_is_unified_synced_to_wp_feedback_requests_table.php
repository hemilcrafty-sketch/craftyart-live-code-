<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsUnifiedSyncedToWpFeedbackRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('crafty_automation_mysql')->table('wp_feedback_requests', function (Blueprint $table) {
            $table->tinyInteger('is_unified_synced')->default(0)->after('status')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('crafty_automation_mysql')->table('wp_feedback_requests', function (Blueprint $table) {
            $table->dropColumn('is_unified_synced');
        });
    }
}
