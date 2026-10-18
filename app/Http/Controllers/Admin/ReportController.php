<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Metrics;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Report', [
            'headline' => Metrics::headline(),
            'topMembers' => Metrics::topMembers(),
            'goals' => Metrics::goalDistribution(),
        ]);
    }
}
