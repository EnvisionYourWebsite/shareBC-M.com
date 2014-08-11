<?php

use Illuminate\Database\Migrations\Migration;

class CreateThrottleTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('throttle'))
            return true;

        Schema::create('throttle', function($table) {
            $table->engine = 'InnoDB';
            
            $table->increments('id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->string('ip_address', 255)->nullable();
            $table->integer('attempts');
            $table->boolean('suspended');
            $table->boolean('banned');
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->timestamp('banned_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('throttle');
    }

}