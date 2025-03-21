<?php

namespace App\Models\Likes;

use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    protected $fillable = ['user_id', 'problem_id'];

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function problem()
    {
        return $this->belongsTo(\App\Models\Problem::class, 'problem_id', 'problem_id');
    }
}

