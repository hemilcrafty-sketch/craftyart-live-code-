<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVideoMainCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::connection('crafty_video_mysql')->hasTable('main_categories')) {
            return;
        }
        
        Schema::connection('crafty_video_mysql')->create('main_categories', function (Blueprint $table) {
            $table->id('id');
            $table->bigInteger('parent_category_id')->nullable()->default(0);
            $table->integer('emp_id')->default(0);
            $table->integer('seo_emp_id')->nullable();
            $table->string('string_id')->nullable();
            $table->string('category_name');
            $table->string('id_name')->nullable();
            $table->string('cat_link')->nullable();
            $table->string('canonical_link')->nullable();
            $table->string('meta_title', 60)->nullable();
            $table->string('primary_keyword')->nullable();
            $table->string('h1_tag', 60)->nullable();
            $table->string('tag_line')->nullable();
            $table->text('meta_desc')->nullable();
            $table->text('short_desc')->nullable();
            $table->string('h2_tag')->nullable();
            $table->text('long_desc')->nullable();
            $table->string('category_thumb');
            $table->string('mockup')->nullable();
            $table->string('banner')->nullable();
            $table->integer('app_id')->nullable();
            $table->text('contents')->nullable();
            $table->text('faqs')->nullable();
            $table->json('top_keywords')->nullable();
            $table->integer('sequence_number');
            $table->integer('status');
            $table->tinyInteger('imp')->default(0);
            $table->tinyInteger('no_index')->default(1);
            $table->string('fldr_str')->nullable();
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
        Schema::connection('crafty_video_mysql')->dropIfExists('main_categories');
    }
}

