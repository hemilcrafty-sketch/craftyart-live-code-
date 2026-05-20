<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutomationWhatsappTemplateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::connection('crafty_automation_mysql')->hasTable('whatsapp_template')) {
            return;
        }
        
        Schema::connection('crafty_automation_mysql')->create('whatsapp_template', function (Blueprint $table) {
            $table->id('id');
            $table->string('campaign_name');
            $table->longText('template_params')->nullable();
            $table->integer('template_params_count')->default(0);
            $table->tinyInteger('media_url')->nullable()->default(0);
            $table->string('url')->nullable();
            $table->integer('status')->default(1);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('crafty_automation_mysql')->dropIfExists('whatsapp_template');
    }
}

