<?php

use Illuminate\Database\Migrations\Migration;

class CreateEmailrecipientsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('email_recipients'))
            return true;

        Schema::create('email_recipients', function($table) {
            $table->engine = 'InnoDB';
            
            $table->bigIncrements('id')->unsigned();
            $table->bigInteger('from_user_id')->unsigned();
            $table->bigInteger('to_user_id')->unsigned();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('email_recipients');
    }

}