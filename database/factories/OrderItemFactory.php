<?php

use Faker\Generator as Faker;
use App\Models\{Product, OrderItem};

$factory->define(App\Models\OrderItem::class, function (Faker $faker) {
    $product = Product::query()->where('on_sale', true)->inRandomOrder()->first();
    $sku = $product->skus()->inRandomOrder()->first();

    return [
        'amount' => random_int(1, 5),
        'price' => $sku->price,
        'rating' => null,
        'review' => null,
        'reviewed_at' => null,
        'product_id' => $product->id,
        'product_sku_id' => $sku->id, 
    ];
});
