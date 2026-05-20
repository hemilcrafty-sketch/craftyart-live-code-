<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobOpeningsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('job_openings')) {
            return;
        }

        Schema::create('job_openings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->string('location');
            $table->string('type');
            $table->string('department')->nullable();
            $table->string('experience')->nullable();
            $table->string('salary_range')->nullable();
            $table->longText('description');
            $table->json('responsibilities')->nullable();
            $table->json('requirements')->nullable();
            $table->json('perks')->nullable();
            $table->json('tools')->nullable();
            $table->json('interview_steps')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_actively_hiring')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_openings');
    }
}
