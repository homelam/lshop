<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterUsersAddFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nickname', 60)->nullable()->default('');
            $table->string('provider')->default('system')->comment('用户来源');
            $table->string('social_id')->nullable()->comment('帐号在平台的id'); // 如果是社交帐号，则会有纪录
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn('nickname');
            $table->dropColumn('provider');
            $table->dropColumn('social_id');
        });
    }
}
