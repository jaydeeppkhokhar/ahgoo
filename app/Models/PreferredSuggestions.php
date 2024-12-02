<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class PreferredSuggestions extends Model
{
    use HasApiTokens, Notifiable;

    protected $connection = 'mongodb';
    protected $collection = 'preferred_suggestions';

    protected $fillable = [
        'user_id', 'countries_suggestions', 'states_suggestions', 'interests_suggestions', 'age_groups_suggestions'
    ];
}