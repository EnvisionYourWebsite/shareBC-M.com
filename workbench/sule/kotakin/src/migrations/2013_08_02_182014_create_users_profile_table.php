<?php

use Illuminate\Database\Migrations\Migration;

class CreateUsersprofileTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('users_profile'))
            return true;

        Schema::create('users_profile', function($table) {
            $table->engine = 'InnoDB';
            
            $table->increments('id')->unsigned();
            $table->integer('user_id')->index()->unsigned();
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->string('display_name', 100);
            $table->string('phone', 20)->nullable();
            $table->string('date_format', 50)->default("Y/m/d H:i A");
            $table->integer('max_upload_size');
            $table->text('allowed_file_types');
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
        Schema::drop('users_profile');
    }

}