<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('designs')) {
            return;
        }

        Schema::table('designs', function (Blueprint $table) {
            if (!Schema::hasColumn('designs', 'priority')) {
                $table->decimal('priority', 3, 2)->default(0.90)->after('status')
                    ->comment('Sitemap priority (0.00 to 1.00)');
            }
            if (!Schema::hasColumn('designs', 'frequency')) {
                $table->enum('frequency', ['daily', 'weekly', 'monthly'])->default('daily')->after('priority')
                    ->comment('Sitemap change frequency');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('designs')) {
            return;
        }

        Schema::table('designs', function (Blueprint $table) {
            if (Schema::hasColumn('designs', 'priority')) {
                $table->dropColumn('priority');
            }
            if (Schema::hasColumn('designs', 'frequency')) {
                $table->dropColumn('frequency');
            }
        });
    }
};
