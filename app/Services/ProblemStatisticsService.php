<?php

namespace App\Services;

use App\Models\Problem\Problem;

class ProblemStatisticsService
{
    public function getStatistics(): array
    {
        return Problem::selectRaw("
            COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
            COUNT(CASE WHEN status = 'in_progress' THEN 1 END) as in_progress,
            COUNT(CASE WHEN status = 'done' THEN 1 END) as done,
            COUNT(CASE WHEN status = 'declined' THEN 1 END) as declined
        ")->first()->toArray();
    }
}
