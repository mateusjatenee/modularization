<?php

namespace Modules\Payment;

interface PaymentGateway
{
    public function charge(PaymentDetails $details): SuccessfulPayment;

    public function id(): PaymentProvider;
}
