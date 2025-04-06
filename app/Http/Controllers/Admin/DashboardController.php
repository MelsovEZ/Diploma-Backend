<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ProblemStatisticsService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    protected ProblemStatisticsService $statisticsService;

    public function __construct(ProblemStatisticsService $statisticsService)
    {
        $this->statisticsService = $statisticsService;
    }

    public function index(): JsonResponse
    {
        $stats = $this->statisticsService->getStatistics();

        return response()->json([
            'data' => $stats
        ]);
    }
}
