<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'description', 'price', 'category_id'
        ,'quantity', 'image_url','delete_flag'];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('active', function ($builder) {
            if(request()->method() !== 'PUT')
            $builder->where('delete_flag', 0);
        });
    }

}
