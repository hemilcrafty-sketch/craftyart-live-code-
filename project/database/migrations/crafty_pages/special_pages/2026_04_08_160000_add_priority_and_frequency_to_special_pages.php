<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPriorityAndFrequencyToSpecialPages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('special_page_mysql')->table('special_pages', function (Blueprint $table) {
            if (!Schema::connection('special_page_mysql')->hasColumn('special_pages', 'priority')) {
                $table->decimal('priority', 3, 2)->default(0.90)->after('status')->comment('Sitemap priority between 0.00 and 1.00');
            }
            if (!Schema::connection('special_page_mysql')->hasColumn('special_pages', 'frequency')) {
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
        Schema::connection('special_page_mysql')->table('special_pages', function (Blueprint $table) {
            if (Schema::connection('special_page_mysql')->hasColumn('special_pages', 'priority')) {
                $table->dropColumn('priority');
            }
            if (Schema::connection('special_page_mysql')->hasColumn('special_pages', 'frequency')) {
                $table->dropColumn('frequency');
            }
        });
    }
}
