<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Optional: links crafty_db.designs row to crafty_db.user_data.id for freelancer templates.
 * Publish works without this column; run migrate to store designer_id on designs.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('designs')) {
            return;
        }

        Schema::table('designs', function (Blueprint $table) {
            if (! Schema::hasColumn('designs', 'designer_id')) {
                $table->unsignedBigInteger('designer_id')->nullable()->after('emp_id')
                    ->comment('user_data.id for freelancer-submitted templates');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('designs') || ! Schema::hasColumn('designs', 'designer_id')) {
            return;
        }

        Schema::table('designs', function (Blueprint $table) {
            $table->dropColumn('designer_id');
        });
    }
};
