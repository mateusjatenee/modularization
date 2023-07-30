<?php

namespace Modules\Order\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Order\Exceptions\OrderMissingOrderLinesException;
use Modules\Payment\Payment;
use Modules\Product\CartItem;
use Modules\Product\CartItemCollection;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'total_in_cents',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'total_in_cents' => 'integer',
    ];

    public const COMPLETED = 'completed';

    public const PENDING = 'pending';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function lastPayment(): HasOne
    {
        return $this->payments()->one()->latest();
    }

    public function lines(): HasMany
    {
        return $this->hasMany(OrderLine::class);
    }

    public function url(): string
    {
        return route('order::orders.show', $this);
    }

    public static function startForUser(int $userId): self
    {
        return self::make([
            'user_id' => $userId,
            'status' => self::PENDING
        ]);
    }

    /**
     * @param  \Modules\Product\CartItemCollection<CartItem>  $items
     * @return void
     */
    public function addLinesFromCartItems(CartItemCollection $items): void
    {
        foreach ($items->items() as $item) {
            $this->lines->push(OrderLine::make([
                'product_id' => $item->product->id,
                'product_price_in_cents' => $item->product->priceInCents,
                'quantity' => $item->quantity
            ]));
        }

        $this->total_in_cents = $this->lines->sum(fn (OrderLine $line) => $line->product_price_in_cents);
    }

    /**
     * @throws \Modules\Order\Exceptions\OrderMissingOrderLinesException
     */
    public function fulfill(): void
    {
        if ($this->lines->isEmpty()) {
            throw new OrderMissingOrderLinesException();
        }

        $this->status = self::COMPLETED;

        $this->save();
        $this->lines()->saveMany($this->lines);
    }
}
