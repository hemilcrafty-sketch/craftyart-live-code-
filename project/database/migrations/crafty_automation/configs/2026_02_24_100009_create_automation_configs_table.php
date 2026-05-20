<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutomationConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::connection('crafty_automation_mysql')->hasTable('configs')) {
            return;
        }
        
        Schema::connection('crafty_automation_mysql')->create('configs', function (Blueprint $table) {
            $table->id('id');
            $table->string('name');
            $table->longText('value');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('crafty_automation_mysql')->dropIfExists('configs');
    }
}

