<?php

namespace Modules\Order\Checkout;

use Modules\Order\Contracts\OrderDto;
use Modules\User\UserDto;

readonly class OrderFulfilled
{
    public function __construct(
        public OrderDto $order,
        public UserDto $user,
    ) {
    }
}
