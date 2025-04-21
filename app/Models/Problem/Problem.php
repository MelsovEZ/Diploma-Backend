<?php

namespace App\Models\Problem;

use App\Filters\SearchQuery;
use App\Models\Category\Category;
use App\Models\City\City;
use App\Models\Comment\Comment;
use App\Models\District\District;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Likes\Like;
use Illuminate\Http\Request;

class Problem extends Model
{
    use HasFactory;

    protected $primaryKey = 'problem_id';
    protected $dateFormat = 'Y-m-d H:i:s';
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'category_id',
        'city_id',
        'district_id',
        'address',
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

    public function city(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id', 'id');
    }
    public function district(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(District::class, 'district_id', 'id');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class, 'problem_id', 'problem_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'problem_id', 'problem_id');
    }

    public function report(): HasOne
    {
        return $this->hasOne(ProblemReport::class, 'problem_id', 'problem_id');
    }

    public function reportPhotos(): HasMany
    {
        return $this->hasMany(ProblemReportPhoto::class, 'problem_id');
    }


    public function scopeFilter(Builder $query, Request $request): Builder
    {

        $query = SearchQuery::apply($query, $request, ['title', 'description']);

        if ($request->filled('category_id') && is_array($request->category_id)) {
            $query->whereIn('category_id', $request->category_id);
        }


        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->input('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->where('created_at', '>=', $request->input('from_date'));
        }

        $query->when(!auth()->check() || !in_array(auth()->user()->status, ['admin', 'moderator']), function ($query) {
            return $query->whereNotIn('status', ['pending']);
        });


        return $query;
    }



}
