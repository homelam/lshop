<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar')->default('')->after('email')->comment('用户头像');
            $table->string('last_login_ip', 15)->nullable()->comment('最近一次登录的ip');
            $table->dateTime('last_login_at')->nullable()->comment('最近一次登录的时间');
            $table->date('birthday')->nullable()->comment('用户生日');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('avatar');
            $table->dropColumn('last_login_ip');
            $table->dropColumn('last_login_at');
            $table->dropColumn('birthdayl');
        });
    }
}
