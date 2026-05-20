<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPriorityAndFrequencyToSpecialKeywords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('special_keywords', function (Blueprint $table) {
            if (!Schema::hasColumn('special_keywords', 'priority')) {
                $table->decimal('priority', 3, 2)->default(0.90)->after('status')->comment('Sitemap priority between 0.00 and 1.00');
            }
            if (!Schema::hasColumn('special_keywords', 'frequency')) {
                $table->enum('frequency', ['daily', 'weekly', 'monthly'])->default('daily')->after('priority')->comment('Sitemap change frequency');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('special_keywords', function (Blueprint $table) {
            if (Schema::hasColumn('special_keywords', 'priority')) {
                $table->dropColumn('priority');
            }
            if (Schema::hasColumn('special_keywords', 'frequency')) {
                $table->dropColumn('frequency');
            }
        });
    }
}
