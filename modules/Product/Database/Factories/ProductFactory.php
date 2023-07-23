<?php

namespace Modules\Product\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Product\Models\Product;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence,
            'price_in_cents' => random_int(100, 10000),
            'stock' => random_int(1, 10),
        ];
    }
}
