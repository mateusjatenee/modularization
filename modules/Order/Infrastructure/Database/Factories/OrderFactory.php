<?php

namespace Modules\Order\Infrastructure\Database\Factories;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Order\Order;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {
        return [
            'total_in_cents' => random_int(100, 10000),
            'status' => Order::PENDING,
            'user_id' => UserFactory::new(),
        ];
    }
}