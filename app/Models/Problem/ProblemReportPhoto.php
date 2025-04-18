<?php
namespace App\Models\Problem;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProblemReportPhoto extends Model
{
    use HasFactory;

    protected $table = 'problem_report_photos';

    protected $primaryKey = 'photo_id';

    protected $fillable = [
        'report_id',
        'photo_url',
    ];
    public $timestamps = false;


    // Связь с моделью ProblemReport
    public function problemReport(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProblemReport::class, 'report_id');
    }
}
