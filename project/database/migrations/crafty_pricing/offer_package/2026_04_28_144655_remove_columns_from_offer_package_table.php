<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveColumnsFromOfferPackageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
        protected $connection = 'crafty_pricing_mysql';

    public function up()
    {
        $schema = Schema::connection($this->connection);

        if (!$schema->hasTable('offer_package')) {
            return;
        }

        Schema::connection($this->connection)->table('offer_package', function (Blueprint $table) use ($schema) {

            $columns = [];

            foreach (['slugs', 'urls', 'instructions'] as $col) {
                if ($schema->hasColumn('offer_package', $col)) {
                    $columns[] = $col;
                }
            }

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }

    public function down()
    {
        $schema = Schema::connection($this->connection);

        if (!$schema->hasTable('offer_package')) {
            return;
        }

        Schema::connection($this->connection)->table('offer_package', function (Blueprint $table) use ($schema) {

            if (!$schema->hasColumn('offer_package', 'slugs')) {
                $table->longText('slugs')->nullable();
            }

            if (!$schema->hasColumn('offer_package', 'urls')) {
                $table->longText('urls')->nullable();
            }

            if (!$schema->hasColumn('offer_package', 'instructions')) {
                $table->longText('instructions')->nullable();
            }
        });
    }

}
