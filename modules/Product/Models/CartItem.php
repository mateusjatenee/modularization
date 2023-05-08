<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'quantity',
        'user_id',
        'product_id',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'user_id' => 'integer',
        'product_id' => 'integer',
    ];
}
