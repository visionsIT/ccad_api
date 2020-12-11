<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddApproverAccountIdInUserNominations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_nominations', function (Blueprint $table) {
            $table->integer('approver_account_id')
                ->comment('account id for approver who has approved nominations')
                ->nullable()
                ->unsigned()
                ->after('account_id');

            $table->foreign('approver_account_id')->references('id')->on('accounts')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_nominations', function (Blueprint $table) {
            $table->dropForeign('user_nominations_approver_account_id_foreign');
            $table->dropColumn('approver_account_id');

        });
    }
}
