<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProblemPhoto extends Model
{
    use HasFactory;

    protected $primaryKey = 'photo_id';

    protected $fillable = ['problem_id', 'photo_url'];

    public function problem(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Problem::class);
    }
}
