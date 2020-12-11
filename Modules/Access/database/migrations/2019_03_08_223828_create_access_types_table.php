<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccessTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('access_types', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('program_id')->unsigned();
            $table->enum('way_to_access_the_program', ['no_login', 'sso', 'pre_registration', 'self_registration']);
            $table->enum('register_require_approval', ['yes', 'no'])->default('yes');
            $table->string('email')->unique();
            $table->text('account_locked_out_message');
            $table->enum('reset_password_option', ['email', 'sms'])->default('email');
            $table->foreign('program_id')->references('id')->on('programs')->onDelete('cascade');
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
        Schema::dropIfExists('access_types');
    }
}
