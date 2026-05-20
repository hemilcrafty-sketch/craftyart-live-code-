<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update vendor_template table
        Schema::connection('crafty_vendor_mysql')->table('vendor_template', function (Blueprint $table) {
            if (!Schema::connection('crafty_vendor_mysql')->hasColumn('vendor_template', 'slug')) {
                $table->string('slug')->nullable()->after('template_id')->unique();
            }
            if (!Schema::connection('crafty_vendor_mysql')->hasColumn('vendor_template', 'title')) {
                $table->string('title')->nullable()->after('currency');
            }
            if (!Schema::connection('crafty_vendor_mysql')->hasColumn('vendor_template', 'description')) {
                $table->text('description')->nullable()->after('title');
            }
            if (!Schema::connection('crafty_vendor_mysql')->hasColumn('vendor_template', 'long_description')) {
                $table->text('long_description')->nullable()->after('description');
            }
            if (!Schema::connection('crafty_vendor_mysql')->hasColumn('vendor_template', 'is_premium')) {
                $table->boolean('is_premium')->default(0)->after('long_description');
            }
            if (!Schema::connection('crafty_vendor_mysql')->hasColumn('vendor_template', 'no_index')) {
                $table->boolean('no_index')->default(0)->after('is_premium');
            }
            if (!Schema::connection('crafty_vendor_mysql')->hasColumn('vendor_template', 'status')) {
                $table->boolean('status')->default(1)->after('no_index');
            }
        });

        // Update vendor_details table
        Schema::connection('crafty_vendor_mysql')->table('vendor_details', function (Blueprint $table) {
            if (!Schema::connection('crafty_vendor_mysql')->hasColumn('vendor_details', 'meta_title')) {
                $table->string('meta_title')->nullable()->after('owner_profile_photo');
            }
            if (!Schema::connection('crafty_vendor_mysql')->hasColumn('vendor_details', 'meta_description')) {
                $table->text('meta_description')->nullable()->after('meta_title');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('crafty_vendor_mysql')->table('vendor_template', function (Blueprint $table) {
            $table->dropColumn(['slug', 'title', 'description', 'long_description', 'is_premium', 'no_index', 'status']);
        });

        Schema::connection('crafty_vendor_mysql')->table('vendor_details', function (Blueprint $table) {
            $table->dropColumn(['meta_title', 'meta_description']);
        });
    }
};
