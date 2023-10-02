<?php

namespace Modules\Payment\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Payment\Actions\CreatePaymentForOrder;
use Modules\Payment\Actions\CreatePaymentForOrderInterface;
use Modules\Payment\PayBuddyGateway;
use Modules\Payment\PayBuddySdk;
use Modules\Payment\PaymentGateway;

class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(EventServiceProvider::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->app->bind(PaymentGateway::class, fn () => new PayBuddyGateway(new PayBuddySdk()));
        $this->app->bind(CreatePaymentForOrderInterface::class, fn () => new CreatePaymentForOrder());
    }
}
