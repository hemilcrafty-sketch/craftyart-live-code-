<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Backfill purchase_user_id from crafty_revenue purchase_history.user_id where purchase_id is set.
 */
return new class extends Migration {
    protected $connection = 'crafty_vendor_mysql';

    public function up(): void
    {
        $vendorConn = $this->connection;
        $revenueConn = 'crafty_revenue_mysql';

        if (! Schema::connection($vendorConn)->hasTable('vendor_history')) {
            return;
        }

        if (! Schema::connection($vendorConn)->hasColumn('vendor_history', 'purchase_user_id')) {
            return;
        }

        if (! Schema::connection($revenueConn)->hasTable('purchase_history')) {
            Log::info('backfill purchase_user_id skipped: purchase_history not found on revenue connection');

            return;
        }

        $vendorDb = Config::get("database.connections.{$vendorConn}.database");
        $revenueDb = Config::get("database.connections.{$revenueConn}.database");

        if (! $vendorDb || ! $revenueDb) {
            return;
        }

        try {
            DB::statement("
                UPDATE `{$vendorDb}`.`vendor_history` vh
                INNER JOIN `{$revenueDb}`.`purchase_history` ph ON ph.id = vh.purchase_id
                SET vh.purchase_user_id = ph.user_id
                WHERE (vh.purchase_user_id IS NULL OR vh.purchase_user_id = '')
                  AND vh.purchase_id IS NOT NULL
                  AND ph.user_id IS NOT NULL
                  AND ph.user_id != ''
            ");
        } catch (\Throwable $e) {
            Log::warning('vendor_history purchase_user_id backfill failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function down(): void
    {
        // Non-reversible data fix
    }
};
