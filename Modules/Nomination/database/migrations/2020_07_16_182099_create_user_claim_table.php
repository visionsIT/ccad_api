<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserClaimTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('user_claims', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->comment('Reference to program_users table ');
            $table->integer('claim_type_id')->unsigned()->comment('Reference to claim_types table ');
            $table->integer('approved_points')->nullable();
            $table->longText('reason')->nullable();
            $table->string('attachment_path')->nullable();
            $table->integer('approval_by')->nullable()->comment('HR user can approve or decline the claim request');
            $table->integer('approval_status')->default(0)->comment('0 mean not approved yet & 1 mean approved  & -1 mean decline');
            $table->longText('approval_decline_reason')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('program_users')->onDelete('cascade');
            $table->foreign('claim_type_id')->references('id')->on('claim_types')->onDelete('cascade');
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
        Schema::dropIfExists('user_claims');
    }
}
