<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserNominationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('user_nominations', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('account_id')->unsigned();
            $table->integer('nomination_id')->unsigned();

            $table->integer('user')->unsigned()->comment('Reference to ACCOUNT table ');;
            $table->integer('value')->unsigned()->comment('Reference to nomination type table ');
            $table->integer('points')->unsigned()->comment('Reference to awards level table ');

            $table->longText('reason')->nullable();
            $table->string('attachments')->nullable();

            $table->integer('level_1_approval')->default(0)->comment('0 mean not approved yet & 1 mean approved  & -1 mean decline');
            $table->integer('level_2_approval')->default(0)->comment('0 mean not approved yet & 1 mean approved  & -1 mean decline');

            $table->timestamps();

            $table->foreign('user')->references('id')->on('accounts')->onDelete('cascade');

            $table->foreign('value')->references('id')->on('nomination_types')->onDelete('cascade');
            $table->foreign('points')->references('id')->on('awards_levels')->onDelete('cascade');

            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->foreign('nomination_id')->references('id')->on('nominations')->onDelete('cascade');

            $table->integer('is_active')->default(1);

        });
        Schema::enableForeignKeyConstraints();

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_nominations');
    }
}
