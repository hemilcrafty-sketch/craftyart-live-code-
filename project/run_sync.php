<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Starting Unified Support Sync...\n";

try {
    \App\Helpers\UnifiedSupportSyncHelper::syncAllData();
    echo "Sync completed successfully!\n";
} catch (\Exception $e) {
    echo "Error during sync: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
