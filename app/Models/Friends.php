<?php
namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class Friends extends Model
{
    use HasApiTokens, Notifiable;

    protected $connection = 'mongodb';
    protected $collection = 'friends';

    protected $fillable = [
        'sent_to', 'sent_by','is_accepted'
    ];
}