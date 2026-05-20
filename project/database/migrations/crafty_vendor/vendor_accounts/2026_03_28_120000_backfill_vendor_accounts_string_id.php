<?php

use App\Http\Controllers\HelperController;
use App\Models\Vendor\VendorAccount;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    protected $connection = 'crafty_vendor_mysql';

    public function up(): void
    {
        $conn = DB::connection($this->connection);
        if (!$conn->getSchemaBuilder()->hasTable('vendor_accounts')) {
            return;
        }

        $ids = $conn->table('vendor_accounts')
            ->where(function ($q) {
                $q->whereNull('string_id')->orWhere('string_id', '');
            })
            ->pluck('id');

        foreach ($ids as $id) {
            $conn->table('vendor_accounts')->where('id', $id)->update([
                'string_id' => HelperController::generateStringIds(source: VendorAccount::class),
            ]);
        }
    }

    public function down(): void
    {
        // irreversible
    }
};
