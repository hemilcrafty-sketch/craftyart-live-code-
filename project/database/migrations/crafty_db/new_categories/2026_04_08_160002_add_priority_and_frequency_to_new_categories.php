<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPriorityAndFrequencyToNewCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('new_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('new_categories', 'priority')) {
                $table->decimal('priority', 3, 2)->default(0.90)->after('status')->comment('Sitemap priority between 0.00 and 1.00');
            }
            if (!Schema::hasColumn('new_categories', 'frequency')) {
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
        Schema::table('new_categories', function (Blueprint $table) {
            if (Schema::hasColumn('new_categories', 'priority')) {
                $table->dropColumn('priority');
            }
            if (Schema::hasColumn('new_categories', 'frequency')) {
                $table->dropColumn('frequency');
            }
        });
    }
}
