<?php

namespace Modules\Order\Actions;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Mail;
use Modules\Order\Events\OrderFulfilled;
use Modules\Order\Mail\OrderReceived;
use Modules\Order\Models\Order;
use Modules\Payment\Actions\CreatePaymentForOrder;
use Modules\Payment\PayBuddy;
use Modules\Product\CartItemCollection;
use Modules\Product\Warehouse\ProductStockManager;

class PurchaseItems
{
    public function __construct(
        protected ProductStockManager $productStockManager,
        protected CreatePaymentForOrder $createPaymentForOrder,
        protected DatabaseManager $databaseManager,
        protected Dispatcher $events
    ) {
    }

    public function handle(CartItemCollection $items, PayBuddy $paymentProvider, string $paymentToken, int $userId, string $userEmail): Order
    {
        /** @var Order $order */
        $order = $this->databaseManager->transaction(function () use ($paymentToken, $paymentProvider, $items, $userId) {
            $order = Order::startForUser($userId);
            $order->addLinesFromCartItems($items);
            $order->fulfill();

            $this->createPaymentForOrder->handle(
                $order->id,
                $userId,
                $items->totalInCents(),
                $paymentProvider,
                $paymentToken
            );

            return $order;
        });

        $this->events->dispatch(
            new OrderFulfilled(
                orderId: $order->id,
                totalInCents: $order->total_in_cents,
                localizedTotal: $order->localizedTotal(),
                cartItems: $items,
                userId: $userId,
                userEmail: $userEmail
            )
        );

        return $order;
    }
}
