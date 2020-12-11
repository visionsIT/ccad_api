<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserDataCustomFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_data_custom_fields', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('program_id')->unsigned();
            $table->string('field_name');
            $table->string('field_label');
            $table->string('field_type')->default('text');
            $table->enum('is_hidden',  ['yes', 'no'])->default('no');
            $table->enum('is_identifier', ['yes', 'no'])->default('no');
            $table->enum('is_mandatory',  ['yes', 'no'])->default('no');
            $table->enum('is_custom',  ['yes', 'no'])->default('no');
            $table->longText('values')->nullable();
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
        Schema::dropIfExists('user_data_custom_fields');
    }
}
