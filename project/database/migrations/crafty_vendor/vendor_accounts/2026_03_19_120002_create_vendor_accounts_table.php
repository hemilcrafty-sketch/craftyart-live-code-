<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'crafty_vendor_mysql';

    public function up(): void
    {
        $schema = Schema::connection($this->connection);

        if ($schema->hasTable('vendor_accounts')) {
            return;
        }

        $schema->create('vendor_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('string_id');
            $table->string('user_id');
            $table->string('contact_id');
            $table->string('status');
            $table->timestamps();

            $table->index('user_id');
            $table->index('string_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('vendor_accounts');
    }
};
