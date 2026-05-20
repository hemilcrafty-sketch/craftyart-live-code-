<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'crafty_vendor_mysql';

    public function up(): void
    {
        $schema = Schema::connection($this->connection);

        if (!$schema->hasTable('wallet_settings')) {
            return;
        }

        if ($schema->hasColumn('wallet_settings', 'referral_commission_rate')) {
            return;
        }

        $schema->table('wallet_settings', function (Blueprint $table) {
            $table->decimal('referral_commission_rate', 5, 2)->default(10.00)->after('platform_commission_rate')->comment('Commission rate for referral users (affiliate)');
        });

        // Update existing records to set default referral commission rate
        $conn = DB::connection($this->connection);
        $conn->table('wallet_settings')
            ->whereNull('referral_commission_rate')
            ->orWhere('referral_commission_rate', 0)
            ->update(['referral_commission_rate' => 10.00]);
    }

    public function down(): void
    {
        $schema = Schema::connection($this->connection);

        if (!$schema->hasTable('wallet_settings')) {
            return;
        }

        if ($schema->hasColumn('wallet_settings', 'referral_commission_rate')) {
            $schema->table('wallet_settings', function (Blueprint $table) {
                $table->dropColumn('referral_commission_rate');
            });
        }
    }
};
