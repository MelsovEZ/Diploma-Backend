<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Problem extends Model
{
    use HasFactory;

    protected $primaryKey = 'problem_id';
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'category_id',
        'status',
        'location_lat',
        'location_lng',
    ];
}
