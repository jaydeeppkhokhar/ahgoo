<?php
namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class Blocks extends Model
{
    use HasApiTokens, Notifiable;

    protected $connection = 'mongodb';
    protected $collection = 'block_and_reports';

    protected $fillable = [
        'block_to', 'block_by','is_report'
    ];
}