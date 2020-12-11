<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddL2ApproverAccountIdInUserNominations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_nominations', function (Blueprint $table) {
            $table->integer('l2_approver_account_id')
                ->comment('account id for L2 approver who has approved nominations')
                ->nullable()
                ->unsigned()
                ->after('account_id');

            $table->foreign('l2_approver_account_id')->references('id')->on('accounts')->onDelete('cascade');

            $table->integer('rajecter_account_id')
                ->comment('account id for rejecter who has raject the nomination')
                ->nullable()
                ->unsigned()
                ->after('account_id');

            $table->foreign('rajecter_account_id')->references('id')->on('accounts')->onDelete('cascade');

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
            $table->dropForeign('user_nominations_l2_approver_account_id_foreign');
            $table->dropForeign('user_nominations_rajecter_account_id_foreign');
            $table->dropColumn('l2_approver_account_id');
            $table->dropColumn('rajecter_account_id');

        });
    }
}
