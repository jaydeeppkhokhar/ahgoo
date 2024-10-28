<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class Events extends Model
{
    use HasApiTokens, Notifiable;

    protected $connection = 'mongodb';
    protected $collection = 'events';

    // protected $fillable = [
    //     'user_id', 'is_showing_event', 'type', 'event_name', 'event_start', 'location', 'cover_pic', 'automatic_public', 'estimated_size', 'age_from', 'total_days', 'event_location', 'is_confirm'
    // ];
    protected $fillable = [
        'user_id', 'event_type', 'event_name', 'event_subtitle', 'event_description', 'is_permanent', 'event_date', 'duration', 'cover_pic', 'location', 'event_category', 'attendees_type', 'is_confirmed'
    ];
}