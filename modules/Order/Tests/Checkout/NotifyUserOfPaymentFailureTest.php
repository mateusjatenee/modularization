<?php

declare(strict_types=1);

namespace Modules\Order\Tests\Checkout;

use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Mail;
use Modules\Order\Checkout\NotifyUserOfPaymentFailure;
use Modules\Order\Checkout\PaymentForOrderFailed;
use Modules\Order\Contracts\OrderDto;
use Modules\Order\Infrastructure\Database\Factories\OrderFactory;
use Modules\Payment\PaymentFailed;
use Modules\User\UserDto;
use Tests\TestCase;

class NotifyUserOfPaymentFailureTest extends TestCase
{
    use DatabaseMigrations;

    public function test_it_notifies_the_user_of_the_payment_failure(): void
    {
        Mail::fake();

        $order = OrderFactory::new()->create();
        $orderDto = OrderDto::fromEloquentModel($order);
        $userDto = UserDto::fromEloquentModel(UserFactory::new()->create());

        $event = new PaymentFailed($orderDto, $userDto, 'Payment failed.');
        app(NotifyUserOfPaymentFailure::class)->handle($event);

        Mail::assertSent(PaymentForOrderFailed::class, fn (PaymentForOrderFailed $mailable) =>
            $mailable->hasTo($userDto->email)
        );
    }
}
