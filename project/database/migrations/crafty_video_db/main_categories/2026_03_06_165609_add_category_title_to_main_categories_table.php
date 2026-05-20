<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddCategoryTitleToMainCategoriesTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    $connection = Schema::connection('crafty_video_mysql');

    // Check if theme column exists
    $hasTheme = $connection->hasColumn('main_categories', 'theme');
    $hasCategoryTitle = $connection->hasColumn('main_categories', 'category_title');

    if ($hasTheme && !$hasCategoryTitle) {
      // Rename theme to category_title using raw SQL
      DB::connection('crafty_video_mysql')->statement('ALTER TABLE main_categories CHANGE theme category_title VARCHAR(255) NULL');
    } elseif (!$hasTheme && !$hasCategoryTitle) {
      // Create category_title if neither exists
      Schema::connection('crafty_video_mysql')->table('main_categories', function (Blueprint $table) {
        $table->string('category_title')->nullable()->after('tag_line');
      });
    }
    // If category_title already exists, do nothing (migration already ran)
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    $connection = Schema::connection('crafty_video_mysql');

    if ($connection->hasColumn('main_categories', 'category_title')) {
      // Rename back to theme using raw SQL
      DB::connection('crafty_video_mysql')->statement('ALTER TABLE main_categories CHANGE category_title theme VARCHAR(255) NULL');
    }
  }
}
