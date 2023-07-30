<?php

namespace Modules\Payment\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Payment\PayBuddyGateway;
use Modules\Payment\PayBuddySdk;
use Modules\Payment\PaymentGateway;

class PaymentServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->app->bind(PaymentGateway::class, fn () => new PayBuddyGateway(new PayBuddySdk()));
    }
}
