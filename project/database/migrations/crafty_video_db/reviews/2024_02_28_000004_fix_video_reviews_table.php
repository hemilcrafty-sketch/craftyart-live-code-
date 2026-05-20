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
    // Drop and recreate the table with correct structure
    Schema::connection('crafty_video_mysql')->dropIfExists('reviews');

    Schema::connection('crafty_video_mysql')->create('reviews', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('user_id')->nullable()->comment('User ID from main database');
      $table->string('name')->nullable();
      $table->string('email')->nullable();
      $table->string('photo_uri')->nullable();
      $table->text('feedback')->nullable();
      $table->tinyInteger('rate')->default(5)->comment('Rating 1-5');
      $table->tinyInteger('is_approve')->default(0)->comment('0=Not Approved, 1=Approved');
      $table->timestamps();

      // Indexes
      $table->index('user_id');
      $table->index('is_approve');
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
