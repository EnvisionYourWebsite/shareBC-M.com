<?php

use Illuminate\Database\Migrations\Migration;

class CreateEmailtemplatesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('email_templates'))
            return true;

        Schema::create('email_templates', function($table) {
            $table->engine = 'InnoDB';
            
            $table->increments('id')->unsigned();
            $table->string('identifier', 50);
            $table->string('subject', 100);
            $table->longtext('content_html');
            $table->text('content_plain');
            $table->string('note', 255)->nullable();
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
        Schema::drop('email_templates');
    }

}