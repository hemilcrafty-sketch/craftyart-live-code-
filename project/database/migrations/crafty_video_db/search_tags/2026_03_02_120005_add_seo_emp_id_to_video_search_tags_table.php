<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSeoEmpIdToVideoSearchTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::connection('crafty_video_mysql')->hasColumn('search_tags', 'seo_emp_id')) {
            Schema::connection('crafty_video_mysql')->table('search_tags', function (Blueprint $table) {
                $table->integer('seo_emp_id')->nullable()->after('id_name');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('crafty_video_mysql')->table('search_tags', function (Blueprint $table) {
            $table->dropColumn('seo_emp_id');
        });
    }
}
