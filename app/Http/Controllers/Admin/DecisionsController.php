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
        // Approved applications ready for decision issuing
        $approvedApps = Application::with(['candidate', 'workUniversity', 'latestDecision'])
            ->whereIn('status', ['موافق عليها', 'تم الصدور', 'مقبول مبدئياً', 'قيد الدراسة'])
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

        $path = $request->file('decision_file')->store('decisions', 'public');

        $decision = ApplicationDecision::create([
            'application_id' => $request->application_id,
            'decision_no' => $request->decision_no,
            'decision_date' => $request->decision_date,
            'file_path' => $path,
            'notes' => $request->notes ?? 'قرار معادلة صادرة رسمياً من مجلس التعليم العالي',
        ]);

        // Automatically update application status
        $app = Application::findOrFail($request->application_id);
        $app->status = 'تم الصدور';
        $app->save();

        return redirect()->route('admin.decisions.index')->with('success', 'تم إصدار وإرسال قرار التعادل للجامعة المعنية بنجاح');
    }
}
