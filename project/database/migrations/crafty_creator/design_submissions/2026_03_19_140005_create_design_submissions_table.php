<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'crafty_creator_mysql';

    public function up(): void
    {
        $schema = Schema::connection($this->connection);

        if ($schema->hasTable('design_submissions')) {
            return;
        }

        $schema->create('design_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('designer_id')->comment('user_data.id - freelancer (crafty_db.user_data.id)');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('design_file_path');
            $table->json('preview_images')->nullable();
            $table->json('tags')->nullable();
            $table->enum('status', [
                'pending_designer_head',
                'approved_by_designer_head',
                'rejected_by_designer_head',
                'pending_seo',
                'approved_by_seo',
                'rejected_by_seo',
                'live',
            ])->default('pending_designer_head');
            $table->text('designer_head_notes')->nullable();
            $table->text('seo_head_notes')->nullable();
            $table->unsignedBigInteger('designer_head_reviewed_by')->nullable();
            $table->timestamp('designer_head_reviewed_at')->nullable();
            $table->unsignedBigInteger('seo_head_reviewed_by')->nullable();
            $table->timestamp('seo_head_reviewed_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('crafty_design_id')->nullable()->comment('crafty_db.designs.id when live');
            $table->integer('total_sales')->default(0);
            $table->decimal('total_revenue', 10, 2)->default(0);
            $table->timestamps();

            $table->index(['designer_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('design_submissions');
    }
};
