<?php
namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class Countries extends Model
{
    use HasApiTokens, Notifiable;

    protected $connection = 'mongodb';
    protected $collection = 'countries';

    protected $fillable = [
        'name', 'official_name', 'cca2', 'cca3', 'region', 'subregion', 'capital', 'population', 'area', 'languages', 'borders'
    ];
}
