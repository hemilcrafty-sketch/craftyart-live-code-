<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Refactor design_submissions to align with designer_drafts structure:
 * - Remove: category, design_file_path, preview_images
 * - Rename: app_user_uid -> user_id
 * - Keep JSON fields: designs, thumbs, caricature_ids (already aligned)
 */
return new class extends Migration {
  protected $connection = 'crafty_creator_mysql';

  public function up(): void
  {
    $schema = Schema::connection($this->connection);

    if (!$schema->hasTable('design_submissions')) {
      return;
    }

    // Step 1: Add new user_id column
    if (!$schema->hasColumn('design_submissions', 'user_id')) {
      $schema->table('design_submissions', function (Blueprint $table) {
        $table->string('user_id', 64)->nullable()->after('designer_id')
          ->comment('App user uid (mirrors designer_drafts.user_id)');
      });
    }

    // Step 2: Copy data from app_user_uid to user_id
    if ($schema->hasColumn('design_submissions', 'app_user_uid')) {
      DB::connection($this->connection)->statement(
        'UPDATE design_submissions SET user_id = app_user_uid WHERE app_user_uid IS NOT NULL'
      );
    }

    // Step 3: Drop old columns
    $columnsToDrop = [];
    if ($schema->hasColumn('design_submissions', 'app_user_uid')) {
      $columnsToDrop[] = 'app_user_uid';
    }
    if ($schema->hasColumn('design_submissions', 'category')) {
      $columnsToDrop[] = 'category';
    }
    if ($schema->hasColumn('design_submissions', 'design_file_path')) {
      $columnsToDrop[] = 'design_file_path';
    }
    if ($schema->hasColumn('design_submissions', 'preview_images')) {
      $columnsToDrop[] = 'preview_images';
    }

    if (!empty($columnsToDrop)) {
      $schema->table('design_submissions', function (Blueprint $table) use ($columnsToDrop) {
        $table->dropColumn($columnsToDrop);
      });
    }
  }

  public function down(): void
  {
    $schema = Schema::connection($this->connection);

    if (!$schema->hasTable('design_submissions')) {
      return;
    }

    // Restore removed columns
    $schema->table('design_submissions', function (Blueprint $table) {
      if (!Schema::connection($this->connection)->hasColumn('design_submissions', 'app_user_uid')) {
        $table->string('app_user_uid', 64)->nullable()->after('designer_id');
      }
      if (!Schema::connection($this->connection)->hasColumn('design_submissions', 'category')) {
        $table->string('category')->nullable()->after('description');
      }
      if (!Schema::connection($this->connection)->hasColumn('design_submissions', 'design_file_path')) {
        $table->string('design_file_path')->nullable()->after('category_id');
      }
      if (!Schema::connection($this->connection)->hasColumn('design_submissions', 'preview_images')) {
        $table->json('preview_images')->nullable()->after('design_file_path');
      }
    });

    // Copy data back from user_id to app_user_uid
    if ($schema->hasColumn('design_submissions', 'user_id')) {
      DB::connection($this->connection)->statement(
        'UPDATE design_submissions SET app_user_uid = user_id WHERE user_id IS NOT NULL'
      );
    }

    // Drop user_id column
    if ($schema->hasColumn('design_submissions', 'user_id')) {
      $schema->table('design_submissions', function (Blueprint $table) {
        $table->dropColumn('user_id');
      });
    }
  }
};
