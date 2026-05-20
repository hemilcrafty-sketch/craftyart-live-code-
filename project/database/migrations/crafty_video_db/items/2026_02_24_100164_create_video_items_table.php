<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVideoItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::connection('crafty_video_mysql')->hasTable('items')) {
            return;
        }
        
        Schema::connection('crafty_video_mysql')->create('items', function (Blueprint $table) {
            $table->id('id');
            $table->integer('emp_id')->nullable()->default(1);
            $table->integer('seo_emp_id')->default(0);
            $table->integer('relation_id');
            $table->string('string_id');
            $table->integer('category_id');
            $table->string('video_name')->nullable();
            $table->string('folder_name');
            $table->string('video_thumb')->nullable();
            $table->string('video_url')->nullable();
            $table->string('video_zip_url', 1000);
            $table->integer('width');
            $table->integer('height');
            $table->integer('watermark_height');
            $table->integer('template_type');
            $table->integer('do_front_lottie')->nullable()->default(0);
            $table->text('editable_image')->nullable();
            $table->text('editable_text')->nullable();
            $table->text('keyword')->nullable();
            $table->string('id_name')->nullable();
            $table->string('h2_tag')->nullable();
            $table->string('canonical_link')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('description')->nullable();
            $table->string('lang_id')->nullable();
            $table->string('theme_id')->nullable();
            $table->string('style_id')->nullable();
            $table->string('orientation')->nullable();
            $table->integer('template_size')->nullable();
            $table->string('religion_id')->nullable();
            $table->string('interest_id')->nullable();
            $table->integer('change_text')->nullable()->default(0);
            $table->integer('change_music');
            $table->integer('encrypted')->default(0);
            $table->text('encryption_key')->nullable();
            $table->integer('is_premium');
            $table->tinyInteger('is_freemium')->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('color_ids')->nullable();
            $table->integer('pages');
            $table->integer('status');
            $table->integer('isDeleted')->default(0);
            $table->integer('views')->default(0);
            $table->integer('daily_views')->default(0);
            $table->integer('weekly_views')->default(0);
            $table->integer('creation')->nullable()->default(0);
            $table->integer('daily_creation')->nullable()->default(0);
            $table->integer('weekly_creation')->default(0);
            $table->integer('no_index')->default(1);
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
        Schema::connection('crafty_video_mysql')->dropIfExists('items');
    }
}

