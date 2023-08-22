<?php

namespace Modules\Order\Contracts;

use Modules\Order\Order;

readonly class OrderDto
{
    /**
     * @param  OrderLineDto[]  $lines
     */
    public function __construct(
        public int $id,
        public int $totalInCents,
        public string $localizedTotal,
        public string $url,
        public array $lines
    ) {
    }

    public static function fromEloquentModel(Order $order): self
    {
        return new self(
            $order->id,
            $order->total_in_cents,
            $order->localizedTotal(),
            $order->url(),
            OrderLineDto::fromEloquentCollection($order->lines)
        );
    }
}
