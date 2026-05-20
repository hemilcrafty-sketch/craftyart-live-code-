<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private const CONNECTION = 'crafty_pricing_mysql';

    public function up(): void
    {
        $schema = Schema::connection(self::CONNECTION);

        if (! $schema->hasTable('offer_package')) {
            return;
        }

        Schema::connection(self::CONNECTION)->table('offer_package', function (Blueprint $table) use ($schema) {
            if (! $schema->hasColumn('offer_package', 'custom_days')) {
                $table->unsignedInteger('custom_days')->nullable()->after('duration_id');
            }
            if (! $schema->hasColumn('offer_package', 'slugs')) {
                $table->longText('slugs')->nullable()->after('package_name');
            }
            if (! $schema->hasColumn('offer_package', 'urls')) {
                $table->longText('urls')->nullable()->after('slugs');
            }
            if (! $schema->hasColumn('offer_package', 'instructions')) {
                $table->longText('instructions')->nullable()->after('plan_details');
            }
        });
    }

    public function down(): void
    {
        $schema = Schema::connection(self::CONNECTION);

        if (! $schema->hasTable('offer_package')) {
            return;
        }

        Schema::connection(self::CONNECTION)->table('offer_package', function (Blueprint $table) use ($schema) {
            $columnsToDrop = [];
            foreach (['custom_days', 'slugs', 'urls', 'instructions'] as $col) {
                if ($schema->hasColumn('offer_package', $col)) {
                    $columnsToDrop[] = $col;
                }
            }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
