<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'quantity', 'username', 'total'];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('active', function ($builder) {
            if (request()->method() !== 'PUT')
                $builder->where('delete_flag', 0);
            $builder->orderBy('created_at', 'desc');
        });
    }
}
