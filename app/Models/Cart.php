<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'quantity', 'username', 'total'];

    // Định nghĩa mối quan hệ "productDetail" để dùng eager loading with()
    public function productDetail()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('active', function ($builder) {
            if (request()->method() !== 'PUT')
                $builder->where('carts.delete_flag', 0);
            $builder->orderBy('created_at', 'desc');
        });
    }
}
