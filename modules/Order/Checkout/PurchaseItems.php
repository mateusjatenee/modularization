<?php

namespace Modules\Order\Checkout;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\DatabaseManager;
use Modules\Order\Contracts\OrderDto;
use Modules\Order\Contracts\PendingPayment;
use Modules\Order\Order;
use Modules\Payment\Actions\CreatePaymentForOrderInterface;
use Modules\Product\Collections\CartItemCollection;
use Modules\Product\Warehouse\ProductStockManager;
use Modules\User\UserDto;

class PurchaseItems
{
    public function __construct(
        protected ProductStockManager $productStockManager,
        protected CreatePaymentForOrderInterface $createPaymentForOrder,
        protected DatabaseManager $databaseManager,
        protected Dispatcher $events
    ) {
    }

    public function handle(CartItemCollection $items, PendingPayment $pendingPayment, UserDto $user): OrderDto
    {
        /** @var OrderDto $order */
        $order = $this->databaseManager->transaction(function () use ($user, $items) {
            $order = Order::startForUser($user->id);
            $order->addLinesFromCartItems($items);
            $order->start();

            return OrderDto::fromEloquentModel($order);
        });

        $this->events->dispatch(
            new OrderStarted($order, $user, $pendingPayment)
        );

        return $order;
    }
}
