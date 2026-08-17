<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;
use App\Models\Application;
use App\Models\ApplicationDecision;
use App\Models\ApplicationMessage;
use Illuminate\Support\Facades\Auth;

class DecisionsController extends Controller
{
    // =========================================================================
    // 1. PAGE FOR ACADEMIC EQUIVALENCE DECISIONS (ACADEMIC MASTERS & DOCTORATE)
    // =========================================================================
    public function index(Request $request)
    {
        // Applications ready for decision issuing (Academic Masters & Doctorate)
        $approvedApps = Application::with(['candidate', 'workUniversity', 'latestDecision'])
            ->whereIn('status', ['بانتظار إصدار القرار', 'بانتظار صدور القرار'])
            ->where('request_type', 'not like', '%سماح%')
            ->where('request_type', 'not like', '%تدريسية%')
            ->where('request_type', 'not like', '%تطبيقي%')
            ->where('request_type', 'not like', '%بحوث%')
            ->where('request_type', 'not like', '%باحث%')
            ->latest()
            ->get();

        $search = $request->query('search');

        $issuedDecisions = ApplicationDecision::with('application.candidate', 'application.workUniversity')
            ->whereHas('application', function ($q) {
                $q->where('request_type', 'not like', '%سماح%')
                  ->where('request_type', 'not like', '%تدريسية%')
                  ->where('request_type', 'not like', '%تطبيقي%')
                  ->where('request_type', 'not like', '%بحوث%')
                  ->where('request_type', 'not like', '%باحث%');
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q2) use ($search) {
                    $q2->whereHas('application.candidate', function ($q) use ($search) {
                        $q->where('full_name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('application.workUniversity', function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('application', function ($q) use ($search) {
                        $q->where('request_type', 'like', '%' . $search . '%')
                          ->orWhere('application_no', 'like', '%' . $search . '%');
                    })
                    ->orWhere('decision_no', 'like', '%' . $search . '%')
                    ->orWhere('eligibility_decision_no', 'like', '%' . $search . '%');
                });
            })
            ->latest()
            ->get();

        return view('admin.decisions.index', compact('approvedApps', 'issuedDecisions', 'search'));
    }

    // =========================================================================
    // 2. PAGE FOR APPLIED MASTER EQUIVALENCE DECISIONS (ماجستير تطبيقي)
    // =========================================================================
    public function appliedIndex(Request $request)
    {
        // Applications ready for Applied Master decision issuing
        $approvedApps = Application::with(['candidate', 'workUniversity', 'latestDecision'])
            ->whereIn('status', ['بانتظار إصدار القرار', 'بانتظار صدور القرار'])
            ->where('request_type', 'like', '%تطبيقي%')
            ->latest()
            ->get();

        $search = $request->query('search');

        $issuedDecisions = ApplicationDecision::with('application.candidate', 'application.workUniversity')
            ->whereHas('application', function ($q) {
                $q->where('request_type', 'like', '%تطبيقي%');
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q2) use ($search) {
                    $q2->whereHas('application.candidate', function ($q) use ($search) {
                        $q->where('full_name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('application.workUniversity', function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('application', function ($q) use ($search) {
                        $q->where('request_type', 'like', '%' . $search . '%')
                          ->orWhere('application_no', 'like', '%' . $search . '%');
                    })
                    ->orWhere('decision_no', 'like', '%' . $search . '%')
                    ->orWhere('eligibility_decision_no', 'like', '%' . $search . '%');
                });
            })
            ->latest()
            ->get();

        return view('admin.decisions.applied_index', compact('approvedApps', 'issuedDecisions', 'search'));
    }

    public function appliedStore(Request $request)
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
            $eligFileName = 'Applied_Eligibility_Decision_No' . $safeEligDecNo . '_' . $safeAppNo . ($cleanCandidateName ? '_' . $cleanCandidateName : '') . '.' . $extElig;
            $eligibilityPath = $eligFile->storeAs('decisions', $eligFileName, 'public');
        }

        // 2. Process Applied Equivalence Decision File
        $ext = $request->file('decision_file')->getClientOriginalExtension();
        $decisionFileName = 'Applied_Decision_No' . $safeDecNo . '_' . $safeAppNo . ($cleanCandidateName ? '_' . $cleanCandidateName : '') . '.' . $ext;
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
            'notes'                     => $request->notes ?? 'قرار معادلة ماجستير تطبيقي صادرة رسمياً من مجلس التعليم العالي',
        ]);

        // Automatically update application status
        $app->status = 'تم الصدور';
        $app->save();

        // Send automated notification message to university
        $candidateFullName = $app->candidate ? $app->candidate->full_name : '';
        $eligibilityInfoText = $request->eligibility_decision_no ? " وقرار الأهلية برقم ({$request->eligibility_decision_no})" : "";

        ApplicationMessage::create([
            'application_id' => $app->id,
            'sender_id' => Auth::id() ?? 1,
            'message' => "📜 [إشعار رسمي - صدور قرار تعادل ماجستير تطبيقي]: تم صدور قرار معادلة الماجستير التطبيقي رسمياً برقم ({$request->decision_no}){$eligibilityInfoText} للطلب رقم (#{$app->application_no}) للمرشح ({$candidateFullName}). يمكنك الاطلاع على نسخة القرارات وتحميلها أصولاً.",
            'is_read' => false,
        ]);

        return redirect()->route('admin.applied_decisions.index')->with('success', 'تم تسجيل وإرسال قرار تعادل الماجستير التطبيقي وإشعار الجامعة المعنية بنجاح.');
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

        ApplicationMessage::create([
            'application_id' => $app->id,
            'sender_id' => Auth::id() ?? 1,
            'message' => "📜 [إشعار رسمي - صدور قرار التعادل]: تم صدور قرار معادلة الشهادة العلمية رسمياً برقم ({$request->decision_no}){$eligibilityInfoText} للطلب رقم (#{$app->application_no}) للمرشح ({$candidateFullName}). يمكنك الاطلاع على نسخة القرارات وتحميلها أصولاً.",
            'is_read' => false,
        ]);

        return redirect()->route('admin.decisions.index')->with('success', 'تم تسجيل وإرسال قرار الأهلية وقرار التعادل النهائي وإشعار الجامعة المعنية بنجاح.');
    }

    // =========================================================================
    // 2. PAGE FOR FACULTY TEACHING PERMISSION DECISIONS (سماح بالتدريس)
    // =========================================================================
    public function facultyIndex(Request $request)
    {
        // Applications ready for Faculty Permission decision issuing
        $approvedApps = Application::with(['candidate', 'workUniversity', 'latestDecision'])
            ->whereIn('status', ['بانتظار إصدار القرار', 'بانتظار صدور القرار'])
            ->where(function($q) {
                $q->where('request_type', 'like', '%سماح%')
                  ->orWhere('request_type', 'like', '%تدريسية%');
            })
            ->latest()
            ->get();

        $search = $request->query('search');

        $issuedDecisions = ApplicationDecision::with('application.candidate', 'application.workUniversity')
            ->whereHas('application', function ($q) {
                $q->where(function($sq) {
                    $sq->where('request_type', 'like', '%سماح%')
                       ->orWhere('request_type', 'like', '%تدريسية%');
                });
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q2) use ($search) {
                    $q2->whereHas('application.candidate', function ($q) use ($search) {
                        $q->where('full_name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('application.workUniversity', function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('application', function ($q) use ($search) {
                        $q->where('application_no', 'like', '%' . $search . '%');
                    })
                    ->orWhere('decision_no', 'like', '%' . $search . '%');
                });
            })
            ->latest()
            ->get();

        return view('admin.decisions.faculty_index', compact('approvedApps', 'issuedDecisions', 'search'));
    }

    public function facultyStore(Request $request)
    {
        $request->validate([
            'application_id' => 'required|exists:applications,id',
            'decision_no'    => 'required|string',
            'decision_date'  => 'required|date',
            'decision_file'  => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'notes'          => 'nullable|string',
        ]);

        $app = Application::findOrFail($request->application_id);

        if (!in_array($app->status, ['بانتظار إصدار القرار', 'بانتظار صدور القرار'])) {
            return redirect()->back()->with('error', 'لا يمكن إرفاق قرار لطلب حالته حالياً (' . $app->status . '). إصدار القرارات متاح فقط للطلبات بحالة (بانتظار إصدار القرار).');
        }

        $safeAppNo = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $app->application_no ?? ('App_' . $app->id));
        $safeDecNo = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $request->decision_no);
        $candidateName = $app->candidate ? preg_replace('/[^\p{L}\p{N}\s\-_]/u', '', $app->candidate->full_name) : '';
        $cleanCandidateName = trim(preg_replace('/\s+/', '_', $candidateName));

        // Process Faculty Permission Decision File
        $ext = $request->file('decision_file')->getClientOriginalExtension();
        $decisionFileName = 'Faculty_Permission_No' . $safeDecNo . '_' . $safeAppNo . ($cleanCandidateName ? '_' . $cleanCandidateName : '') . '.' . $ext;
        $path = $request->file('decision_file')->storeAs('decisions', $decisionFileName, 'public');

        // Create Decision Record
        $decision = ApplicationDecision::create([
            'application_id' => $request->application_id,
            'decision_no'     => $request->decision_no,
            'decision_date'   => $request->decision_date,
            'file_path'       => $path,
            'notes'           => $request->notes ?? 'قرار سماح بالتدريس صادر رسمياً لعضو هيئة تدريسية',
        ]);

        // Automatically update application status
        $app->status = 'تم الصدور';
        $app->save();

        // Send automated notification message to university
        $candidateFullName = $app->candidate ? $app->candidate->full_name : '';
        ApplicationMessage::create([
            'application_id' => $app->id,
            'sender_id' => Auth::id() ?? 1,
            'message' => "📜 [إشعار رسمي - صدور قرار السماح بالتدريس]: تم صدور قرار السماح بالتدريس رسمياً برقم ({$request->decision_no}) للطلب رقم (#{$app->application_no}) للمرشح ({$candidateFullName}). يمكنك الاطلاع على نسخة القرار وتحميلها أصولاً.",
            'is_read' => false,
        ]);

        return redirect()->route('admin.faculty_decisions.index')->with('success', 'تم تسجيل وإرسال قرار السماح بالتدريس النهائي وإشعار الجامعة المعنية بنجاح.');
    }

    // =========================================================================
    // 4. PAGE FOR RESEARCH CENTER SCIENTIST DECISIONS (باحث في مراكز البحوث)
    // =========================================================================
    public function researchIndex(Request $request)
    {
        // Applications ready for Research Center Scientist decision issuing
        $approvedApps = Application::with(['candidate', 'workUniversity', 'latestDecision'])
            ->whereIn('status', ['بانتظار إصدار القرار', 'بانتظار صدور القرار'])
            ->where(function($q) {
                $q->where('request_type', 'like', '%بحوث%')
                  ->orWhere('request_type', 'like', '%باحث%');
            })
            ->latest()
            ->get();

        $search = $request->query('search');

        $issuedDecisions = ApplicationDecision::with('application.candidate', 'application.workUniversity')
            ->whereHas('application', function ($q) {
                $q->where(function($sq) {
                    $sq->where('request_type', 'like', '%بحوث%')
                       ->orWhere('request_type', 'like', '%باحث%');
                });
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q2) use ($search) {
                    $q2->whereHas('application.candidate', function ($q) use ($search) {
                        $q->where('full_name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('application.workUniversity', function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('application', function ($q) use ($search) {
                        $q->where('application_no', 'like', '%' . $search . '%');
                    })
                    ->orWhere('decision_no', 'like', '%' . $search . '%');
                });
            })
            ->latest()
            ->get();

        return view('admin.decisions.research_index', compact('approvedApps', 'issuedDecisions', 'search'));
    }

    public function researchStore(Request $request)
    {
        $request->validate([
            'application_id' => 'required|exists:applications,id',
            'decision_no'    => 'required|string',
            'decision_date'  => 'required|date',
            'decision_file'  => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'notes'          => 'nullable|string',
        ]);

        $app = Application::findOrFail($request->application_id);

        if (!in_array($app->status, ['بانتظار إصدار القرار', 'بانتظار صدور القرار'])) {
            return redirect()->back()->with('error', 'لا يمكن إرفاق قرار لطلب حالته حالياً (' . $app->status . '). إصدار القرارات متاح فقط للطلبات بحالة (بانتظار إصدار القرار).');
        }

        $safeAppNo = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $app->application_no ?? ('App_' . $app->id));
        $safeDecNo = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $request->decision_no);
        $candidateName = $app->candidate ? preg_replace('/[^\p{L}\p{N}\s\-_]/u', '', $app->candidate->full_name) : '';
        $cleanCandidateName = trim(preg_replace('/\s+/', '_', $candidateName));

        // Process Research Decision File
        $ext = $request->file('decision_file')->getClientOriginalExtension();
        $decisionFileName = 'Research_Decision_No' . $safeDecNo . '_' . $safeAppNo . ($cleanCandidateName ? '_' . $cleanCandidateName : '') . '.' . $ext;
        $path = $request->file('decision_file')->storeAs('decisions', $decisionFileName, 'public');

        // Create Decision Record
        $decision = ApplicationDecision::create([
            'application_id' => $request->application_id,
            'decision_no'     => $request->decision_no,
            'decision_date'   => $request->decision_date,
            'file_path'       => $path,
            'notes'           => $request->notes ?? 'قرار اعتماد باحث في مركز بحوث صادر رسمياً للتدريس في الجامعات الخاصة',
        ]);

        // Automatically update application status
        $app->status = 'تم الصدور';
        $app->save();

        // Send automated notification message to university
        $candidateFullName = $app->candidate ? $app->candidate->full_name : '';
        ApplicationMessage::create([
            'application_id' => $app->id,
            'sender_id' => Auth::id() ?? 1,
            'message' => "📜 [إشعار رسمي - صدور قرار باحث مراكز البحوث]: تم صدور قرار معادلة الدكتوراه والاعتماد للتدريس رسمياً برقم ({$request->decision_no}) للطلب رقم (#{$app->application_no}) للمرشح ({$candidateFullName}). يمكنك الاطلاع على نسخة القرار وتحميلها أصولاً.",
            'is_read' => false,
        ]);

        return redirect()->route('admin.research_decisions.index')->with('success', 'تم تسجيل وإرسال قرار باحث مراكز البحوث النهائي وإشعار الجامعة المعنية بنجاح.');
    }
}
