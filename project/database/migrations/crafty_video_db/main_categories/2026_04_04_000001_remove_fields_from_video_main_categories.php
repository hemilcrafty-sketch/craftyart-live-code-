<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveFieldsFromVideoMainCategories extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::connection('crafty_video_mysql')->table('main_categories', function (Blueprint $table) {
      $table->dropColumn(['id_name', 'cat_link', 'app_id']);
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::connection('crafty_video_mysql')->table('main_categories', function (Blueprint $table) {
      $table->string('id_name')->nullable()->after('category_name');
      $table->string('cat_link')->nullable()->after('slug');
      $table->integer('app_id')->nullable()->after('banner');
    });
  }
}
