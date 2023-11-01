<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReplyRating extends Model
{
    protected $table = "rating_replies";
    use HasFactory;
    protected $fillable = ['rating_id ', 'username', 'reply'];
}
