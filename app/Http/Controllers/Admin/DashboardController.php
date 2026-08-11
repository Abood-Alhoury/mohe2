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
        $totalApps = Application::where('status', '!=', 'مسودة')->count();
        $underStudyCount = Application::where('status', 'تحت التدقيق الأولي')->count();
        $approvedCount = Application::where('status', 'تم الصدور')->count();
        $committeeCount = Application::where('status', 'لجنة عامة')->count();
        $documentsCount = Application::where('status', 'بانتظار الوثائق')->count();
        $prodScienceCount = Application::where('status', 'بانتظار لجنة إنتاج علمي')->count();
        $interviewCount = Application::where('status', 'بانتظار المقابلة')->count();

        // Recent applications for admin review
        $recentApplications = Application::where('status', '!=', 'مسودة')
            ->with(['candidate', 'workUniversity', 'user'])
            ->latest()
            ->take(10)
            ->get();

        $totalUniversities = LookupUniversity::count();

        return view('admin.dashboard', compact(
            'totalApps',
            'underStudyCount',
            'approvedCount',
            'committeeCount',
            'documentsCount',
            'prodScienceCount',
            'interviewCount',
            'recentApplications',
            'totalUniversities'
        ));
    }
}
