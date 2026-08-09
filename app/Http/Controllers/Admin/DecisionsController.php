<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\ApplicationDecision;

class DecisionsController extends Controller
{
    public function index(Request $request)
    {
        // Applications ready for decision issuing (Requirement 4: Exclude 'بانتظار الوثائق', 'مرفوض', 'معلق')
        $approvedApps = Application::with(['candidate', 'workUniversity', 'latestDecision'])
            ->whereNotIn('status', ['بانتظار الوثائق', 'مرفوض', 'معلق'])
            ->latest()
            ->get();

        $search = $request->query('search');

        $issuedDecisions = ApplicationDecision::with('application.candidate', 'application.workUniversity')
            ->when($search, function ($query) use ($search) {
                $query->whereHas('application.candidate', function ($q) use ($search) {
                    $q->where('full_name', 'like', '%' . $search . '%');
                })->orWhereHas('application.workUniversity', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                });
            })
            ->latest()
            ->get();

        return view('admin.decisions.index', compact('approvedApps', 'issuedDecisions', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'application_id' => 'required|exists:applications,id',
            'decision_no' => 'required|string',
            'decision_date' => 'required|date',
            'decision_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $app = Application::findOrFail($request->application_id);
        $forbiddenStatuses = ['بانتظار الوثائق', 'مرفوض', 'معلق'];

        if (in_array($app->status, $forbiddenStatuses)) {
            return redirect()->back()->with('error', 'لا يمكن إرفاق قرار تعادل لطلب حالته حالياً (' . $app->status . ').');
        }

        $path = $request->file('decision_file')->store('decisions', 'public');

        $decision = ApplicationDecision::create([
            'application_id' => $request->application_id,
            'decision_no' => $request->decision_no,
            'decision_date' => $request->decision_date,
            'file_path' => $path,
            'notes' => $request->notes ?? 'قرار معادلة صادرة رسمياً من مجلس التعليم العالي',
        ]);

        // Automatically update application status
        $app->status = 'تم الصدور';
        $app->save();

        // Requirement 3: Send automated message to university
        $candidateName = $app->candidate ? $app->candidate->full_name : '';
        \App\Models\ApplicationMessage::create([
            'application_id' => $app->id,
            'sender_id' => \Illuminate\Support\Facades\Auth::id() ?? 1,
            'message' => "📜 [إشعار رسمي - صدور قرار التعادل]: تم صدور قرار معادلة الشهادة العلمية رسمياً برقم ({$request->decision_no}) للطلب رقم (#{$app->application_no}) للمرشح ({$candidateName}). يمكنك الاطلاع على نسخة القرار وتحميلها أصولاً.",
            'is_read' => false,
        ]);

        return redirect()->route('admin.decisions.index')->with('success', 'تم إصدار وإرسال قرار التعادل وإشعار الجامعة المعنية بنجاح.');
    }
}
