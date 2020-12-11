<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserIdInRippleBudgetLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ripple_budget_log', function (Blueprint $table) {
            $table->integer('user_id')->comment("budget receiver program user id")->after('type');
            $table->float('current_balance')->comment("receiver user current ripple budget")->after('user_id')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ripple_budget_log');
    }
}
