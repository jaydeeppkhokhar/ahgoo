<?php
namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class Posts extends Model
{
    use HasApiTokens, Notifiable;

    protected $connection = 'mongodb';
    protected $collection = 'posts';

    protected $fillable = [
        'user_id','caption','media','is_active','is_deleted'
    ];

    public function user()
    {
        return $this->belongsTo(AllUser::class, 'user_id', '_id');
    }
}
