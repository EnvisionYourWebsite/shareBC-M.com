<?php

use Illuminate\Database\Migrations\Migration;

class CreateGroupsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('groups'))
            return true;

        Schema::create('groups', function($table) {
            $table->engine = 'InnoDB';
            
            $table->increments('id')->unsigned();
            $table->string('name', 255)->unique();
            $table->text('permissions')->nullable();
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
        Schema::drop('groups');
    }

}