<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;
use App\Models\Application;
use App\Models\ApplicationDecision;

class DecisionsController extends Controller
{
    public function index(Request $request)
    {
        // Applications ready for decision issuing (Only status 'بانتظار إصدار القرار' / 'بانتظار صدور القرار')
        $approvedApps = Application::with(['candidate', 'workUniversity', 'latestDecision'])
            ->whereIn('status', ['بانتظار إصدار القرار', 'بانتظار صدور القرار'])
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
            'application_id'           => 'required|exists:applications,id',
            'eligibility_decision_no'   => 'nullable|string',
            'eligibility_decision_date' => 'nullable|date',
            'eligibility_decision_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'decision_no'              => 'required|string',
            'decision_date'            => 'required|date',
            'decision_file'            => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'notes'                    => 'nullable|string',
        ]);

        $app = Application::findOrFail($request->application_id);

        if (!in_array($app->status, ['بانتظار إصدار القرار', 'بانتظار صدور القرار'])) {
            return redirect()->back()->with('error', 'لا يمكن إرفاق قرار لطلب حالته حالياً (' . $app->status . '). إصدار القرارات متاح فقط للطلبات بحالة (بانتظار إصدار القرار).');
        }

        $safeAppNo = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $app->application_no ?? ('App_' . $app->id));
        $safeDecNo = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $request->decision_no);
        $candidateName = $app->candidate ? preg_replace('/[^\p{L}\p{N}\s\-_]/u', '', $app->candidate->full_name) : '';
        $cleanCandidateName = trim(preg_replace('/\s+/', '_', $candidateName));

        // 1. Process Eligibility Decision File if uploaded
        $eligibilityPath = null;
        if ($request->hasFile('eligibility_decision_file')) {
            $eligFile = $request->file('eligibility_decision_file');
            $safeEligDecNo = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $request->eligibility_decision_no ?? 'Elig');
            $extElig = $eligFile->getClientOriginalExtension();
            $eligFileName = 'Eligibility_Decision_No' . $safeEligDecNo . '_' . $safeAppNo . ($cleanCandidateName ? '_' . $cleanCandidateName : '') . '.' . $extElig;
            $eligibilityPath = $eligFile->storeAs('decisions', $eligFileName, 'public');
        }

        // 2. Process Equivalence Decision File
        $ext = $request->file('decision_file')->getClientOriginalExtension();
        $decisionFileName = 'Official_Decision_No' . $safeDecNo . '_' . $safeAppNo . ($cleanCandidateName ? '_' . $cleanCandidateName : '') . '.' . $ext;
        $path = $request->file('decision_file')->storeAs('decisions', $decisionFileName, 'public');

        // 3. Create Decision Record
        $decision = ApplicationDecision::create([
            'application_id'            => $request->application_id,
            'eligibility_decision_no'   => $request->eligibility_decision_no,
            'eligibility_decision_date' => $request->eligibility_decision_date,
            'eligibility_file_path'     => $eligibilityPath,
            'decision_no'               => $request->decision_no,
            'decision_date'             => $request->decision_date,
            'file_path'                 => $path,
            'notes'                     => $request->notes ?? 'قرار معادلة صادرة رسمياً من مجلس التعليم العالي',
        ]);

        // Automatically update application status
        $app->status = 'تم الصدور';
        $app->save();

        // Send automated notification message to university
        $candidateFullName = $app->candidate ? $app->candidate->full_name : '';
        $eligibilityInfoText = $request->eligibility_decision_no ? " وقرار الأهلية برقم ({$request->eligibility_decision_no})" : "";
        
        \App\Models\ApplicationMessage::create([
            'application_id' => $app->id,
            'sender_id' => \Illuminate\Support\Facades\Auth::id() ?? 1,
            'message' => "📜 [إشعار رسمي - صدور قرار التعادل]: تم صدور قرار معادلة الشهادة العلمية رسمياً برقم ({$request->decision_no}){$eligibilityInfoText} للطلب رقم (#{$app->application_no}) للمرشح ({$candidateFullName}). يمكنك الاطلاع على نسخة القرارات وتحميلها أصولاً.",
            'is_read' => false,
        ]);

        return redirect()->route('admin.decisions.index')->with('success', 'تم تسجيل وإرسال قرار الأهلية وقرار التعادل النهائي وإشعار الجامعة المعنية بنجاح.');
    }
}
