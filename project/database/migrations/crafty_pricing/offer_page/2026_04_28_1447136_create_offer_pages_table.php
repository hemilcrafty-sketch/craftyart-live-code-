<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOfferPagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    protected $connection = 'crafty_pricing_mysql';

    public function up()
    {
        if (Schema::connection($this->connection)->hasTable('offer_pages')) {
            return;
        }

        Schema::connection($this->connection)->create('offer_pages', function (Blueprint $table) {

            $table->id();

            $table->unsignedInteger('offer_package_id')->index();
            $table->string('slug')->unique();

            $table->boolean('enable_instructions')->default(false);
            $table->longText('instructions')->nullable();

            $table->timestamps();

        });
    }

    public function down()
    {
        Schema::connection($this->connection)->dropIfExists('offer_pages');
    }
}
