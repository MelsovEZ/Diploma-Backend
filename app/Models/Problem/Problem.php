<?php

namespace App\Models\Problem;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    public function photos(): HasMany
    {
        return $this->hasMany(ProblemPhoto::class, 'problem_id', 'problem_id');
    }
}
