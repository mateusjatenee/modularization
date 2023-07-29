<?php

namespace Modules\Product;

readonly class CartItem
{
    public function __construct(
        public ProductDto $product,
        public int $quantity
    )
    {
    }
}
