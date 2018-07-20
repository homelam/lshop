<?php

use Faker\Generator as Faker;
use App\Models\Category;

$factory->define(Category::class, function (Faker $faker) {

    return [
        'name' => $faker->word,
        'parent_id' => 0,
        'description' => $faker->sentence, // 分类描述
        'is_show' => true,
        'sort_order' => random_int(1, 200),
    ];
});
