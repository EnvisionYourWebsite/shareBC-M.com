<?php

use Illuminate\Database\Migrations\Migration;

class AlterUserUploadFoldersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( ! Schema::hasColumn('folders', 'user_upload')) {
            Schema::table('folders', function($table) {
                $table->boolean('user_upload')->default(1)->after('is_shared');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('folders', function($table) {
            $table->dropColumn('user_upload');
        });

    }

}