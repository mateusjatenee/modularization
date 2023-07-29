<?php

namespace Modules\Order\Http\Controllers;

use Illuminate\Validation\ValidationException;
use Modules\Order\Http\Requests\CheckoutRequest;
use Modules\Order\Models\Order;
use Modules\Payment\PayBuddy;
use Modules\Product\Models\Product;
use RuntimeException;

class CheckoutController
{
    public function __invoke(CheckoutRequest $request)
    {
        $products = collect($request->input('products'))->map(function (array $productDetails) {
            return [
                'product' => Product::find($productDetails['id']),
                'quantity' => $productDetails['quantity']
            ];
        });

        $orderTotalInCents = $products->sum(fn (array $productDetails) =>
            $productDetails['quantity'] * $productDetails['product']->price_in_cents
        );

        $payBuddy = PayBuddy::make();

        try {
            $charge = $payBuddy->charge($request->input('payment_token'), $orderTotalInCents, 'Modularization');
        } catch (RuntimeException) {
            throw ValidationException::withMessages([
                'payment_token' => 'We could not complete your payment.'
            ]);
        }

        $order = Order::query()->create([
            'payment_id' => $charge['id'],
            'status' => 'paid',
            'payment_gateway' => 'PayBuddy',
            'total_in_cents' => $orderTotalInCents,
            'user_id' => $request->user()->id
        ]);

        foreach ($products as $product) {
            $product['product']->decrement('stock');

            $order->lines()->create([
                'product_id' => $product['product']->id,
                'product_price_in_cents' => $product['product']->price_in_cents,
                'quantity' => $product['quantity']
            ]);
        }

        return response()->json([], 201);
    }
}
