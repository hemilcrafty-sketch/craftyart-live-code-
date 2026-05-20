<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEventDateAndSourceTypeToUnifiedSupportTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $connection = 'crafty_automation_mysql';
        Schema::connection($connection)->table('unified_support', function (Blueprint $table) use ($connection) {
            if (!Schema::connection($connection)->hasColumn('unified_support', 'event_date')) {
                $table->timestamp('event_date')->nullable()->index()->after('user_id');
            }
            if (!Schema::connection($connection)->hasColumn('unified_support', 'source_type')) {
                $table->string('source_type', 50)->nullable()->index()->after('event_date');
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
            $table->dropColumn(['event_date', 'source_type']);
        });
    }
}
