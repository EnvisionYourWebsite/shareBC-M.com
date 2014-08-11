<?php

use Illuminate\Database\Migrations\Migration;

class CreateMediasTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('medias'))
            return true;

        Schema::create('medias', function($table) {
            $table->engine = 'InnoDB';
            
            $table->bigIncrements('id')->unsigned();
            $table->bigInteger('parent_id')->unsigned();
            $table->bigInteger('object_id')->unsigned();
            $table->string('object_type', 100);
            $table->bigInteger('author_id')->unsigned();
            $table->string('type', 10);
            $table->string('title', 150);
            $table->string('alt_text', 255);
            $table->string('path', 255);
            $table->string('filename', 255);
            $table->string('extension', 5);
            $table->string('mime_type', 50);
            $table->string('size', 10);
            $table->longtext('metadata');
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
        Schema::drop('medias');
    }

}