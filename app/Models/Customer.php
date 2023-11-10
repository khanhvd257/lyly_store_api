<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = ['username', 'avatar', 'name', 'address', 'phone', 'email', 'status'];

    protected static function boot()
    {
        parent::boot();
//        static::addGlobalScope('active', function ($builder) {
//            if (request()->method() !== 'PUT')
//                $builder->where('products.delete_flag', 0);
//            $builder->orderBy('created_at', 'desc');
//        });
        static::retrieved(function ($customer) {
            if ($customer->avatar)
                return $customer->avatar = url('storage/images/' . $customer->avatar);

        });
    }
}
