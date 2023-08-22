<?php

namespace Modules\Product\Events;

use Modules\Order\Checkout\OrderFulfilled;
use Modules\Product\Warehouse\ProductStockManager;

class DecreaseProductStock
{
    public function __construct(
        protected ProductStockManager $productStockManager
    ) {
    }

    public function handle(OrderFulfilled $event): void
    {
        foreach ($event->order->lines as $orderLine) {
            $this->productStockManager->decrement($orderLine->productId, $orderLine->quantity);
        }
    }
}
