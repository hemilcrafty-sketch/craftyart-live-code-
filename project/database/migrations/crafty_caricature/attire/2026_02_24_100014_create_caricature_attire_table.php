<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCaricatureAttireTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::connection('crafty_caricature_mysql')->hasTable('attire')) {
            return;
        }

        Schema::connection('crafty_caricature_mysql')->create('attire', function (Blueprint $table) {
            // Primary Key - id (int, auto_increment)
            $table->integer('id')->autoIncrement()->unsigned();

            // JSON and URLs
            $table->longText('json');
            $table->string('preview_url')->nullable();
            $table->text('attire_url');
            $table->text('thumbnail_url');
            $table->text('coordinate_image');

            // Identifiers
            $table->string('string_id');
            $table->string('id_name');
            $table->string('post_name');

            // SEO Fields
            $table->text('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('h2_tag')->nullable();
            $table->text('long_desc')->nullable();
            $table->string('contents')->nullable();
            $table->string('faqs')->nullable();
            $table->string('canonical_link')->nullable();

            // Attributes
            $table->integer('head_count')->default(0);
            $table->text('style_id')->nullable();
            $table->text('theme_id')->nullable();
            $table->text('religion_id')->nullable();
            $table->text('related_tags')->nullable();
            $table->string('skin_color');
            $table->integer('width')->default(0);
            $table->integer('height')->default(0);

            // Flags
            $table->integer('pinned')->default(0);
            $table->integer('editor_choice')->default(0);
            $table->integer('is_premium')->default(0);
            $table->integer('is_freemium')->default(0);
            $table->integer('status')->default(0);
            $table->integer('no_index')->default(0);
            $table->integer('deleted')->default(0);

            // Statistics
            $table->integer('views')->default(0);
            $table->integer('trending_views')->default(0);

            // Folder and Relations
            $table->string('fldr_str');
            $table->integer('category_id')->default(0);
            $table->integer('emp_id')->default(0);

            // Additional Data
            $table->longText('faces')->nullable();

            // Timestamps
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // No additional indexes in SQL file for attire table except PRIMARY KEY
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('crafty_caricature_mysql')->dropIfExists('attire');
    }
}
