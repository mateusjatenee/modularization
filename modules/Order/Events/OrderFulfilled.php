<?php

namespace Modules\Order\Events;

use Modules\Product\CartItemCollection;

readonly class OrderFulfilled
{
    public function __construct(
        public int $orderId,
        public int $totalInCents,
        public string $localizedTotal,
        public CartItemCollection $cartItems,
        public int $userId,
        public string $userEmail
    )
    {
    }
}
