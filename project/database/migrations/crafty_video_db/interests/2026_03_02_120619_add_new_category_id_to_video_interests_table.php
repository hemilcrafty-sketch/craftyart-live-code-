<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewCategoryIdToVideoInterestsTable extends Migration
{
    public function up()
    {
        if (!Schema::connection('crafty_video_mysql')->hasColumn('interests', 'new_category_id')) {
            Schema::connection('crafty_video_mysql')->table('interests', function (Blueprint $table) {
                $table->json('new_category_id')->nullable()->after('id_name');
            });
        }
    }

    public function down()
    {
        Schema::connection('crafty_video_mysql')->table('interests', function (Blueprint $table) {
            $table->dropColumn('new_category_id');
        });
    }
}
