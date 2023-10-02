<?php

declare(strict_types=1);

namespace Modules\Order\Tests\Checkout;

use Database\Factories\UserFactory;
use Event;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Mail;
use Modules\Order\Checkout\OrderStarted;
use Modules\Order\Checkout\PurchaseItems;
use Modules\Order\Contracts\PendingPayment;
use Modules\Payment\InMemoryGateway;
use Modules\Payment\PayBuddySdk;
use Modules\Product\Collections\CartItemCollection;
use Modules\Product\Database\factories\ProductFactory;
use Modules\Product\DTOs\ProductDto;
use Modules\User\UserDto;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PurchaseItemsTest extends TestCase
{
    use DatabaseMigrations;

    #[Test]
    public function it_creates_an_order(): void
    {
        Mail::fake();
        Event::fake();

        $user = UserFactory::new()->create();
        $product = ProductFactory::new()->create([
            'stock' => 10,
            'price_in_cents' => 100_00,
        ]);

        $cartItemCollection = CartItemCollection::fromProduct(ProductDto::fromEloquentModel($product), 2);
        $pendingPayment = new PendingPayment(new InMemoryGateway(), PayBuddySdk::validToken());
        $userDto = UserDto::fromEloquentModel($user);

        $purchaseItems = app(PurchaseItems::class);
        $order = $purchaseItems->handle($cartItemCollection, $pendingPayment, $userDto);

        $orderLine = $order->lines[0];

        $this->assertEquals($product->price_in_cents * 2, $order->totalInCents);
        $this->assertCount(1, $order->lines);
        $this->assertEquals($product->id, $orderLine->productId);
        $this->assertEquals($product->price_in_cents, $orderLine->productPriceInCents);
        $this->assertEquals(2, $orderLine->quantity);

        Event::assertDispatched(OrderStarted::class, function (OrderStarted $event) use ($userDto, $order) {
            return $event->order === $order && $event->user === $userDto;
        });
    }
}
