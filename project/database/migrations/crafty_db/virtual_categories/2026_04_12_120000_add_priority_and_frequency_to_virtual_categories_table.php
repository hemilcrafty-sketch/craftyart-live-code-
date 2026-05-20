<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::connection('mysql')->hasTable('virtual_categories')) {
            return;
        }

        Schema::connection('mysql')->table('virtual_categories', function (Blueprint $table) {
            if (! Schema::connection('mysql')->hasColumn('virtual_categories', 'priority')) {
                $table->decimal('priority', 4, 2)->default(0.90)->after('no_index');
            }
            if (! Schema::connection('mysql')->hasColumn('virtual_categories', 'frequency')) {
                $table->string('frequency', 20)->default('daily')->after('priority');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::connection('mysql')->hasTable('virtual_categories')) {
            return;
        }

        Schema::connection('mysql')->table('virtual_categories', function (Blueprint $table) {
            if (Schema::connection('mysql')->hasColumn('virtual_categories', 'frequency')) {
                $table->dropColumn('frequency');
            }
            if (Schema::connection('mysql')->hasColumn('virtual_categories', 'priority')) {
                $table->dropColumn('priority');
            }
        });
    }
};
 