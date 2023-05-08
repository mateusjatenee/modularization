<?php

namespace Modules\Order\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_price',
        'quantity',
    ];

    protected $casts = [
        'order_id' => 'integer',
        'product_id' => 'integer',
        'product_price_in_cents' => 'integer',
        'quantity' => 'integer',
    ];
}
