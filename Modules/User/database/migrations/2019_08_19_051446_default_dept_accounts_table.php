<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DefaultDeptAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $sql = "ALTER TABLE `accounts` ADD COLUMN `def_dept_id` INT(10) UNSIGNED NULL AFTER `remember_token`, ADD FOREIGN KEY (`def_dept_id`) REFERENCES `departments`(`id`) ON DELETE SET NULL;";
                DB::connection()->getPdo()->exec($sql);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
