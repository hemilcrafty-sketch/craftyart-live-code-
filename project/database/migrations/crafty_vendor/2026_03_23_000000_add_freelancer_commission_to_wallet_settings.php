<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   * 
   * Adds freelancer_commission_rate field to wallet_settings table
   * Renames platform_commission_rate to platform_commission_rate (keeping same name for backward compatibility)
   * 
   * New logic:
   * - Purchase Amount: ₹1000
   * - Platform Commission (30%): ₹300
   * - Freelancer Commission (30%): ₹300
   * - Remaining (40%): ₹400 (goes to platform/other costs)
   */
  public function up(): void
  {
    Schema::connection('crafty_vendor_mysql')->table('wallet_settings', function (Blueprint $table) {
      // Add freelancer commission rate field
      $table->decimal('freelancer_commission_rate', 5, 2)->default(30.00)->after('platform_commission_rate')
        ->comment('Commission rate for freelancer designers (e.g., 30.00 = 30%)');
    });

    // Update existing records to set freelancer_commission_rate = 30%
    DB::connection('crafty_vendor_mysql')->table('wallet_settings')
      ->update(['freelancer_commission_rate' => 30.00]);
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::connection('crafty_vendor_mysql')->table('wallet_settings', function (Blueprint $table) {
      $table->dropColumn('freelancer_commission_rate');
    });
  }
};
