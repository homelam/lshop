<?php

use Illuminate\Database\Seeder;
use App\Models\{User, UserAddress};

class UserAddressesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::all()->each(function(User $user) {
            // 为每个用户生成1 - 3个随机地址
            factory(UserAddress::class, random_int(1, 3))->create(['user_id' => $user->id]);
        });
    }
}
