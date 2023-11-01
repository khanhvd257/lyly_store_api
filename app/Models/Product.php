<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'status', 'price', 'category_id'
        , 'quantity', 'image_url', 'delete_flag'];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('active', function ($builder) {
            if (request()->method() !== 'PUT')
                $builder->where('products.delete_flag', 0);
            $builder->orderBy('created_at', 'desc');
        });
        static::retrieved(function ($product) {
            if ($product->image_url)
                return $product->image_url = url('storage/images/' . $product->image_url);

        });


    }


    /**
     * Kiểm tra số lượng trong kho hàng
     * @param $quantity
     * @return bool
     */
    public function isAvailableInStock($quantity): bool
    {
        // Lấy số lượng tồn kho của sản phẩm từ bảng Product
        $product = Product::find($this->id);

        if ($product) {
            return $quantity <= $product['quantity'];
        }

        return false;
    }

}
