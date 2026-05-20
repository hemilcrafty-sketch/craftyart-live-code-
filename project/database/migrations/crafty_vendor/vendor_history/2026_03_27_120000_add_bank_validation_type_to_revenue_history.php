<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'crafty_vendor_mysql';

    public function up(): void
    {
        $schema = Schema::connection($this->connection);
        
        // After rename, table is revenue_history
        if (!$schema->hasTable('revenue_history')) {
            return;
        }

        $db = DB::connection($this->connection)->getDatabaseName();
        DB::connection($this->connection)->statement(
            "ALTER TABLE `{$db}`.`revenue_history` MODIFY COLUMN `type` ENUM(
                'old_sub','new_sub','template','caricature','video','withdraw','bank_validation'
            ) NOT NULL"
        );
    }

    public function down(): void
    {
        $schema = Schema::connection($this->connection);
        
        if (!$schema->hasTable('revenue_history')) {
            return;
        }

        DB::connection($this->connection)->table('revenue_history')
            ->where('type', 'bank_validation')
            ->delete();

        $db = DB::connection($this->connection)->getDatabaseName();
        DB::connection($this->connection)->statement(
            "ALTER TABLE `{$db}`.`revenue_history` MODIFY COLUMN `type` ENUM(
                'old_sub','new_sub','template','caricature','video','withdraw'
            ) NOT NULL"
        );
    }
};
