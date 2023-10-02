<?php

declare(strict_types=1);

namespace Modules\Order;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Payment\PaymentSucceeded;

class CompleteOrder implements ShouldQueue
{
    public function handle(PaymentSucceeded $event): void
    {
        Order::find($event->order->id)->complete();
    }
}
