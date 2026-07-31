<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Application;

class CommitteeController extends Controller
{
    public function index()
    {
        // Only suspended applications that require General Committee deliberation
        $committeeApps = Application::with(['candidate', 'workUniversity', 'educations.level'])
            ->where('status', 'معلق')
            ->latest()
            ->get();

        return view('admin.committee.index', compact('committeeApps'));
    }

    public function decide(Request $request, $id)
    {
        $request->validate([
            'decision' => 'required|in:قيد الدراسة,معلق,مرفوض,تم الصدور',
        ]);

        $app = Application::findOrFail($id);
        $app->status = $request->decision;
        $app->save();

        return redirect()->route('admin.committee.index')->with('success', 'تم اتخاذ قرار اللجنة العامة على الطلب بنجاح ورصد النتيجة ('.$app->status.')');
    }
}
