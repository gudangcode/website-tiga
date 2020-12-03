<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForiegnKeysToSubscriptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Just in case of "Unknown database type enum requested, Doctrine\DBAL\Platforms\MySQL57Platform may not support it." error
        DB::connection()->getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');

        \Doctrine\DBAL\Types\Type::addType('uuid', 'Ramsey\Uuid\Doctrine\UuidType');

        DB::statement(sprintf('ALTER TABLE %s CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;', table('customers')));
        DB::statement(sprintf('ALTER TABLE %s MODIFY uid CHAR(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;', table('customers')));

        DB::statement(sprintf('ALTER TABLE %s CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;', table('plans')));
        DB::statement(sprintf('ALTER TABLE %s MODIFY uid CHAR(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;', table('plans')));

        DB::statement(sprintf('ALTER TABLE %s CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;', table('subscriptions')));
        DB::statement(sprintf('ALTER TABLE %s MODIFY plan_id CHAR(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;', table('subscriptions')));
        DB::statement(sprintf('ALTER TABLE %s MODIFY user_id CHAR(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;', table('subscriptions')));

        DB::statement(sprintf("ALTER TABLE %s ENGINE = 'InnoDB'", table('subscriptions')));
        DB::statement(sprintf("ALTER TABLE %s ENGINE = 'InnoDB'", table('customers')));
        DB::statement(sprintf("ALTER TABLE %s ENGINE = 'InnoDB'", table('plans')));

        Schema::table('plans', function ($table) {
            $table->collation = 'utf8mb4_unicode_ci';
            $table->charset = 'utf8mb4';
            $table->uuid('uid')->collation('utf8mb4_unicode_ci')->charset('utf8mb4')->change();
            $table->index('uid');
        });

        Schema::table('customers', function ($table) {            
            $table->collation = 'utf8mb4_unicode_ci';
            $table->charset = 'utf8mb4';
            $table->uuid('uid')->collation('utf8mb4_unicode_ci')->charset('utf8mb4')->change();
            $table->index('uid');
        });

        \Acelle\Cashier\Subscription::whereRaw(sprintf('(%s NOT IN (SELECT uid FROM %s) OR %s NOT IN (SELECT uid FROM %s))', 'user_id', table('customers'), 'plan_id', table('plans')))->delete();

        Schema::table('subscriptions', function ($table) {
            $table->collation = 'utf8mb4_unicode_ci';
            $table->charset = 'utf8mb4';
            $table->uuid('user_id')->collation('utf8mb4_unicode_ci')->charset('utf8mb4')->change();
            $table->uuid('plan_id')->collation('utf8mb4_unicode_ci')->charset('utf8mb4')->change();

            $table->foreign('plan_id')->references('uid')->on('plans')->onDelete('cascade');
            $table->foreign('user_id')->references('uid')->on('customers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            //
        });
    }
}
