<?php

namespace Modules\Product\DTOs;

use Modules\Product\Models\Product;

readonly class ProductDto
{
    public function __construct(
        public int $id,
        public int $priceInCents,
        public int $unitsInStock
    ) {
    }

    public static function fromEloquentModel(Product $product): ProductDto
    {
        return new ProductDto(
            id: $product->id,
            priceInCents: $product->price_in_cents,
            unitsInStock: $product->stock
        );
    }
}
