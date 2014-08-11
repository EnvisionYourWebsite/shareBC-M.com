<?php

use Illuminate\Database\Migrations\Migration;

class CreateTermssharingsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('terms_sharings'))
            return true;

        Schema::create('terms_sharings', function($table) {
            $table->engine = 'InnoDB';
            
            $table->bigIncrements('id')->unsigned();
            $table->bigInteger('term_id')->unsigned();
            $table->bigInteger('user_id')->unsigned();
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
        Schema::drop('terms_sharings');
    }

}