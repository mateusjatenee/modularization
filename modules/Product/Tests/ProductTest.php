<?php

namespace Modules\Product\Tests;

use Modules\Product\Models\Product;
use Tests\TestCase;

class ProductTest extends TestCase
{
    public function test_it_creates_a_product()
    {
        $product = new Product();

        $this->assertTrue(true);
    }
}
