<?php

namespace Modules\Product\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as BaseEventServiceProvider;
use Modules\Order\Events\OrderFulfilled;
use Modules\Product\Events\DecreaseProductStock;

class EventServiceProvider extends BaseEventServiceProvider
{
    protected $listen = [
        OrderFulfilled::class => [
            DecreaseProductStock::class,
        ],
    ];
}
