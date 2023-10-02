<?php

declare(strict_types=1);

namespace Modules\Payment\Tests;

use Event;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Modules\Order\Checkout\OrderStarted;
use Modules\Order\Contracts\OrderDto;
use Modules\Order\Contracts\PendingPayment;
use Modules\Order\Infrastructure\Database\Factories\OrderFactory;
use Modules\Order\Infrastructure\Database\Factories\OrderLineFactory;
use Modules\Payment\Actions\CreatePaymentForOrderInMemory;
use Modules\Payment\Actions\CreatePaymentForOrderInterface;
use Modules\Payment\Exceptions\PaymentFailedException;
use Modules\Payment\PayBuddySdk;
use Modules\Payment\PaymentFailed;
use Modules\Payment\PaymentGateway;
use Modules\Payment\PaymentProvider;
use Modules\Payment\PaymentSucceeded;
use Modules\Payment\PayOrder;
use Modules\User\UserDto;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PayOrderTest extends TestCase
{
    use DatabaseMigrations;

    #[Test]
    public function it_is_queued(): void
    {
        $this->assertInstanceOf(ShouldQueue::class, app(PayOrder::class));
    }

    #[Test]
    public function it_pays_for_an_order(): void
    {
        Event::fake();

        $order = OrderFactory::new()->make();
        $order->addLines([
            OrderLineFactory::new()->make([
                'product_price_in_cents' => 99_00,
            ]),
        ]);
        $order->start();

        $event = new OrderStarted(
            OrderDto::fromEloquentModel($order),
            UserDto::fromEloquentModel($order->user),
            new PendingPayment(app(PaymentGateway::class), PayBuddySdk::validToken())
        );

        app(PayOrder::class)->handle($event);

        $payment = $order->lastPayment;
        $this->assertEquals('paid', $payment->status);
        $this->assertEquals(PaymentProvider::PayBuddy, $payment->payment_gateway);
        $this->assertEquals(36, strlen($payment->payment_id));
        $this->assertEquals(99_00, $payment->total_in_cents);
        $this->assertTrue($payment->user->is($order->user));

        Event::assertDispatched(PaymentSucceeded::class);
    }

    #[Test]
    public function it_handles_a_payment_failure(): void
    {
        $this->expectException(PaymentFailedException::class);

        $createPayment = new CreatePaymentForOrderInMemory();
        $createPayment->shouldFail();
        $this->app->instance(CreatePaymentForOrderInterface::class, $createPayment);

        Event::fake();

        $order = OrderFactory::new()->make();
        $order->addLines([
            OrderLineFactory::new()->make([
                'product_price_in_cents' => 99_00,
            ]),
        ]);

        $order->start();

        $event = new OrderStarted(
            OrderDto::fromEloquentModel($order),
            UserDto::fromEloquentModel($order->user),
            new PendingPayment(app(PaymentGateway::class), PayBuddySdk::validToken())
        );

        try {
            app(PayOrder::class)->handle($event);
        } finally {
            $order->refresh();

            Event::assertDispatched(PaymentFailed::class);
            Event::assertNotDispatched(PaymentSucceeded::class);
        }

    }
}
