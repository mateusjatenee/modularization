<?php

namespace Modules\Order\Checkout;

use Modules\Order\Contracts\OrderDto;
use Modules\Order\Contracts\PendingPayment;
use Modules\User\UserDto;

readonly class OrderStarted
{
    public function __construct(
        public OrderDto $order,
        public UserDto $user,
        public PendingPayment $pendingPayment,
    ) {
    }
}
