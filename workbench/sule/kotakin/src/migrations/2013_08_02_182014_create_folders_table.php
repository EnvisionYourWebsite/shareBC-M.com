<?php

use Illuminate\Database\Migrations\Migration;

class CreateFoldersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('folders'))
            return true;

        Schema::create('folders', function($table) {
            $table->engine = 'InnoDB';
            
            $table->bigIncrements('id')->unsigned();
            $table->bigInteger('parent_id')->unsigned();
            $table->string('type', 32);
            $table->string('name', 255);
            $table->string('slug', 255);
            $table->text('description');
            $table->string('password', 255);
            $table->boolean('is_default');
            $table->boolean('is_shared');
            $table->boolean('user_upload');
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
        Schema::drop('folders');
    }

}