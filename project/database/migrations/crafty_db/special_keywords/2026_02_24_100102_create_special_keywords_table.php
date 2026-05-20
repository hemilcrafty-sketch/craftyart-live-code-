<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpecialKeywordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('special_keywords')) {
            return;
        }
        
        Schema::create('special_keywords', function (Blueprint $table) {
            $table->id('id');
            $table->bigInteger('emp_id')->nullable();
            $table->integer('cat_id')->default(0);
            $table->text('string_id')->nullable();
            $table->text('name');
            $table->text('canonical_link')->nullable();
            $table->text('meta_title');
            $table->text('title');
            $table->text('h2_tag')->nullable();
            $table->text('meta_desc');
            $table->text('short_desc');
            $table->longText('long_desc')->nullable();
            $table->text('banner')->nullable();
            $table->text('contents')->nullable();
            $table->text('faqs')->nullable();
            $table->longText('top_keywords')->nullable();
            $table->text('fldr_str')->nullable();
            $table->text('cta')->nullable();
            $table->text('primary_keyword')->nullable();
            $table->integer('no_index')->default(0);
            $table->integer('status');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('special_keywords');
    }
}

