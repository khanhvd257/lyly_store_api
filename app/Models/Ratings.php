<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ratings extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'username', 'rating', 'comment', 'image_name'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
