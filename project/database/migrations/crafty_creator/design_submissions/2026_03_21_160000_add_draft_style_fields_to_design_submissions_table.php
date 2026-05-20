<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Align design_submissions with designer_drafts-style payload (editor JSON, thumbs, template_id, etc.).
 */
return new class extends Migration {
    protected $connection = 'crafty_creator_mysql';

    public function up(): void
    {
        $schema = Schema::connection($this->connection);

        if (! $schema->hasTable('design_submissions')) {
            return;
        }

        $schema->table('design_submissions', function (Blueprint $table) {
            if (! Schema::connection($this->connection)->hasColumn('design_submissions', 'string_id')) {
                $table->string('string_id', 128)->nullable()->after('id');
            }
            if (! Schema::connection($this->connection)->hasColumn('design_submissions', 'template_id')) {
                $table->string('template_id', 255)->nullable()->after('string_id');
            }
            if (! Schema::connection($this->connection)->hasColumn('design_submissions', 'app_user_uid')) {
                $table->string('app_user_uid', 64)->nullable()->after('designer_id')
                    ->comment('App user uid (mirrors designer_drafts.user_id)');
            }
            if (! Schema::connection($this->connection)->hasColumn('design_submissions', 'caricature_ids')) {
                $table->json('caricature_ids')->nullable()->after('app_user_uid');
            }
            if (! Schema::connection($this->connection)->hasColumn('design_submissions', 'ratio')) {
                $table->decimal('ratio', 10, 4)->nullable()->after('caricature_ids');
            }
            if (! Schema::connection($this->connection)->hasColumn('design_submissions', 'width')) {
                $table->unsignedInteger('width')->nullable()->after('ratio');
            }
            if (! Schema::connection($this->connection)->hasColumn('design_submissions', 'height')) {
                $table->unsignedInteger('height')->nullable()->after('width');
            }
            if (! Schema::connection($this->connection)->hasColumn('design_submissions', 'thumbs')) {
                $table->json('thumbs')->nullable()->after('preview_images')
                    ->comment('e.g. {"thumb":"uploads/..."} like designer_drafts.thumbs');
            }
            if (! Schema::connection($this->connection)->hasColumn('design_submissions', 'video')) {
                $table->text('video')->nullable()->after('thumbs');
            }
            if (! Schema::connection($this->connection)->hasColumn('design_submissions', 'designs')) {
                $table->longText('designs')->nullable()->after('video')
                    ->comment('Editor/canvas JSON (designer_drafts.designs)');
            }
            if (! Schema::connection($this->connection)->hasColumn('design_submissions', 'msg')) {
                $table->text('msg')->nullable()->after('designs');
            }
            if (! Schema::connection($this->connection)->hasColumn('design_submissions', 'is_live')) {
                $table->smallInteger('is_live')->nullable()->after('msg')
                    ->comment('Editor draft flag; mirrors designer_drafts.is_live (-1 draft, 1 live)');
            }
        });

        if (Schema::connection($this->connection)->hasColumn('design_submissions', 'string_id')) {
            try {
                $schema->table('design_submissions', function (Blueprint $table) {
                    $table->unique('string_id');
                });
            } catch (\Throwable) {
                // unique index already exists
            }
        }

        // Allow editor-only submissions (no binary design file)
        try {
            DB::connection($this->connection)->statement(
                'ALTER TABLE `design_submissions` MODIFY `design_file_path` VARCHAR(255) NULL'
            );
        } catch (\Throwable) {
        }
    }

    public function down(): void
    {
        $schema = Schema::connection($this->connection);

        if (! $schema->hasTable('design_submissions')) {
            return;
        }

        if (Schema::connection($this->connection)->hasColumn('design_submissions', 'string_id')) {
            try {
                $schema->table('design_submissions', function (Blueprint $table) {
                    $table->dropUnique(['string_id']);
                });
            } catch (\Throwable) {
            }
        }

        $cols = [
            'string_id', 'template_id', 'app_user_uid', 'caricature_ids', 'ratio',
            'width', 'height', 'thumbs', 'video', 'designs', 'msg', 'is_live',
        ];
        $conn = $this->connection;
        $toDrop = array_values(array_filter($cols, function ($col) use ($conn) {
            return Schema::connection($conn)->hasColumn('design_submissions', $col);
        }));
        if ($toDrop !== []) {
            $schema->table('design_submissions', function (Blueprint $table) use ($toDrop) {
                $table->dropColumn($toDrop);
            });
        }

        try {
            DB::connection($this->connection)->statement(
                'ALTER TABLE `design_submissions` MODIFY `design_file_path` VARCHAR(255) NOT NULL'
            );
        } catch (\Throwable) {
        }
    }
};
