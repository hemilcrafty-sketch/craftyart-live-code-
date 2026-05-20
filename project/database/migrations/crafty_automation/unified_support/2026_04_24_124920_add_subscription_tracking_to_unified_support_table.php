<?php
/*
 * (c) Crafty Art
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSubscriptionTrackingToUnifiedSupportTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('crafty_automation_mysql')->table('unified_support', function (Blueprint $table) {
            $table->integer('subscription_email_sent')->default(0)->after('subscription_followup_label');
            $table->integer('subscription_wp_sent')->default(0)->after('subscription_email_sent');
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
            $table->dropColumn(['subscription_email_sent', 'subscription_wp_sent']);
        });
    }
}
