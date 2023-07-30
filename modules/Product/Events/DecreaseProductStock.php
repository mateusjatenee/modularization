<?php

namespace Modules\Product\Events;

use Modules\Order\Events\OrderFulfilled;
use Modules\Product\Warehouse\ProductStockManager;

class DecreaseProductStock
{
    public function __construct(
        protected ProductStockManager $productStockManager
    )
    {
    }

    public function handle(OrderFulfilled $event): void
    {
        foreach ($event->cartItems->items() as $cartItem) {
            $this->productStockManager->decrement($cartItem->product->id, $cartItem->quantity);
        }
    }
}
