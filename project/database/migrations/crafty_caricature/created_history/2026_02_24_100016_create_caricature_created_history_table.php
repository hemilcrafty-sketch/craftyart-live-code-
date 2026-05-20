<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCaricatureCreatedHistoryTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    if (Schema::connection('crafty_caricature_mysql')->hasTable('created_history')) {
      return;
    }

    Schema::connection('crafty_caricature_mysql')->create('created_history', function (Blueprint $table) {
      // Primary Key - id (int, auto_increment)
      $table->integer('id')->autoIncrement()->unsigned();

      // User and Caricature Relations
      $table->string('user_id');
      $table->string('caricature_id');

      // Image Data
      $table->text('images');

      // Payment Reference
      $table->string('payment_id')->nullable();

      // User Input
      $table->text('user_input')->nullable();

      // Cartoon Image
      $table->text('cartoon_image')->nullable();

      // Display Flag
      $table->integer('show_data')->default(1);

      // Timestamps
      $table->timestamp('created_at')->useCurrent();
      $table->timestamp('updated_at')->useCurrent();

      // Indexes - exactly as per SQL file
      $table->index(['user_id', 'caricature_id', 'created_at', 'updated_at'], 'user_id');
      $table->index('payment_id', 'payment_id');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::connection('crafty_caricature_mysql')->dropIfExists('created_history');
  }
}
