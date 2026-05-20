<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMobileAndMetaTokenToUsersTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::table('users', function (Blueprint $table) {
      if (!Schema::hasColumn('users', 'mobile_number')) {
        $table->string('mobile_number', 15)->nullable()->after('email');
        $table->index('mobile_number');
      }
      
      if (!Schema::hasColumn('users', 'meta_api_token')) {
        $table->text('meta_api_token')->nullable()->after('mobile_number');
      }
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table('users', function (Blueprint $table) {
      if (Schema::hasColumn('users', 'mobile_number')) {
        $table->dropIndex(['mobile_number']);
        $table->dropColumn('mobile_number');
      }
      
      if (Schema::hasColumn('users', 'meta_api_token')) {
        $table->dropColumn('meta_api_token');
      }
    });
  }
}
