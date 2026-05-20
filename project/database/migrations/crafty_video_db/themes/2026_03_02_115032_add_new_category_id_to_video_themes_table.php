<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewCategoryIdToVideoThemesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::connection('crafty_video_mysql')->hasColumn('themes', 'new_category_id')) {
            Schema::connection('crafty_video_mysql')->table('themes', function (Blueprint $table) {
                $table->json('new_category_id')->nullable()->after('id_name');
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
        Schema::connection('crafty_video_mysql')->table('themes', function (Blueprint $table) {
            $table->dropColumn('new_category_id');
        });
    }
}
