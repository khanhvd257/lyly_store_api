<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    protected $table='accounts';
    protected $fillable = ['username', 'password'];
    use HasFactory;
    use Notifiable, HasApiTokens;

}
