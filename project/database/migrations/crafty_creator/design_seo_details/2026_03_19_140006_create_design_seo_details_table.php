<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'crafty_creator_mysql';

    public function up(): void
    {
        $schema = Schema::connection($this->connection);

        if ($schema->hasTable('design_seo_details')) {
            return;
        }

        $schema->create('design_seo_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('design_submission_id')->unique();
            $table->string('post_name')->nullable();
            $table->string('id_name')->nullable();
            $table->string('h2_tag')->nullable();
            $table->text('description')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('slug')->nullable();
            $table->json('keywords')->nullable();
            $table->string('og_image')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_trending')->default(false);
            $table->integer('priority')->default(0);
            $table->unsignedBigInteger('primary_category_id')->nullable();
            $table->json('filters')->nullable();
            $table->string('aspect_ratio')->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->timestamps();

            $table->foreign('design_submission_id')->references('id')->on('design_submissions')->onDelete('cascade');
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('design_seo_details');
    }
};
