<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'crafty_vendor_mysql';

    public function up(): void
    {
        $schema = Schema::connection($this->connection);

        if (! $schema->hasTable('vendor_history')) {
            return;
        }

        // Already migrated (fresh installs using updated create migration)
        if (! $schema->hasColumn('vendor_history', 'transaction_id')) {
            return;
        }

        if ($schema->hasColumn('vendor_history', 'purchase_id')) {
            return;
        }

        $vendorDb = config('database.connections.crafty_vendor_mysql.database');
        $revenueDb = config('database.connections.crafty_revenue_mysql.database');

        $schema->table('vendor_history', function (Blueprint $table) {
            $table->unsignedBigInteger('purchase_id')->nullable()->after('string_id');
            $table->string('payout_reference', 255)->nullable()->after('purchase_id');
        });

        // Preserve legacy string (gateway / payout ids) before dropping column
        DB::connection($this->connection)->update(
            "UPDATE `{$vendorDb}`.`vendor_history` SET `payout_reference` = `transaction_id` WHERE `transaction_id` IS NOT NULL AND `transaction_id` != ''"
        );

        // Map to crafty_revenue.purchase_history.id by payment transaction_id (non-withdraw rows)
        DB::connection($this->connection)->update("
            UPDATE `{$vendorDb}`.`vendor_history` vh
            INNER JOIN `{$revenueDb}`.`purchase_history` ph ON ph.transaction_id = vh.transaction_id
            SET vh.purchase_id = ph.id
            WHERE vh.type <> 'withdraw'
        ");

        // Legacy: transaction_id stored numeric purchase_history primary key
        DB::connection($this->connection)->update("
            UPDATE `{$vendorDb}`.`vendor_history` vh
            INNER JOIN `{$revenueDb}`.`purchase_history` ph ON ph.id = CAST(vh.transaction_id AS UNSIGNED)
            SET vh.purchase_id = ph.id
            WHERE vh.purchase_id IS NULL AND vh.type <> 'withdraw' AND vh.transaction_id REGEXP '^[0-9]+$'
        ");

        // Withdraw rows should not point at purchase_history
        DB::connection($this->connection)->update(
            "UPDATE `{$vendorDb}`.`vendor_history` SET `purchase_id` = NULL WHERE `type` = 'withdraw'"
        );

        $schema->table('vendor_history', function (Blueprint $table) {
            $table->dropIndex(['transaction_id']);
        });

        $schema->table('vendor_history', function (Blueprint $table) {
            $table->dropColumn('transaction_id');
        });

        $schema->table('vendor_history', function (Blueprint $table) {
            $table->index('purchase_id');
            $table->index('payout_reference');
        });

        $this->addCrossDatabaseForeignKey($vendorDb, $revenueDb);
    }

    /**
     * Optional FK — requires same MySQL server and compatible engines.
     */
    private function addCrossDatabaseForeignKey(string $vendorDb, string $revenueDb): void
    {
        try {
            DB::connection($this->connection)->statement("
                ALTER TABLE `{$vendorDb}`.`vendor_history`
                ADD CONSTRAINT `vendor_history_purchase_id_foreign`
                FOREIGN KEY (`purchase_id`) REFERENCES `{$revenueDb}`.`purchase_history` (`id`)
                ON DELETE SET NULL
            ");
        } catch (\Throwable $e) {
            Log::warning('vendor_history purchase_id foreign key not created (cross-DB or permissions).', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function down(): void
    {
        $schema = Schema::connection($this->connection);

        if (! $schema->hasTable('vendor_history')) {
            return;
        }

        if (! $schema->hasColumn('vendor_history', 'purchase_id')) {
            return;
        }

        $vendorDb = config('database.connections.crafty_vendor_mysql.database');

        try {
            DB::connection($this->connection)->statement(
                "ALTER TABLE `{$vendorDb}`.`vendor_history` DROP FOREIGN KEY `vendor_history_purchase_id_foreign`"
            );
        } catch (\Throwable $e) {
            // Constraint may not exist
        }

        if (! $schema->hasColumn('vendor_history', 'transaction_id')) {
            $schema->table('vendor_history', function (Blueprint $table) {
                $table->string('transaction_id')->nullable();
            });
        }

        // Restore best-effort string reference before dropping new columns
        DB::connection($this->connection)->update("
            UPDATE `{$vendorDb}`.`vendor_history`
            SET `transaction_id` = COALESCE(
                NULLIF(TRIM(`payout_reference`), ''),
                IF(`purchase_id` IS NULL, NULL, CAST(`purchase_id` AS CHAR))
            )
        ");

        try {
            $schema->table('vendor_history', function (Blueprint $table) {
                $table->dropIndex(['purchase_id']);
            });
        } catch (\Throwable $e) {
            //
        }

        try {
            $schema->table('vendor_history', function (Blueprint $table) {
                $table->dropIndex(['payout_reference']);
            });
        } catch (\Throwable $e) {
            //
        }

        $schema->table('vendor_history', function (Blueprint $table) {
            $table->dropColumn(['purchase_id', 'payout_reference']);
        });

        DB::connection($this->connection)->update(
            "UPDATE `{$vendorDb}`.`vendor_history` SET `transaction_id` = '' WHERE `transaction_id` IS NULL"
        );

        try {
            $schema->table('vendor_history', function (Blueprint $table) {
                $table->index('transaction_id');
            });
        } catch (\Throwable $e) {
            //
        }
    }
};
