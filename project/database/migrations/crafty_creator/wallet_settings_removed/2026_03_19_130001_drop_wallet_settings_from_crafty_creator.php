<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * wallet_settings now lives on crafty_vendor (see crafty_vendor/wallet_settings migrations).
 * Copies any existing rows from crafty_creator before dropping (payment_type defaults to manual).
 */
return new class extends Migration {
    protected $connection = 'crafty_creator_mysql';

    public function up(): void
    {
        $creatorSchema = Schema::connection('crafty_creator_mysql');

        if (!$creatorSchema->hasTable('wallet_settings')) {
            return;
        }

        $vendor = DB::connection('crafty_vendor_mysql');
        if (!Schema::connection('crafty_vendor_mysql')->hasTable('wallet_settings')) {
            Schema::connection('crafty_creator_mysql')->dropIfExists('wallet_settings');

            return;
        }

        $rows = DB::connection('crafty_creator_mysql')->table('wallet_settings')->get();

        foreach ($rows as $row) {
            if ($vendor->table('wallet_settings')->where('setting_key', $row->setting_key)->exists()) {
                continue;
            }

            $vendor->table('wallet_settings')->insert([
                'setting_key' => $row->setting_key,
                'setting_name' => $row->setting_name ?? null,
                'description' => $row->description ?? null,
                'min_withdrawal_threshold' => $row->min_withdrawal_threshold ?? 500,
                'max_withdrawal_limit' => $row->max_withdrawal_limit ?? null,
                'platform_commission_rate' => $row->platform_commission_rate ?? 30,
                'payment_type' => 'manual',
                'is_active' => (bool) ($row->is_active ?? true),
                'created_at' => $row->created_at ?? now(),
                'updated_at' => $row->updated_at ?? now(),
            ]);
        }

        Schema::connection('crafty_creator_mysql')->dropIfExists('wallet_settings');
    }

    public function down(): void
    {
        // Intentionally empty: table definition moved to crafty_vendor.
    }
};
