<?php

namespace Modules\Order\Tests\Http\Controllers;

use Database\Factories\UserFactory;
use Event;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Mail;
use Modules\Order\Checkout\OrderReceived;
use Modules\Order\Checkout\OrderStarted;
use Modules\Order\Order;
use Modules\Order\Tests\OrderTestCase;
use Modules\Payment\PayBuddySdk;
use Modules\Payment\PaymentProvider;
use Modules\Product\Database\factories\ProductFactory;
use PHPUnit\Framework\Attributes\Test;

class CheckoutControllerTest extends OrderTestCase
{
    use DatabaseMigrations;

    #[Test]
    public function it_successfuly_creates_an_order(): void
    {
        $this->withoutExceptionHandling();

        Event::fake();
        Mail::fake();
        $user = UserFactory::new()->create();
        $products = ProductFactory::new()->count(2)->create(
            new Sequence(
                ['name' => 'Very expensive air fryer', 'price_in_cents' => 10000, 'stock' => 10],
                ['name' => 'Macbook Pro M3', 'price_in_cents' => 50000, 'stock' => 10]
            )
        );

        $paymentToken = PayBuddySdk::validToken();

        $response = $this->actingAs($user)
            ->post(route('order::checkout', [
                'payment_token' => $paymentToken,
                'products' => [
                    ['id' => $products->first()->id, 'quantity' => 1],
                    ['id' => $products->last()->id, 'quantity' => 1],
                ],
            ]));

        $order = Order::query()->latest('id')->first();

        $response
            ->assertJson([
                'order_url' => $order->url(),
            ])
            ->assertStatus(201);

        // Order
        $this->assertTrue($order->user->is($user));
        $this->assertEquals(60000, $order->total_in_cents);
        $this->assertEquals(Order::PENDING, $order->status);

        // Order Lines
        $this->assertCount(2, $order->lines);

        foreach ($products as $product) {
            /** @var \Modules\Order\OrderLine $orderLine */
            $orderLine = $order->lines->where('product_id', $product->id)->first();

            $this->assertEquals($product->price_in_cents, $orderLine->product_price_in_cents);
            $this->assertEquals(1, $orderLine->quantity);
        }

        Event::assertDispatched(OrderStarted::class);
    }

    #[Test]
    public function it_fails_with_an_invalid_token(): void
    {
        $this->markTestSkipped();

        $user = UserFactory::new()->create();
        $product = ProductFactory::new()->create();
        $paymentToken = PayBuddySdk::invalidToken();

        $response = $this->actingAs($user)
            ->postJson(route('order::checkout', [
                'payment_token' => $paymentToken,
                'products' => [
                    ['id' => $product->id, 'quantity' => 1],
                ],
            ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['payment_token']);

        $this->assertEquals(0, Order::query()->count());
    }
}
