<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'crafty_creator_mysql';

    public function up(): void
    {
        $schema = Schema::connection($this->connection);

        if (!$schema->hasTable('design_seo_details')) {
            return;
        }

        if (!$schema->hasColumn('design_seo_details', 'post_thumb')) {
            $schema->table('design_seo_details', function (Blueprint $table) {
                $table->string('post_thumb', 512)->nullable()->after('og_image');
            });
        }

        if (!$schema->hasColumn('design_seo_details', 'additional_thumb')) {
            $schema->table('design_seo_details', function (Blueprint $table) {
                $table->string('additional_thumb', 512)->nullable()->after('post_thumb');
            });
        }
    }

    public function down(): void
    {
        $schema = Schema::connection($this->connection);

        if (!$schema->hasTable('design_seo_details')) {
            return;
        }

        if ($schema->hasColumn('design_seo_details', 'additional_thumb')) {
            $schema->table('design_seo_details', function (Blueprint $table) {
                $table->dropColumn('additional_thumb');
            });
        }

        if ($schema->hasColumn('design_seo_details', 'post_thumb')) {
            $schema->table('design_seo_details', function (Blueprint $table) {
                $table->dropColumn('post_thumb');
            });
        }
    }
};
