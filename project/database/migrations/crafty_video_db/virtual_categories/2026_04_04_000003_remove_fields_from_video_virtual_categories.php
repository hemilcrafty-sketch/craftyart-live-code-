<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveFieldsFromVideoVirtualCategories extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::connection('crafty_video_mysql')->table('virtual_categories', function (Blueprint $table) {
      $table->dropColumn(['id_name', 'virtual_query', 'app_id']);
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::connection('crafty_video_mysql')->table('virtual_categories', function (Blueprint $table) {
      $table->text('id_name')->after('parent_category_id');
      $table->longText('virtual_query')->nullable()->after('fldr_str');
      $table->integer('app_id')->nullable()->after('seo_emp_id');
    });
  }
}
