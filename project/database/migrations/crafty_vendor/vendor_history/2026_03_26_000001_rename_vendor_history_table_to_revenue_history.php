<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'crafty_vendor_mysql';

    /**
     * Rename crafty_vendor.vendor_history → revenue_history.
     */
    public function up(): void
    {
        $schema = Schema::connection($this->connection);

        if ($schema->hasTable('revenue_history')) {
            return;
        }

        if (!$schema->hasTable('vendor_history')) {
            return;
        }

        $schema->rename('vendor_history', 'revenue_history');
    }

    public function down(): void
    {
        $schema = Schema::connection($this->connection);

        if ($schema->hasTable('vendor_history')) {
            return;
        }

        if (!$schema->hasTable('revenue_history')) {
            return;
        }

        $schema->rename('revenue_history', 'vendor_history');
    }
};
