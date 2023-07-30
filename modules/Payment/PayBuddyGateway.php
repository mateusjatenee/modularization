<?php

namespace Modules\Payment;

use Modules\Payment\Exceptions\PaymentFailedException;
use RuntimeException;

class PayBuddyGateway implements PaymentGateway
{
    public function __construct(
        protected PayBuddySdk $payBuddySdk
    ) {
    }

    /**
     * @param  \Modules\Payment\PaymentDetails  $details
     * @return \Modules\Payment\SuccessfulPayment
     *
     * @throws \Modules\Payment\Exceptions\PaymentFailedException
     */
    public function charge(PaymentDetails $details): SuccessfulPayment
    {
        try {
            $charge = $this->payBuddySdk->charge(
                $details->token,
                $details->amountInCents,
                $details->statementDescription
            );
        } catch (RuntimeException $exception) {
            throw new PaymentFailedException($exception->getMessage());
        }

        return new SuccessfulPayment(
            $charge['id'],
            $charge['amount_in_cents'],
            $this->id()
        );
    }

    public function id(): PaymentProvider
    {
        return PaymentProvider::PayBuddy;
    }
}
