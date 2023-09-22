<?php

namespace Modules\Product\DTOs;

readonly class CartItem
{
    public function __construct(
        public ProductDto $product,
        public int $quantity
    ) {
    }
}
