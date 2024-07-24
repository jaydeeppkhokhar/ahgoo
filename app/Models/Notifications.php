<?php
namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class Notifications extends Model
{
    use HasApiTokens, Notifiable;

    protected $connection = 'mongodb';
    protected $collection = 'notifications';

    protected $fillable = [
        'user_id', 'relavant_id', 'relavant_image', 'message', 'type', 'is_seen',
    ];
}