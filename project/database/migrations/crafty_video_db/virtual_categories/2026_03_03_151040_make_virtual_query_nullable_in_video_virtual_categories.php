<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MakeVirtualQueryNullableInVideoVirtualCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::connection('crafty_video_mysql')->statement('ALTER TABLE `virtual_categories` MODIFY `virtual_query` LONGTEXT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::connection('crafty_video_mysql')->statement('ALTER TABLE `virtual_categories` MODIFY `virtual_query` LONGTEXT NOT NULL');
    }
}
