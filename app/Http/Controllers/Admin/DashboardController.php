<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\LookupUniversity;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $totalApps = Application::count();
        $underStudyCount = Application::where('status', 'تحت التدقيق الأولي')->count();
        $approvedCount = Application::where('status', 'تم الصدور')->count();
        $committeeCount = Application::where('status', 'معلق')->count();
        $rejectedCount = Application::where('status', 'مرفوض')->count();

        // Recent applications for admin review
        $recentApplications = Application::with(['candidate', 'workUniversity', 'user'])
            ->latest()
            ->take(10)
            ->get();

        $totalUniversities = LookupUniversity::count();

        return view('admin.dashboard', compact(
            'totalApps',
            'underStudyCount',
            'approvedCount',
            'committeeCount',
            'rejectedCount',
            'recentApplications',
            'totalUniversities'
        ));
    }
}
