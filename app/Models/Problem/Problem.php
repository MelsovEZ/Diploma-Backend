<?php

namespace App\Models\Problem;

use App\Filters\SearchQuery;
use App\Models\Category\Category;
use App\Models\Comment\Comment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Likes\Like;
use Illuminate\Http\Request;

class Problem extends Model
{
    use HasFactory;

    protected $primaryKey = 'problem_id';

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'category_id',
        'city_id',
        'status',
        'location_lat',
        'location_lng',
    ];
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function photos(): HasMany
    {
        return $this->hasMany(ProblemPhoto::class, 'problem_id', 'problem_id');
    }

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class, 'problem_id', 'problem_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'problem_id', 'problem_id');
    }

    public function scopeFilter(Builder $query, Request $request): Builder
    {

        $query = SearchQuery::apply($query, $request, ['title', 'description']);

        if ($request->filled('category_id')) {
            $query->whereIn('category_id', $request->input('category_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        } else {
            $query->where('status', 'in_progress');
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->input('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->input('to_date'));
        }

        $query->when(!auth()->check() || !in_array(auth()->user()->status, ['admin', 'moderator']), function ($query) {
            return $query->whereNotIn('status', ['pending']);
        });


        return $query;
    }



}
