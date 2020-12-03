<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeliveryAttemptTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::statement(sprintf("ALTER TABLE %s ENGINE = 'InnoDB'", table('subscribers')));
        \DB::statement(sprintf("ALTER TABLE %s ENGINE = 'InnoDB'", table('auto_triggers')));
        \DB::statement(sprintf("ALTER TABLE %s ENGINE = 'InnoDB'", table('emails')));

        Schema::create('delivery_attempts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uid');
            $table->integer('subscriber_id')->unsigned();
            $table->integer('email_id')->unsigned();
            $table->integer('auto_trigger_id')->unsigned()->nullable();
            $table->timestamps();

            $table->foreign('subscriber_id')->references('id')->on('subscribers')->onDelete('cascade');
            $table->foreign('email_id')->references('id')->on('emails')->onDelete('cascade');
            $table->foreign('auto_trigger_id')->references('id')->on('auto_triggers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('delivery_attempts');
    }
}
