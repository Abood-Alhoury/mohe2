<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\LookupUniversity;
use App\Models\LookupEducationLevel;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function index()
    {
        $totalApps = Application::count();

        // Breakdown by status
        $statusBreakdown = Application::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        // Breakdown by University
        $universityBreakdown = LookupUniversity::withCount('applications')->get();

        // Breakdown by Education Level
        $levelBreakdown = LookupEducationLevel::withCount(['educations as count' => function($q) {
            $q->select(DB::raw('count(distinct application_id)'));
        }])->get();

        return view('admin.reports.index', compact(
            'totalApps',
            'statusBreakdown',
            'universityBreakdown',
            'levelBreakdown'
        ));
    }
}
