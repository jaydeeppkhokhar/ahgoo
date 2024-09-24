<?php
namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class AllUser extends Model
{
    use HasApiTokens, Notifiable;

    protected $connection = 'mongodb';
    protected $collection = 'users';

    protected $fillable = [
        'name', 'email', 'username', 'password', 'phone', 'country', 'user_type', 'profile_pic', 'cover_pic', 'step', 'country1', 'country2', 'country3', 'country4', 'country5', 'dob', 'gender', 'hobby1', 'hobby2', 'hobby3', 'hobby4', 'hobby5', 'latitude', 'longitude', 'profile_summary'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
