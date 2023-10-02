<?php

declare(strict_types=1);

namespace Modules\Order\Tests\Checkout;

use Illuminate\Support\Facades\Event;
use Modules\Order\Checkout\OrderStarted;
use Modules\Order\Checkout\SendOrderConfirmationEmail;
use Modules\Payment\PayOrder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderStartedTest extends TestCase
{
    #[Test]
    public function it_has_listeners(): void
    {
        Event::fake();

        Event::assertListening(OrderStarted::class, SendOrderConfirmationEmail::class);
        Event::assertListening(OrderStarted::class, PayOrder::class);
    }
}
