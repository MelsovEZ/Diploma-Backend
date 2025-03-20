<?php

namespace App\Models\Comment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'problem_id', 'text', 'created_at', 'updated_at'];

    protected $primaryKey = 'id';

    public $timestamps = false;
}
