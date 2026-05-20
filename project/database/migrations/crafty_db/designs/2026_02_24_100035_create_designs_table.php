<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDesignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('designs')) {
            return;
        }
        
        Schema::create('designs', function (Blueprint $table) {
            $table->id('id');
            $table->string('string_id')->nullable();
            $table->text('id_name')->nullable();
            $table->text('canonical_link')->nullable();
            $table->text('h2_tag')->nullable();
            $table->text('creator_id')->nullable();
            $table->text('creator_draft_id')->nullable();
            $table->bigInteger('emp_id')->nullable()->default(0);
            $table->integer('seo_emp_id')->default(0);
            $table->integer('seo_assigner_id')->default(0);
            $table->integer('app_id');
            $table->bigInteger('category_id')->nullable()->default(0);
            $table->bigInteger('new_category_id')->default(0);
            $table->text('sub_cat_id')->nullable();
            $table->text('style_id')->nullable();
            $table->text('interest_id')->nullable();
            $table->text('lang_id')->nullable();
            $table->json('caricature_ids')->nullable();
            $table->string('post_name');
            $table->text('meta_title')->nullable();
            $table->text('additional_thumb')->nullable();
            $table->string('post_thumb');
            $table->text('thumb_array')->nullable();
            $table->integer('default_thumb_pos')->default(0);
            $table->text('video_thumb')->nullable();
            $table->string('ratio');
            $table->integer('width');
            $table->integer('height');
            $table->longText('designs')->nullable();
            $table->longText('fab_designs')->nullable();
            $table->integer('total_pages')->nullable()->default(1);
            $table->text('description')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('related_tags')->nullable();
            $table->text('new_related_tags')->nullable();
            $table->text('special_keywords')->nullable();
            $table->text('start_date')->nullable();
            $table->text('end_date')->nullable();
            $table->bigInteger('size')->nullable()->default(0);
            $table->text('template_size')->nullable();
            $table->integer('animation')->nullable()->default(0);
            $table->text('theme_id')->nullable();
            $table->text('color_id')->nullable();
            $table->text('religion_id')->nullable();
            $table->bigInteger('trending_views')->nullable()->default(0);
            $table->bigInteger('views')->nullable()->default(0);
            $table->bigInteger('web_views')->default(0);
            $table->integer('auto_create')->nullable()->default(0);
            $table->integer('is_premium');
            $table->integer('is_freemium')->default(0);
            $table->integer('editor_choice')->default(0);
            $table->text('orientation')->nullable();
            $table->text('cta')->nullable();
            $table->integer('pinned')->default(0);
            $table->integer('no_index')->default(1);
            $table->integer('status');
            $table->integer('latest')->default(1);
            $table->integer('deleted')->nullable()->default(0);
            $table->integer('has_bug')->nullable()->default(0);
            $table->integer('is_fix')->nullable()->default(0);
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
        Schema::dropIfExists('designs');
    }
}

