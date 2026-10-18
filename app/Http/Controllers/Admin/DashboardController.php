<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Metrics;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Dashboard', [
            'headline' => Metrics::headline(),
            'series' => Metrics::dailySeries(14),
            'topExercises' => Metrics::topExercises(),
            'recentWorkouts' => Metrics::recentWorkouts(),
            'recentMembers' => Metrics::recentMembers(),
        ]);
    }
}
