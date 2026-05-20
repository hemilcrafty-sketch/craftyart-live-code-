<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('new_categories')) {
            return;
        }
        
        Schema::create('new_categories', function (Blueprint $table) {
            $table->id('id');
            $table->integer('parent_category_id')->nullable();
            $table->text('child_cat_ids')->nullable();
            $table->integer('total_templates')->default(0);
            $table->text('id_name');
            $table->text('string_id')->nullable();
            $table->text('cat_link')->nullable();
            $table->bigInteger('emp_id')->nullable()->default(0);
            $table->text('seo_emp_id')->nullable();
            $table->integer('app_id')->nullable();
            $table->text('canonical_link')->nullable();
            $table->text('meta_title')->nullable();
            $table->text('meta_desc')->nullable();
            $table->text('h1_tag')->nullable();
            $table->text('h2_tag')->nullable();
            $table->text('short_desc')->nullable();
            $table->longText('long_desc')->nullable();
            $table->text('tag_line')->nullable();
            $table->string('category_name');
            $table->text('size')->nullable();
            $table->string('category_thumb');
            $table->text('banner')->nullable();
            $table->text('mockup')->nullable();
            $table->text('contents')->nullable();
            $table->text('faqs')->nullable();
            $table->text('top_keywords')->nullable();
            $table->text('cta')->nullable();
            $table->text('primary_keyword')->nullable();
            $table->integer('imp')->default(0);
            $table->text('fldr_str')->nullable();
            $table->integer('no_index')->default(0);
            $table->integer('sequence_number');
            $table->integer('status');
            $table->integer('deleted')->nullable()->default(0);
            $table->timestamp('child_updated_at')->nullable();
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
        Schema::dropIfExists('new_categories');
    }
}

