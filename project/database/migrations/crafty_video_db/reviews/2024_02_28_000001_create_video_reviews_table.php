<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    if (Schema::connection('crafty_video_mysql')->hasTable('reviews')) {
      return;
    }

    Schema::connection('crafty_video_mysql')->create('reviews', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('user_id')->nullable()->comment('User ID from main database');
      $table->unsignedBigInteger('video_id')->comment('Video template ID');
      $table->string('name')->nullable();
      $table->string('email')->nullable();
      $table->string('photo_uri')->nullable();
      $table->text('feedback')->nullable();
      $table->tinyInteger('rate')->default(5)->comment('Rating 1-5');
      $table->tinyInteger('is_approve')->default(0)->comment('0=Not Approved, 1=Approved');
      $table->tinyInteger('is_deleted')->default(0)->comment('0=Active, 1=Deleted');
      $table->timestamps();

      // Indexes
      $table->index('video_id');
      $table->index('user_id');
      $table->index('is_approve');
      $table->index('is_deleted');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::connection('crafty_video_mysql')->dropIfExists('reviews');
  }
};
