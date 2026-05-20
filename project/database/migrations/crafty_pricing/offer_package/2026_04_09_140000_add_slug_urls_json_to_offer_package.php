<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    private const CONNECTION = 'crafty_pricing_mysql';

    public function up(): void
    {
        $schema = Schema::connection(self::CONNECTION);

        if (! $schema->hasTable('offer_package')) {
            return;
        }

        Schema::connection(self::CONNECTION)->table('offer_package', function (Blueprint $table) use ($schema) {
            if (! $schema->hasColumn('offer_package', 'slug_urls')) {
                $table->json('slug_urls')->nullable()->after('url');
            }
        });

        // Backfill from legacy slug/url
        if ($schema->hasColumn('offer_package', 'slug_urls')) {
            $conn = DB::connection(self::CONNECTION);
            $conn->table('offer_package')
                ->orderBy('id')
                ->chunkById(100, function ($rows) use ($conn) {
                    foreach ($rows as $row) {
                        $existing = $row->slug_urls ?? null;
                        if ($existing !== null && $existing !== '' && $existing !== '[]') {
                            continue;
                        }
                        $slug = isset($row->slug) ? trim((string) $row->slug) : '';
                        $url = $row->url ?? null;
                        $url = $url !== null && $url !== '' ? trim((string) $url) : null;
                        if ($slug === '' && $url === null) {
                            continue;
                        }
                        $norm = Str::slug($slug);
                        if ($norm === '') {
                            continue;
                        }
                        $pair = [
                            'slug' => $norm,
                            'url'  => $url,
                        ];
                        $conn->table('offer_package')->where('id', $row->id)->update([
                            'slug_urls' => json_encode([$pair]),
                        ]);
                    }
                });
        }
    }

    public function down(): void
    {
        $schema = Schema::connection(self::CONNECTION);

        if (! $schema->hasTable('offer_package')) {
            return;
        }

        Schema::connection(self::CONNECTION)->table('offer_package', function (Blueprint $table) use ($schema) {
            if ($schema->hasColumn('offer_package', 'slug_urls')) {
                $table->dropColumn('slug_urls');
            }
        });
    }
};