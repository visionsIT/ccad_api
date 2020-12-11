<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGroupPermissionToRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('roles')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->enum('nomination_approval_access', [0, 1])->default(0)->after('group_level_parent_id');
                $table->enum('instant_point_access', [0, 1])->default(0)->after('nomination_approval_access');
                $table->enum('project_compaign_access', [0, 1])->default(0)->after('instant_point_access');
                $table->enum('status', [0, 1])->default(1)->after('project_compaign_access');
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
        Schema::drop('roles');
    }
}
