<?php

namespace Modules\Order\Infrastructure\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Order\OrderLine;
use Modules\Product\Database\factories\ProductFactory;

class OrderLineFactory extends Factory
{
    protected $model = OrderLine::class;

    public function definition(): array
    {
        return [
            'order_id' => OrderFactory::new(),
            'product_id' => ProductFactory::new(),
            'product_price_in_cents' => $this->faker->randomNumber(),
            'quantity' => 1,
        ];
    }
}
