<?php

namespace App\Models\Problem;

use App\Models\Comment\Comment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Likes\Like;

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

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class, 'problem_id', 'problem_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'problem_id', 'problem_id');
    }

}
