<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVideoFilterTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $connection = 'crafty_video_mysql';

        $tables = ['styles', 'themes', 'search_tags', 'interests', 'language', 'religions'];

        foreach ($tables as $tableName) {
            if (!Schema::connection($connection)->hasTable($tableName)) {
                Schema::connection($connection)->create($tableName, function (Blueprint $table) {
                    $table->id();
                    $table->string('name');
                    $table->string('id_name')->nullable();
                    $table->integer('emp_id')->nullable();
                    $table->tinyInteger('status')->default(1);
                    $table->timestamps();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $connection = 'crafty_video_mysql';

        Schema::connection($connection)->dropIfExists('styles');
        Schema::connection($connection)->dropIfExists('themes');
        Schema::connection($connection)->dropIfExists('search_tags');
        Schema::connection($connection)->dropIfExists('interests');
        Schema::connection($connection)->dropIfExists('language');
        Schema::connection($connection)->dropIfExists('religions');
    }
}
