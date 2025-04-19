<?php
namespace App\Models\Problem;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProblemReport extends Model
{
    use HasFactory;

    protected $primaryKey = 'report_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $table = 'problem_reports';

    public $timestamps = false;

    protected $fillable = [
        'problem_id',
        'moderator_id',
        'assigned_at',
        'submitted_at',
        'description',
        'status',
    ];

    public function problem(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Problem::class, 'problem_id');
    }

    public function moderator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'moderator_id');
    }
    public function photos(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProblemReportPhoto::class, 'report_id', 'report_id');
    }

}
