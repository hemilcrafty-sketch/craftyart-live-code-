<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Drop cmd_tables - unused in project (VideoCmd model not referenced anywhere).
     */
    public function up(): void
    {
        Schema::connection('crafty_video_mysql')->dropIfExists('cmd_tables');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Table recreation handled by original create migration if needed
    }
};
