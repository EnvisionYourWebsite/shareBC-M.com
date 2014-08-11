<?php

use Illuminate\Database\Migrations\Migration;

class CreateUsersgroupsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('users_groups'))
            return true;

        Schema::create('users_groups', function($table) {
            $table->engine = 'InnoDB';
            
            $table->bigInteger('user_id')->unsigned();
            $table->integer('group_id')->unsigned();
            $table->primary(array('user_id', 'group_id'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('users_groups');
    }

}