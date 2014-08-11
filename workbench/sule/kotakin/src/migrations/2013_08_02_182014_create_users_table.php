<?php

use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('users'))
            return true;

        Schema::create('users', function($table) {
            $table->engine = 'InnoDB';
            
            $table->increments('id')->unsigned();
            $table->string('email', 255)->unique();
            $table->string('password', 255);
            $table->text('permissions')->nullable();
            $table->boolean('activated');
            $table->string('activation_code', 255)->nullable()->index();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('last_login')->nullable();
            $table->string('persist_code', 255)->nullable();
            $table->string('reset_password_code', 255)->nullable()->index();
            $table->string('url_slug', 50);
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
        Schema::drop('users');
    }

}