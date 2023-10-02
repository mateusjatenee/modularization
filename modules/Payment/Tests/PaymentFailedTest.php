<?php

declare(strict_types=1);

namespace Modules\Payment\Tests;

use Illuminate\Support\Facades\Event;
use Modules\Order\Checkout\MarkOrderAsFailed;
use Modules\Order\Checkout\NotifyUserOfPaymentFailure;
use Modules\Payment\PaymentFailed;
use Modules\Payment\PaymentSucceeded;
use Modules\Product\Events\DecreaseProductStock;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaymentFailedTest extends TestCase
{
    #[Test]
    public function it_has_listeners(): void
    {
        Event::fake();

        Event::assertListening(PaymentFailed::class, NotifyUserOfPaymentFailure::class);
        Event::assertListening(PaymentFailed::class, MarkOrderAsFailed::class);
    }
}
