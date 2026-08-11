<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\LookupUniversity;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function index()
    {
        $totalApps = Application::count();

        // Official 6 statuses
        $officialStatuses = [
            'تحت التدقيق الأولي'   => ['icon' => 'fa-magnifying-glass text-primary'],
            'بانتظار الوثائق'     => ['icon' => 'fa-hourglass-half text-warning'],
            'لجنة عامة'           => ['icon' => 'fa-users text-info'],
            'بانتظار لجنة إنتاج علمي' => ['icon' => 'fa-flask text-purple'],
            'بانتظار المقابلة'     => ['icon' => 'fa-user-tie text-secondary'],
            'تم الصدور'           => ['icon' => 'fa-award text-success'],
        ];

        $statusCounts = Application::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $statusBreakdown = [];
        foreach ($officialStatuses as $stName => $stMeta) {
            $statusBreakdown[$stName] = [
                'count' => $statusCounts[$stName] ?? 0,
                'icon'  => $stMeta['icon'],
            ];
        }

        // University distribution
        $universityBreakdown = LookupUniversity::withCount('applications')
            ->orderByDesc('applications_count')
            ->get();

        return view('admin.reports.index', compact(
            'totalApps',
            'statusBreakdown',
            'universityBreakdown'
        ));
    }

    public function exportPdf()
    {
        $totalApps = Application::count();

        $officialStatuses = [
            'تحت التدقيق الأولي'   => ['icon' => 'fa-magnifying-glass text-primary'],
            'بانتظار الوثائق'     => ['icon' => 'fa-hourglass-half text-warning'],
            'لجنة عامة'           => ['icon' => 'fa-users text-info'],
            'بانتظار لجنة إنتاج علمي' => ['icon' => 'fa-flask text-purple'],
            'بانتظار المقابلة'     => ['icon' => 'fa-user-tie text-secondary'],
            'تم الصدور'           => ['icon' => 'fa-award text-success'],
        ];

        $statusCounts = Application::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $statusBreakdown = [];
        foreach ($officialStatuses as $stName => $stMeta) {
            $statusBreakdown[$stName] = [
                'count' => $statusCounts[$stName] ?? 0,
                'icon'  => $stMeta['icon'],
            ];
        }

        $universityBreakdown = LookupUniversity::withCount('applications')
            ->orderByDesc('applications_count')
            ->get();

        return view('admin.reports.pdf', compact(
            'totalApps',
            'statusBreakdown',
            'universityBreakdown'
        ));
    }
}
