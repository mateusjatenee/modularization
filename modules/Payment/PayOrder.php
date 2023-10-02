<?php

declare(strict_types=1);

namespace Modules\Payment;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Order\Checkout\OrderStarted;
use Modules\Payment\Actions\CreatePaymentForOrderInterface;
use Modules\Payment\Exceptions\PaymentFailedException;

class PayOrder implements ShouldQueue
{
    public function __construct(
        protected CreatePaymentForOrderInterface $createPaymentForOrder,
        protected Dispatcher $events,
    ) {
    }

    public function handle(OrderStarted $event): void
    {
        try {
            $this->createPaymentForOrder->handle(
                $event->order->id,
                $event->user->id,
                $event->order->totalInCents,
                $event->pendingPayment->provider,
                $event->pendingPayment->paymentToken
            );
        } catch (PaymentFailedException $exception) {
            $this->events->dispatch(
                new PaymentFailed($event->order, $event->user, $exception->getMessage())
            );

            throw $exception;
        }

        $this->events->dispatch(
            new PaymentSucceeded($event->order, $event->user)
        );
    }
}
