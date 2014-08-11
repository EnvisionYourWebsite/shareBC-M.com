<?php

use Illuminate\Database\Migrations\Migration;

class CreateOptionsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('options'))
            return true;

        Schema::create('options', function($table) {
            $table->engine = 'InnoDB';
            
            $table->increments('id')->unsigned();
            $table->string('name', 50);
            $table->text('value');
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
        Schema::drop('options');
    }

}