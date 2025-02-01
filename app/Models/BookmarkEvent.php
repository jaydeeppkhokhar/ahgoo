<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class BookmarkEvent extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'bookmark_event';

    protected $fillable = [
        'event_id',
        'user_id',
    ];
}