<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsShowAddonToOfferPagesTable extends Migration
{
    protected $connection = 'crafty_pricing_mysql';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $schema = Schema::connection($this->connection);

        if (!$schema->hasTable('offer_pages')) {
            return;
        }

        if (!$schema->hasColumn('offer_pages', 'is_show_addon')) {
            $schema->table('offer_pages', function (Blueprint $table) {
                $table->tinyInteger('is_show_addon')->default(0)->after('instructions');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $schema = Schema::connection($this->connection);

        if ($schema->hasTable('offer_pages') && $schema->hasColumn('offer_pages', 'is_show_addon')) {
            $schema->table('offer_pages', function (Blueprint $table) {
                $table->dropColumn('is_show_addon');
            });
        }
    }
}
