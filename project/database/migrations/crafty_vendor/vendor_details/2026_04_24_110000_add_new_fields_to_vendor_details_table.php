<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFieldsToVendorDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('crafty_vendor_mysql')->table('vendor_details', function (Blueprint $table) {
            if (!Schema::connection('crafty_vendor_mysql')->hasColumn('vendor_details', 'title')) {
                $table->string('title')->nullable()->after('user_id');
            }
            if (!Schema::connection('crafty_vendor_mysql')->hasColumn('vendor_details', 'owner_name')) {
                $table->string('owner_name')->nullable()->after('brand_name');
            }
            if (!Schema::connection('crafty_vendor_mysql')->hasColumn('vendor_details', 'email')) {
                $table->string('email')->nullable()->after('owner_name');
            }
            if (!Schema::connection('crafty_vendor_mysql')->hasColumn('vendor_details', 'instagram_url')) {
                $table->string('instagram_url')->nullable()->after('email');
            }
            if (!Schema::connection('crafty_vendor_mysql')->hasColumn('vendor_details', 'facebook_url')) {
                $table->string('facebook_url')->nullable()->after('instagram_url');
            }
            if (!Schema::connection('crafty_vendor_mysql')->hasColumn('vendor_details', 'website_url')) {
                $table->string('website_url')->nullable()->after('facebook_url');
            }
            if (!Schema::connection('crafty_vendor_mysql')->hasColumn('vendor_details', 'business_logo')) {
                $table->string('business_logo')->nullable()->after('website_url');
            }
            if (!Schema::connection('crafty_vendor_mysql')->hasColumn('vendor_details', 'owner_profile_photo')) {
                $table->string('owner_profile_photo')->nullable()->after('business_logo');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('crafty_vendor_mysql')->table('vendor_details', function (Blueprint $table) {
            $table->dropColumn([
                'title',
                'owner_name',
                'email',
                'instagram_url',
                'facebook_url',
                'website_url',
                'business_logo',
                'owner_profile_photo'
            ]);
        });
    }
}
