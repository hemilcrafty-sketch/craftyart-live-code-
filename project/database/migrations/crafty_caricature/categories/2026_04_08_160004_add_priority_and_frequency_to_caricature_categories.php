<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPriorityAndFrequencyToCaricatureCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('crafty_caricature_mysql')->table('categories', function (Blueprint $table) {
            if (!Schema::connection('crafty_caricature_mysql')->hasColumn('categories', 'priority')) {
                $table->decimal('priority', 3, 2)->default(0.90)->after('status')->comment('Sitemap priority between 0.00 and 1.00');
            }
            if (!Schema::connection('crafty_caricature_mysql')->hasColumn('categories', 'frequency')) {
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
        Schema::connection('crafty_caricature_mysql')->table('categories', function (Blueprint $table) {
            if (Schema::connection('crafty_caricature_mysql')->hasColumn('categories', 'priority')) {
                $table->dropColumn('priority');
            }
            if (Schema::connection('crafty_caricature_mysql')->hasColumn('categories', 'frequency')) {
                $table->dropColumn('frequency');
            }
        });
    }
}
