<?php

declare(strict_types=1);

namespace Modules\Payment\Infrastructure\Providers;

use App\Providers\EventServiceProvider as BaseServiceProvider;
use Modules\Order\Checkout\OrderStarted;
use Modules\Payment\PayOrder;

class EventServiceProvider extends BaseServiceProvider
{
    protected $listen = [
        OrderStarted::class => [
            PayOrder::class
        ],
    ];
}
