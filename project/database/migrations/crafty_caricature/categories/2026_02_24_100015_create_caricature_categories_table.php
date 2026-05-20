<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCaricatureCategoriesTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    if (Schema::connection('crafty_caricature_mysql')->hasTable('categories')) {
      return;
    }

    Schema::connection('crafty_caricature_mysql')->create('categories', function (Blueprint $table) {
      // Primary Key - id (int, auto_increment)
      $table->integer('id')->autoIncrement()->unsigned();

      // Parent-Child Relationship
      $table->integer('parent_category_id')->nullable()->default(0);
      $table->string('cat_link')->nullable();
      $table->longText('child_cat_ids')->nullable();

      // Unique Identifiers
      $table->string('id_name');
      $table->string('canonical_link', 500)->nullable();
      $table->string('string_id', 50);

      // Employee Relations
      $table->integer('emp_id')->nullable();
      $table->string('seo_emp_id')->nullable();

      // SEO Fields
      $table->string('meta_title');
      $table->string('primary_keyword');
      $table->text('meta_desc')->nullable();
      $table->string('tag_line');
      $table->string('h1_tag')->nullable();
      $table->string('h2_tag')->nullable();
      $table->text('short_desc')->nullable();
      $table->longText('long_desc')->nullable();
      $table->text('contents')->nullable();
      $table->text('faqs')->nullable();

      // Category Details
      $table->string('category_name');
      $table->string('size', 50)->nullable();
      $table->string('category_thumb', 500);
      $table->string('banner', 500)->nullable();
      $table->string('mockup', 500)->nullable();
      $table->longText('top_keywords')->nullable();

      // Organization
      $table->string('fldr_str', 50);
      $table->tinyInteger('imp')->default(0);
      $table->integer('sequence_number')->default(0);
      $table->integer('total_templates')->default(0);

      // Status Flags
      $table->tinyInteger('status')->default(1);
      $table->tinyInteger('no_index')->default(0);
      $table->tinyInteger('deleted')->default(0);

      // Timestamps
      $table->timestamp('created_at')->nullable()->useCurrent();
      $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
      $table->timestamp('child_updated_at')->nullable()->useCurrent();

      // Indexes - exactly as per SQL file
      $table->unique('id_name', 'id_name_unique');
      $table->unique('string_id', 'string_id_unique');
      $table->index('parent_category_id', 'parent_category_id_index');
      $table->index('status', 'status_index');
      $table->index('emp_id', 'emp_id_index');
      $table->index('sequence_number', 'sequence_number_index');
      $table->index('fldr_str', 'fldr_str_index');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::connection('crafty_caricature_mysql')->dropIfExists('categories');
  }
}
