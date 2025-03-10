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
        'user_id', 'post_id', 'is_showing_event', 'type', 'web_address', 'cover_pic', 'automatic_public', 'is_name_public_already_created', 'estimated_size', 'name_of_audience', 'age_from', 'age_to', 'gender', 'location', 'per_day_spent', 'total_days', 'total_cost', 'payment_method', 'event_location', 'is_confirm', 'event_type', 'target_scope', 'target_interaction', 'target_profile_visits'
    ];
}