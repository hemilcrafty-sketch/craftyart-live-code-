<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('crafty_automation_mysql')->table('unified_support', function (Blueprint $table) {
            $table->integer('account_creation_email_sent')->default(0)->after('user_id');
            $table->integer('account_creation_wp_sent')->default(0)->after('account_creation_email_sent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('crafty_automation_mysql')->table('unified_support', function (Blueprint $table) {
            $table->dropColumn(['account_creation_email_sent', 'account_creation_wp_sent']);
        });
    }
};
