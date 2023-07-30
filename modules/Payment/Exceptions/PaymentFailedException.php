<?php

namespace Modules\Payment\Exceptions;

use RuntimeException;

class PaymentFailedException extends RuntimeException
{
    public static function dueToInvalidToken(): PaymentFailedException
    {
        return new self('The given payment token is invalid.');
    }
}
