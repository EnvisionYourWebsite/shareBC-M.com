<?php

use Illuminate\Database\Migrations\Migration;

class CreateTermsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('terms'))
            return true;

        Schema::create('terms', function($table) {
            $table->engine = 'InnoDB';
            
            $table->bigIncrements('id')->unsigned();
            $table->bigInteger('parent_id')->unsigned();
            $table->bigInteger('object_id')->unsigned();
            $table->bigInteger('author_id')->unsigned();
            $table->string('name', 255);
            $table->boolean('is_folder');
            $table->boolean('is_file');
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
        Schema::drop('terms');
    }

}