<?php

namespace Modules\Product\Events;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Payment\PaymentSucceeded;
use Modules\Product\Warehouse\ProductStockManager;

class DecreaseProductStock implements ShouldQueue
{
    public function __construct(
        protected ProductStockManager $productStockManager
    ) {
    }

    public function handle(PaymentSucceeded $event): void
    {
        foreach ($event->order->lines as $orderLine) {
            $this->productStockManager->decrement($orderLine->productId, $orderLine->quantity);
        }
    }
}
