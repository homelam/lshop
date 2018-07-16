<?php

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // factory 生成100个用户并保存在数据库中
        factory(\App\Models\User::class, 100)->create();
    }
}
