<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpecialPagePersonalAccessTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::connection('special_page_mysql')->hasTable('personal_access_tokens')) {
            return;
        }
        
        Schema::connection('special_page_mysql')->create('personal_access_tokens', function (Blueprint $table) {
            $table->id('id');
            $table->string('tokenable_type');
            $table->bigInteger('tokenable_id')->unsigned();
            $table->string('name');
            $table->string('token', 64);
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
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
        Schema::connection('special_page_mysql')->dropIfExists('personal_access_tokens');
    }
}

