<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserPointsTableRelationUserNominationProductOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_points', function (Blueprint $table) {
            $table->integer('user_nominations_id')->nullable()->after('user_id');
            $table->integer('product_order_id')->nullable()->after('user_nominations_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_points');
    }
}
