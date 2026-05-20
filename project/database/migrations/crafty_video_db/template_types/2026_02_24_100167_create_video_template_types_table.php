<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVideoTemplateTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::connection('crafty_video_mysql')->hasTable('template_types')) {
            return;
        }
        
        Schema::connection('crafty_video_mysql')->create('template_types', function (Blueprint $table) {
            $table->id('id');
            $table->string('type');
            $table->integer('value');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('crafty_video_mysql')->dropIfExists('template_types');
    }
}

