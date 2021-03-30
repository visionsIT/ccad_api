<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateUserEcardsSendDatetimeDatatype extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_ecards', function (Blueprint $table) {

            // $table->dateTime('send_datetime')->change()->nullable();
            DB::statement('ALTER TABLE users_ecards CHANGE send_datetime send_datetime DATETIME NULL DEFAULT NULL');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users_ecards', function (Blueprint $table) {
            DB::statement('ALTER TABLE users_ecards CHANGE send_datetime send_datetime DATETIME NULL DEFAULT NULL');
        });
    }
}
