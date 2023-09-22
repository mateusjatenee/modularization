<?php

declare(strict_types=1);

namespace Modules\Payment\Actions;

use Modules\Payment\Exceptions\PaymentFailedException;
use Modules\Payment\Payment;
use Modules\Payment\PaymentGateway;
use Modules\Payment\PaymentProvider;
use Str;

class CreatePaymentForOrderInMemory implements CreatePaymentForOrderInterface
{
    /** @var Payment[] */
    public array $payments = [];

    protected bool $shouldFail = false;

    public function handle(
        int $orderId,
        int $userId,
        int $totalInCents,
        PaymentGateway $paymentGateway,
        string $paymentToken
    ): Payment {
        if ($this->shouldFail) {
            throw new PaymentFailedException();
        }

        $payment = new Payment([
            'order_id' => $orderId,
            'user_id' => $userId,
            'total_in_cents' => $totalInCents,
            'payment_gateway' => PaymentProvider::InMemory,
            'payment_id' => (string) Str::uuid()
        ]);

        $this->payments[] = $payment;

        return $payment;
    }

    public function shouldFail(): void
    {
        $this->shouldFail = true;
    }
}
