<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class Promotion extends Model
{
    use HasApiTokens, Notifiable;

    protected $connection = 'mongodb';
    protected $collection = 'promotions';

    protected $fillable = [
        'user_id', 'post_id', 'is_showing_event', 'type', 'automatic_public', 'estimated_size', 'name_of_audience', 'age_from', 'age_to', 'gender', 'location', 'per_day_spent', 'total_days', 'event_location', 'is_confirm'
    ];
}