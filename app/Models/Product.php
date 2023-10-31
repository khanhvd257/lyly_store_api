<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'status', 'price', 'category_id'
        , 'quantity', 'image_url', 'delete_flag'];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('active', function ($builder) {
            if (request()->method() !== 'PUT')
                $builder->where('delete_flag', 0);
            $builder->orderBy('created_at', 'desc');
        });
        static::retrieved(function ($product) {
            if($product->image_url)
                return $product->image_url = url('storage/images/' . $product->image_url);

        });


    }

    function getImagePathAttributes()
    {
        return url('storage/images/' . $this->image_url);
    }

}
