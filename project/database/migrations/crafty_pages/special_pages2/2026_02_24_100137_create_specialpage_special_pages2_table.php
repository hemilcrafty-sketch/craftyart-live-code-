<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpecialPageSpecialPages2Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::connection('special_page_mysql')->hasTable('special_pages2')) {
            return;
        }
        
        Schema::connection('special_page_mysql')->create('special_pages2', function (Blueprint $table) {
            $table->id('id');
            $table->text('string_id')->nullable();
            $table->integer('cat_id')->default(0);
            $table->string('page_slug');
            $table->text('canonical_link')->nullable();
            $table->text('meta_title');
            $table->text('title');
            $table->text('meta_desc');
            $table->text('description');
            $table->text('pre_breadcrumb')->nullable();
            $table->text('breadcrumb');
            $table->text('banner_type')->nullable();
            $table->text('banner')->nullable();
            $table->text('hero_bg_option')->nullable();
            $table->text('colors');
            $table->text('hero_background_image')->nullable();
            $table->text('body_background_image')->nullable();
            $table->text('button')->nullable();
            $table->text('button_link')->nullable();
            $table->longText('contents')->nullable();
            $table->text('faqs')->nullable();
            $table->enum('page_type', ['special','tool']);
            $table->tinyInteger('button_target')->nullable();
            $table->tinyInteger('button_rel')->nullable();
            $table->text('resume_guide_content')->nullable();
            $table->text('resume_content')->nullable();
            $table->text('fldr_str')->nullable();
            $table->text('top_keywords')->nullable();
            $table->text('cta')->nullable();
            $table->integer('updated_record')->default(0);
            $table->integer('no_index')->default(0);
            $table->tinyInteger('status')->default(0);
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
        Schema::connection('special_page_mysql')->dropIfExists('special_pages2');
    }
}

