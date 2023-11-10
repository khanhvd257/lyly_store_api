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

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('active', function ($builder) {
            if (request()->method() !== 'PUT')
                $builder->where('orders.delete_flag', 0);
            $builder->orderBy('updated_at', 'desc');
        });

    }
}
