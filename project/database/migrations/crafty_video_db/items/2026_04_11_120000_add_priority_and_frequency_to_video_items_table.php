<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPriorityAndFrequencyToVideoItemsTable extends Migration
{
    public function up(): void
    {
        Schema::connection('crafty_video_mysql')->table('items', function (Blueprint $table) {
            if (!Schema::connection('crafty_video_mysql')->hasColumn('items', 'priority')) {
                $table->decimal('priority', 4, 2)->default(0.90)->after('no_index');
            }
            if (!Schema::connection('crafty_video_mysql')->hasColumn('items', 'frequency')) {
                $table->string('frequency', 20)->default('daily')->after('priority');
            }
        });
    }
    
    public function down(): void
    {
        Schema::connection('crafty_video_mysql')->table('items', function (Blueprint $table) {
            if (Schema::connection('crafty_video_mysql')->hasColumn('items', 'frequency')) {
                $table->dropColumn('frequency');
            }
            if (Schema::connection('crafty_video_mysql')->hasColumn('items', 'priority')) {
                $table->dropColumn('priority');
            }
        });
    }
}
