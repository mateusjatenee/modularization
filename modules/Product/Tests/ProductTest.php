<?php

namespace Modules\Product\Tests;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Modules\Product\Models\Product;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use DatabaseMigrations;

    public function test_it_creates_a_product()
    {
        $product = Product::factory()->create();

        $this->assertNotEmpty($product->name);
    }
}
