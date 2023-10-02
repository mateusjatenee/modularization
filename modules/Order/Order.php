<?php

namespace Modules\Order;

use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Payment\Payment;
use Modules\Product\Collections\CartItemCollection;
use Modules\Product\DTOs\CartItem;
use NumberFormatter;

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

    public const PAYMENT_FAILED = 'payment_failed';

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

    public function localizedTotal(): string
    {
        return (new NumberFormatter('en-US', NumberFormatter::CURRENCY))->formatCurrency($this->total_in_cents / 100, 'USD');
    }

    public static function startForUser(int $userId): self
    {
        return self::make([
            'user_id' => $userId,
            'status' => self::PENDING,
        ]);
    }

    public function addLines(array $lines): void
    {
        foreach ($lines as $line) {
            $this->lines->push($line);
        }

        $this->total_in_cents = $this->lines->sum(fn (OrderLine $line) => $line->total());
    }

    /**
     * @param  \Modules\Product\Collections\CartItemCollection<CartItem>  $items
     */
    public function addLinesFromCartItems(CartItemCollection $items): void
    {
        foreach ($items->items() as $item) {
            $this->lines->push(OrderLine::make([
                'product_id' => $item->product->id,
                'product_price_in_cents' => $item->product->priceInCents,
                'quantity' => $item->quantity,
            ]));
        }

        $this->total_in_cents = $this->lines->sum(fn (OrderLine $line) => $line->total());
    }

    public function markAsFailed(): void
    {
        if ($this->isCompleted()) {
            throw new \RuntimeException('A completed order cannot be marked as failed.');
        }

        $this->status = self::PAYMENT_FAILED;

        $this->save();
    }

    public function isCompleted(): bool
    {
        return $this->status === self::COMPLETED;
    }

    public function complete(): void
    {
        $this->status = self::COMPLETED;

        $this->save();
    }

    /**
     * @throws \Modules\Order\OrderMissingOrderLinesException
     */
    public function start(): void
    {
        if ($this->lines->isEmpty()) {
            throw new OrderMissingOrderLinesException();
        }

        $this->status = self::PENDING;

        $this->save();
        $this->lines()->saveMany($this->lines);
    }
}
