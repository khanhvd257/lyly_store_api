<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['username', 'delivery_address', 'status', 'note'];
    use HasFactory;

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }
}
