<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveIdNameFromVideoItems extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::connection('crafty_video_mysql')->table('items', function (Blueprint $table) {
      $table->dropColumn('id_name');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::connection('crafty_video_mysql')->table('items', function (Blueprint $table) {
      $table->string('id_name')->nullable()->after('keyword');
    });
  }
}
