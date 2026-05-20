<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFollowupColumnsToContactUsWebTable extends Migration
{
    public function up()
    {
        Schema::table('contact_us_web', function (Blueprint $table) {
            if (!Schema::hasColumn('contact_us_web', 'followup_call')) {
                $table->integer('followup_call')->default(0)->after('system_info');
            }
            if (!Schema::hasColumn('contact_us_web', 'followup_note')) {
                $table->text('followup_note')->nullable()->after('followup_call');
            }
            if (!Schema::hasColumn('contact_us_web', 'followup_label')) {
                $table->string('followup_label', 64)->nullable()->after('followup_note');
            }
            if (!Schema::hasColumn('contact_us_web', 'emp_id')) {
                $table->unsignedBigInteger('emp_id')->nullable()->index()->after('followup_label');
            }
        });
    }

    public function down()
    {
        Schema::table('contact_us_web', function (Blueprint $table) {
            $toDrop = array_filter(
                ['followup_call', 'followup_note', 'followup_label', 'emp_id'],
                function ($col) {
                    return Schema::hasColumn('contact_us_web', $col);
                }
            );
            if (!empty($toDrop)) {
                $table->dropColumn($toDrop);
            }
        });
    }
}
