<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'crafty_creator_mysql';

    public function up(): void
    {
        $this->createDatabaseIfNotExists();

        $schema = Schema::connection($this->connection);

        if ($schema->hasTable('designer_applications')) {
            return;
        }

        $schema->create('designer_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('app_user_uid')->nullable();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 15);
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->default('India');
            $table->text('experience')->nullable();
            $table->text('skills')->nullable();
            $table->json('portfolio_links')->nullable();
            $table->json('uploaded_samples')->nullable();
            $table->json('selected_types')->nullable();
            $table->json('selected_categories')->nullable();
            $table->json('selected_goals')->nullable();
            $table->string('experience_level', 50)->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['email', 'status']);
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('designer_applications');
    }

    private function createDatabaseIfNotExists(): void
    {
        $database = env('CRAFTY_CREATOR_DB_DATABASE', 'crafty_creator');
        $charset = config('database.connections.crafty_creator_mysql.charset', 'utf8mb4');
        $collation = config('database.connections.crafty_creator_mysql.collation', 'utf8mb4_unicode_ci');

        $query = "CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET {$charset} COLLATE {$collation}";

        try {
            DB::connection('mysql')->statement($query);
        } catch (\Exception $e) {
            // Continue if DB exists or permission denied
        }
    }
};
