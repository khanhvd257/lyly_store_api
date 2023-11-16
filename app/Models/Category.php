<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'image_name'];

    public static $rules = [
        'name' => 'required|string',
        'description' => 'required|string',
        'image_name' => 'nullable',
    ];

    protected static function boot()
    {
        parent::boot(); //
        static::retrieved(function ($category) {
            if ($category->image_name) {
                $category->image_url = asset('storage/images/' . $category->image_name);
            }
        });
    }

}
