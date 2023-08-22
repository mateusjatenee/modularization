<?php

namespace Modules\Order\Tests\Models;

use Modules\Order\Order;
use Modules\Order\Tests\OrderTestCase;

class OrderTest extends OrderTestCase
{
    public function test_it_creates_an_order(): void
    {
        $order = new Order();

        $this->assertTrue(true);
    }
}
