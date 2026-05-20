<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Remove video_ prefix from crafty_video_db tables.
     * - Rename video_* to * when only old exists
     * - Copy data and drop when both exist (duplicates)
     * - Rename langs to language
     * - Create page_reviews table
     * - Drop video_page_reviews
     */
    public function up(): void
    {
        $connection = 'crafty_video_mysql';
        $schema = Schema::connection($connection);

        $tablePairs = [
            'video_reviews' => 'reviews',
            'video_styles' => 'styles',
            'video_themes' => 'themes',
            'video_search_tags' => 'search_tags',
            'video_interests' => 'interests',
            'video_langs' => 'language',
            'video_religions' => 'religions',
            'video_virtual_categories' => 'virtual_categories',
        ];

        foreach ($tablePairs as $oldTable => $newTable) {
            if ($schema->hasTable($oldTable) && !$schema->hasTable($newTable)) {
                DB::connection($connection)->statement("RENAME TABLE `{$oldTable}` TO `{$newTable}`");
            } elseif ($schema->hasTable($oldTable) && $schema->hasTable($newTable)) {
                $rowCount = DB::connection($connection)->table($oldTable)->count();
                if ($rowCount > 0) {
                    try {
                        $oldCols = array_column(DB::connection($connection)->select("SHOW COLUMNS FROM `{$oldTable}`"), 'Field');
                        $newCols = array_column(DB::connection($connection)->select("SHOW COLUMNS FROM `{$newTable}`"), 'Field');
                        $common = array_values(array_intersect($oldCols, $newCols));
                        if (!empty($common)) {
                            $cols = '`' . implode('`, `', $common) . '`';
                            DB::connection($connection)->statement("INSERT IGNORE INTO `{$newTable}` ({$cols}) SELECT {$cols} FROM `{$oldTable}`");
                        }
                    } catch (\Throwable $e) {
                        // Continue with drop
                    }
                }
                $schema->dropIfExists($oldTable);
            }
        }

        if ($schema->hasTable('langs') && !$schema->hasTable('language')) {
            DB::connection($connection)->statement('RENAME TABLE `langs` TO `language`');
        }

        if (!$schema->hasTable('page_reviews')) {
            Schema::connection($connection)->create('page_reviews', function (Blueprint $table) {
                $table->id();
                $table->text('user_id')->nullable();
                $table->integer('p_type');
                $table->text('p_id');
                $table->string('name', 255)->nullable();
                $table->string('email', 255)->nullable();
                $table->string('photo_uri', 255)->nullable();
                $table->longText('feedback');
                $table->text('suggestion_type')->nullable();
                $table->text('summarised')->nullable();
                $table->double('rate');
                $table->integer('is_approve')->nullable()->default(0);
                $table->integer('is_deleted')->default(0);
                $table->timestamps();
            });

            if (Schema::hasTable('p_reviews')) {
                $videoReviews = DB::connection('mysql')->table('p_reviews')
                    ->whereIn('p_type', [6, 7, 8])
                    ->get();
                foreach ($videoReviews as $row) {
                    $data = (array) $row;
                    unset($data['id']);
                    DB::connection($connection)->table('page_reviews')->insert($data);
                }
            }
        }

        $schema->dropIfExists('video_page_reviews');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = 'crafty_video_mysql';
        $schema = Schema::connection($connection);

        $renames = [
            'reviews' => 'video_reviews',
            'styles' => 'video_styles',
            'themes' => 'video_themes',
            'search_tags' => 'video_search_tags',
            'interests' => 'video_interests',
            'language' => 'video_langs',
            'religions' => 'video_religions',
            'virtual_categories' => 'video_virtual_categories',
        ];

        foreach ($renames as $oldName => $newName) {
            if ($schema->hasTable($oldName)) {
                DB::connection($connection)->statement("RENAME TABLE `{$oldName}` TO `{$newName}`");
            }
        }

        if ($schema->hasTable('language') && !$schema->hasTable('langs')) {
            DB::connection($connection)->statement('RENAME TABLE `language` TO `langs`');
        }

        $schema->dropIfExists('page_reviews');
    }
};
