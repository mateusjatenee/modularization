<?php

namespace Modules\Order\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Modules\Order\OrderLine;

readonly class OrderLineDto
{
    public function __construct(
        public int $productId,
        public int $productPriceInCents,
        public int $quantity
    ) {
    }

    public static function fromEloquentModel(OrderLine $orderLine): self
    {
        return new self($orderLine->product_id, $orderLine->product_price_in_cents, $orderLine->quantity);
    }

    /**
     * @return \Modules\Order\Contracts\OrderLineDto[]
     */
    public static function fromEloquentCollection(Collection $orderLines): array
    {
        return $orderLines->map(fn (OrderLine $orderLine) => self::fromEloquentModel($orderLine))->all();
    }
}
