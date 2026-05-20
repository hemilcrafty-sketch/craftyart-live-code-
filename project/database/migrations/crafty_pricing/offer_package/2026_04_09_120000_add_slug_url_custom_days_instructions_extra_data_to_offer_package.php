<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private const CONNECTION = 'crafty_pricing_mysql';

    public function up(): void
    {
        $schema = Schema::connection(self::CONNECTION);

        if (!$schema->hasTable('offer_package')) {
            return;
        }

        Schema::connection(self::CONNECTION)->table('offer_package', function (Blueprint $table) use ($schema) {
            if (!$schema->hasColumn('offer_package', 'slug')) {
                $table->string('slug', 255)->nullable()->unique('offer_package_slug_unique')->after('package_name');
            }
            if (!$schema->hasColumn('offer_package', 'url')) {
                $table->string('url', 2048)->nullable()->after('slug');
            }
            if (!$schema->hasColumn('offer_package', 'custom_days')) {
                $table->unsignedInteger('custom_days')->nullable()->after('duration_id');
            }
            if (!$schema->hasColumn('offer_package', 'instructions')) {
                $table->json('instructions')->nullable()->after('plan_details');
            }
            if (!$schema->hasColumn('offer_package', 'extra_data')) {
                $table->json('extra_data')->nullable()->after('instructions');
            }
        });
    }

    public function down(): void
    {
        $schema = Schema::connection(self::CONNECTION);

        if (!$schema->hasTable('offer_package')) {
            return;
        }

        Schema::connection(self::CONNECTION)->table('offer_package', function (Blueprint $table) use ($schema) {
            if ($schema->hasColumn('offer_package', 'slug')) {
                try {
                    $table->dropUnique('offer_package_slug_unique');
                } catch (\Throwable) {
                }
            }
            foreach (['extra_data', 'instructions', 'custom_days', 'url', 'slug'] as $col) {
                if ($schema->hasColumn('offer_package', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};