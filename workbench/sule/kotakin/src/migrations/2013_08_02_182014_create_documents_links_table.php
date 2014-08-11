<?php

use Illuminate\Database\Migrations\Migration;

class CreateDocumentslinksTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('documents_links'))
            return true;
        
        Schema::create('documents_links', function($table) {
            $table->engine = 'InnoDB';
            
            $table->bigIncrements('id')->unsigned();
            $table->bigInteger('document_id')->unsigned();
            $table->bigInteger('author_id')->unsigned();
            $table->string('slug', 255);
            $table->text('description');
            $table->string('password', 255)->nullable();
            $table->integer('limit')->default("-1");
            $table->timestamp('valid_until')->default("0000-00-00 00:00:00");
            $table->bigInteger('downloaded_times');
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
        Schema::drop('documents_links');
    }

}