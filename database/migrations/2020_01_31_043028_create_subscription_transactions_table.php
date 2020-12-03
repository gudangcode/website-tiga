<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubscriptionTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Clean up all subscriptions!!!!!
        \Acelle\Cashier\Subscription::whereRaw('true')->delete();

        DB::statement(sprintf('ALTER TABLE %s CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;', table('subscriptions')));
        DB::statement(sprintf("ALTER TABLE %s ENGINE = 'InnoDB'", table('subscriptions')));

        Schema::create('subscription_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('uid');
            $table->integer('subscription_id')->unsigned();
            $table->string('type');
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('current_period_ends_at')->nullable();
            $table->string('status');
            $table->string('description');
            $table->string('title');
            $table->string('amount');
            $table->text('metadata');

            $table->timestamps();

            $table->foreign('subscription_id')->references('id')->on('subscriptions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscription_transactions');
    }
}
