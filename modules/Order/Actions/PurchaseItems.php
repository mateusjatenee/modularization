<?php

namespace Modules\Order\Actions;

use Illuminate\Database\DatabaseManager;
use Modules\Order\Exceptions\PaymentFailedException;
use Modules\Order\Models\Order;
use Modules\Payment\Actions\CreatePaymentForOrder;
use Modules\Payment\PayBuddy;
use Modules\Product\CartItemCollection;
use Modules\Product\Warehouse\ProductStockManager;
use RuntimeException;

class PurchaseItems
{
    public function __construct(
        protected ProductStockManager $productStockManager,
        protected CreatePaymentForOrder $createPaymentForOrder,
        protected DatabaseManager $databaseManager
    )
    {
    }

    public function handle(CartItemCollection $items, PayBuddy $paymentProvider, string $paymentToken, int $userId): Order
    {
        $orderTotalInCents = $items->totalInCents();

        return $this->databaseManager->transaction(function () use ($paymentToken, $paymentProvider, $items, $userId, $orderTotalInCents) {
            $order = Order::query()->create([
                'status' => 'completed',
                'total_in_cents' => $orderTotalInCents,
                'user_id' => $userId,
            ]);

            foreach ($items->items() as $cartItem) {
                $this->productStockManager->decrement($cartItem->product->id, $cartItem->quantity);

                $order->lines()->create([
                    'product_id' => $cartItem->product->id,
                    'product_price_in_cents' => $cartItem->product->priceInCents,
                    'quantity' => $cartItem->quantity
                ]);
            }

            $this->createPaymentForOrder->handle($order->id, $userId, $orderTotalInCents, $paymentProvider, $paymentToken);

            return $order;
        });
    }
}
