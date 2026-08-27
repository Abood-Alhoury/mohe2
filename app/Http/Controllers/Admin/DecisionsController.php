<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;
use App\Models\Application;
use App\Models\ApplicationDecision;
use App\Models\ApplicationMessage;
use App\Models\ApplicationRequestType;
use Illuminate\Support\Facades\Auth;

class DecisionsController extends Controller
{
    /**
     * Get matching ApplicationRequestType IDs for a given decision section.
     */
    private function getRequestTypeIds(string $section): array
    {
        switch ($section) {
            case 'syrian_master':
                $ids = ApplicationRequestType::where(function($q) {
                    $q->where('name', 'like', '%ماجستير%')
                      ->orWhere('name', 'like', '%ماستر%');
                })
                ->where('name', 'like', '%داخلي%')
                ->where('name', 'not like', '%تطبيقي%')
                ->where('name', 'not like', '%خارجي%')
                ->pluck('id')
                ->toArray();
                return !empty($ids) ? $ids : [2];

            case 'syrian_doctorate':
                $ids = ApplicationRequestType::where(function($q) {
                    $q->where('name', 'like', '%دكتوراه%')
                      ->orWhere('name', 'like', '%دكتورة%');
                })
                ->where('name', 'not like', '%خارجي%')
                ->pluck('id')
                ->toArray();
                return !empty($ids) ? $ids : [3];

            case 'applied_master':
                $ids = ApplicationRequestType::where('name', 'like', '%تطبيقي%')
                    ->where('name', 'not like', '%خارجي%')
                    ->pluck('id')
                    ->toArray();
                return !empty($ids) ? $ids : [1];

            case 'faculty_permission':
                $ids = ApplicationRequestType::where(function($q) {
                    $q->where('name', 'like', '%سماح%')
                      ->orWhere('name', 'like', '%تدريس%');
                })
                ->pluck('id')
                ->toArray();
                return !empty($ids) ? $ids : [4];

            case 'foreign_master_applied':
                $ids = ApplicationRequestType::where('name', 'like', '%خارجي%')
                    ->where('name', 'like', '%تطبيقي%')
                    ->pluck('id')
                    ->toArray();
                return !empty($ids) ? $ids : [5];

            case 'foreign_master_theoretical':
                $ids = ApplicationRequestType::where('name', 'like', '%خارجي%')
                    ->where('name', 'like', '%نظري%')
                    ->pluck('id')
                    ->toArray();
                return !empty($ids) ? $ids : [6];

            default:
                return [];
        }
    }

    // =========================================================================
    // 1. PAGE FOR MASTER EQUIVALENCE DECISIONS (تعادل الماجستير الداخلي)
    // =========================================================================
    public function index(Request $request)
    {
        $typeIds = $this->getRequestTypeIds('syrian_master');

        // Applications ready for decision issuing (Syrian Academic Masters)
        $approvedApps = Application::with(['candidate', 'workUniversity', 'latestDecision'])
            ->whereIn('status', ['بانتظار إصدار القرار', 'بانتظار صدور القرار', 7])
            ->whereIn('request_type', $typeIds)
            ->latest()
            ->get();

        if ($request->filled('app_id')) {
            $targetApp = Application::with(['candidate', 'workUniversity', 'latestDecision'])->find($request->query('app_id'));
            if ($targetApp && in_array($targetApp->status, ['بانتظار إصدار القرار', 'بانتظار صدور القرار', 7]) && !$approvedApps->contains('id', $targetApp->id)) {
                $approvedApps->prepend($targetApp);
            }
        }

        $search = $request->query('search');

        $issuedDecisions = ApplicationDecision::with('application.candidate', 'application.workUniversity')
            ->whereHas('application', function ($q) use ($typeIds) {
                $q->whereIn('request_type', $typeIds);
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
                    ->orWhere('decision_no', 'like', '%' . $search . '%')
                    ->orWhere('eligibility_decision_no', 'like', '%' . $search . '%');
                });
            })
            ->latest()
            ->get();

        return view('admin.decisions.index', compact('approvedApps', 'issuedDecisions', 'search'));
    }

    // =========================================================================
    // 2. PAGE FOR DOCTORATE EQUIVALENCE DECISIONS (تعادل الدكتوراه الداخلية)
    // =========================================================================
    public function doctorateIndex(Request $request)
    {
        $typeIds = $this->getRequestTypeIds('syrian_doctorate');

        // Applications ready for Doctorate decision issuing
        $approvedApps = Application::with(['candidate', 'workUniversity', 'latestDecision'])
            ->whereIn('status', ['بانتظار إصدار القرار', 'بانتظار صدور القرار', 7])
            ->whereIn('request_type', $typeIds)
            ->latest()
            ->get();

        if ($request->filled('app_id')) {
            $targetApp = Application::with(['candidate', 'workUniversity', 'latestDecision'])->find($request->query('app_id'));
            if ($targetApp && in_array($targetApp->status, ['بانتظار إصدار القرار', 'بانتظار صدور القرار', 7]) && !$approvedApps->contains('id', $targetApp->id)) {
                $approvedApps->prepend($targetApp);
            }
        }

        $search = $request->query('search');

        $issuedDecisions = ApplicationDecision::with('application.candidate', 'application.workUniversity')
            ->whereHas('application', function ($q) use ($typeIds) {
                $q->whereIn('request_type', $typeIds);
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
                    ->orWhere('decision_no', 'like', '%' . $search . '%')
                    ->orWhere('eligibility_decision_no', 'like', '%' . $search . '%');
                });
            })
            ->latest()
            ->get();

        return view('admin.decisions.doctorate_index', compact('approvedApps', 'issuedDecisions', 'search'));
    }

    // =========================================================================
    // 3. PAGE FOR APPLIED MASTER EQUIVALENCE DECISIONS (ماجستير تطبيقي)
    // =========================================================================
    public function appliedIndex(Request $request)
    {
        $typeIds = $this->getRequestTypeIds('applied_master');

        // Applications ready for Applied Master decision issuing
        $approvedApps = Application::with(['candidate', 'workUniversity', 'latestDecision'])
            ->whereIn('status', ['بانتظار إصدار القرار', 'بانتظار صدور القرار', 7])
            ->whereIn('request_type', $typeIds)
            ->latest()
            ->get();

        if ($request->filled('app_id')) {
            $targetApp = Application::with(['candidate', 'workUniversity', 'latestDecision'])->find($request->query('app_id'));
            if ($targetApp && in_array($targetApp->status, ['بانتظار إصدار القرار', 'بانتظار صدور القرار', 7]) && !$approvedApps->contains('id', $targetApp->id)) {
                $approvedApps->prepend($targetApp);
            }
        }

        $search = $request->query('search');

        $issuedDecisions = ApplicationDecision::with('application.candidate', 'application.workUniversity')
            ->whereHas('application', function ($q) use ($typeIds) {
                $q->whereIn('request_type', $typeIds);
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
            'message' => "📜 [إشعار رسمي - صدور قرار تعادل ماجستير]: تم صدور قرار معادلة الماجستير رسمياً برقم ({$request->decision_no}){$eligibilityInfoText} للطلب رقم (#{$app->application_no}) للمرشح ({$candidateFullName}). يمكنك الاطلاع على نسخة القرارات وتحميلها أصولاً.",
            'is_read' => false,
        ]);

        return redirect()->route('admin.decisions.index')->with('success', 'تم تسجيل وإرسال قرار الأهلية وقرار تعادل الماجستير النهائي وإشعار الجامعة المعنية بنجاح.');
    }

    public function doctorateStore(Request $request)
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
            $eligFileName = 'Doctorate_Eligibility_Decision_No' . $safeEligDecNo . '_' . $safeAppNo . ($cleanCandidateName ? '_' . $cleanCandidateName : '') . '.' . $extElig;
            $eligibilityPath = $eligFile->storeAs('decisions', $eligFileName, 'public');
        }

        // 2. Process Doctorate Equivalence Decision File
        $ext = $request->file('decision_file')->getClientOriginalExtension();
        $decisionFileName = 'Doctorate_Official_Decision_No' . $safeDecNo . '_' . $safeAppNo . ($cleanCandidateName ? '_' . $cleanCandidateName : '') . '.' . $ext;
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
            'notes'                     => $request->notes ?? 'قرار معادلة دكتوراه سورية صادر رسمياً من مجلس التعليم العالي',
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
            'message' => "📜 [إشعار رسمي - صدور قرار تعادل دكتوراه]: تم صدور قرار معادلة الدكتوراه رسمياً برقم ({$request->decision_no}){$eligibilityInfoText} للطلب رقم (#{$app->application_no}) للمرشح ({$candidateFullName}). يمكنك الاطلاع على نسخة القرارات وتحميلها أصولاً.",
            'is_read' => false,
        ]);

        return redirect()->route('admin.doctorate_decisions.index')->with('success', 'تم تسجيل وإرسال قرار الأهلية وقرار تعادل الدكتوراه النهائي وإشعار الجامعة المعنية بنجاح.');
    }

    // =========================================================================
    // 2. PAGE FOR FACULTY TEACHING PERMISSION DECISIONS (سماح بالتدريس)
    // =========================================================================
    public function facultyIndex(Request $request)
    {
        $typeIds = $this->getRequestTypeIds('faculty_permission');

        // Applications ready for Faculty Permission decision issuing
        $approvedApps = Application::with(['candidate', 'workUniversity', 'latestDecision'])
            ->whereIn('status', ['بانتظار إصدار القرار', 'بانتظار صدور القرار', 7])
            ->whereIn('request_type', $typeIds)
            ->latest()
            ->get();

        if ($request->filled('app_id')) {
            $targetApp = Application::with(['candidate', 'workUniversity', 'latestDecision'])->find($request->query('app_id'));
            if ($targetApp && in_array($targetApp->status, ['بانتظار إصدار القرار', 'بانتظار صدور القرار', 7]) && !$approvedApps->contains('id', $targetApp->id)) {
                $approvedApps->prepend($targetApp);
            }
        }

        $search = $request->query('search');

        $issuedDecisions = ApplicationDecision::with('application.candidate', 'application.workUniversity')
            ->whereHas('application', function ($q) use ($typeIds) {
                $q->whereIn('request_type', $typeIds);
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
                    ->orWhere('decision_no', 'like', '%' . $search . '%')
                    ->orWhere('eligibility_decision_no', 'like', '%' . $search . '%');
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
    // 5. PAGE FOR FOREIGN MASTER APPLIED DECISIONS (تعادل الماجستير الخارجي التطبيقي - بدون أهلية)
    // =========================================================================
    public function foreignMasterAppliedIndex(Request $request)
    {
        $typeIds = $this->getRequestTypeIds('foreign_master_applied');

        // Applications ready for Foreign Master Applied decision issuing
        $approvedApps = Application::with(['candidate', 'workUniversity', 'latestDecision', 'educations.level'])
            ->whereIn('status', ['بانتظار إصدار القرار', 'بانتظار صدور القرار', 7])
            ->whereIn('request_type', $typeIds)
            ->latest()
            ->get();

        if ($request->filled('app_id')) {
            $targetApp = Application::with(['candidate', 'workUniversity', 'latestDecision', 'educations.level'])->find($request->query('app_id'));
            if ($targetApp && in_array($targetApp->status, ['بانتظار إصدار القرار', 'بانتظار صدور القرار', 7]) && !$approvedApps->contains('id', $targetApp->id)) {
                $approvedApps->prepend($targetApp);
            }
        }

        $search = $request->query('search');

        $issuedDecisions = ApplicationDecision::with('application.candidate', 'application.workUniversity')
            ->whereHas('application', function ($q) use ($typeIds) {
                $q->whereIn('request_type', $typeIds);
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
                    ->orWhere('decision_no', 'like', '%' . $search . '%')
                    ->orWhere('eligibility_decision_no', 'like', '%' . $search . '%');
                });
            })
            ->latest()
            ->get();

        return view('admin.decisions.foreign_master_applied_index', compact('approvedApps', 'issuedDecisions', 'search'));
    }

    public function foreignMasterAppliedStore(Request $request)
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

        // 1. Process Main Equivalence Decision File
        $ext = $request->file('decision_file')->getClientOriginalExtension();
        $decisionFileName = 'Foreign_Master_Applied_Decision_No' . $safeDecNo . '_' . $safeAppNo . ($cleanCandidateName ? '_' . $cleanCandidateName : '') . '.' . $ext;
        $path = $request->file('decision_file')->storeAs('decisions', $decisionFileName, 'public');

        // 2. Create Decision Record (No Eligibility for Applied Foreign Master)
        $decision = ApplicationDecision::create([
            'application_id'           => $request->application_id,
            'decision_no'              => $request->decision_no,
            'decision_date'            => $request->decision_date,
            'file_path'                => $path,
            'eligibility_decision_no'   => null,
            'eligibility_decision_date' => null,
            'eligibility_file_path'    => null,
            'notes'                    => $request->notes ?? 'قرار تعادل ماجستير خارجي تطبيقي صادر رسمياً',
        ]);

        // 3. Automatically update application status
        $app->status = 'تم الصدور';
        $app->save();

        // 4. Send automated notification message to university
        $candidateFullName = $app->candidate ? $app->candidate->full_name : '';
        ApplicationMessage::create([
            'application_id' => $app->id,
            'sender_id' => Auth::id() ?? 1,
            'message' => "📜 [إشعار رسمي - صدور قرار تعادل ماجستير خارجي تطبيقي]: تم صدور قرار تعادل الماجستير الخارجي (المسار التطبيقي - تدريس الجوانب التطبيقية) رسمياً برقم ({$request->decision_no}) للطلب رقم (#{$app->application_no}) للمرشح ({$candidateFullName}). يمكنك الاطلاع على نسخة القرار وتحميلها أصولاً.",
            'is_read' => false,
        ]);

        return redirect()->route('admin.foreign_master_applied_decisions.index')->with('success', 'تم تسجيل وإرسال قرار تعادل الماجستير الخارجي التطبيقي وإشعار الجامعة المعنية بنجاح.');
    }

    // =========================================================================
    // 6. PAGE FOR FOREIGN MASTER THEORETICAL DECISIONS (تعادل الماجستير الخارجي النظري - مع قرار الأهلية)
    // =========================================================================
    public function foreignMasterTheoreticalIndex(Request $request)
    {
        $typeIds = $this->getRequestTypeIds('foreign_master_theoretical');

        // Applications ready for Foreign Master Theoretical decision issuing
        $approvedApps = Application::with(['candidate', 'workUniversity', 'latestDecision', 'educations.level'])
            ->whereIn('status', ['بانتظار إصدار القرار', 'بانتظار صدور القرار', 7])
            ->whereIn('request_type', $typeIds)
            ->latest()
            ->get();

        if ($request->filled('app_id')) {
            $targetApp = Application::with(['candidate', 'workUniversity', 'latestDecision', 'educations.level'])->find($request->query('app_id'));
            if ($targetApp && in_array($targetApp->status, ['بانتظار إصدار القرار', 'بانتظار صدور القرار', 7]) && !$approvedApps->contains('id', $targetApp->id)) {
                $approvedApps->prepend($targetApp);
            }
        }

        $search = $request->query('search');

        $issuedDecisions = ApplicationDecision::with('application.candidate', 'application.workUniversity')
            ->whereHas('application', function ($q) use ($typeIds) {
                $q->whereIn('request_type', $typeIds);
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
                    ->orWhere('decision_no', 'like', '%' . $search . '%')
                    ->orWhere('eligibility_decision_no', 'like', '%' . $search . '%');
                });
            })
            ->latest()
            ->get();

        return view('admin.decisions.foreign_master_theoretical_index', compact('approvedApps', 'issuedDecisions', 'search'));
    }

    public function foreignMasterTheoreticalStore(Request $request)
    {
        $request->validate([
            'application_id'           => 'required|exists:applications,id',
            'decision_no'              => 'required|string',
            'decision_date'            => 'required|date',
            'decision_file'            => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'eligibility_decision_no'   => 'nullable|string',
            'eligibility_decision_date' => 'nullable|date',
            'eligibility_file'         => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
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

        // 1. Process Main Equivalence Decision File
        $ext = $request->file('decision_file')->getClientOriginalExtension();
        $decisionFileName = 'Foreign_Master_Theoretical_Decision_No' . $safeDecNo . '_' . $safeAppNo . ($cleanCandidateName ? '_' . $cleanCandidateName : '') . '.' . $ext;
        $path = $request->file('decision_file')->storeAs('decisions', $decisionFileName, 'public');

        // 2. Process Eligibility Decision File if provided (has eligibility!)
        $eligibilityPath = null;
        if ($request->hasFile('eligibility_file')) {
            $elExt = $request->file('eligibility_file')->getClientOriginalExtension();
            $elFileName = 'Foreign_Master_Theoretical_Eligibility_No' . ($request->eligibility_decision_no ? str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $request->eligibility_decision_no) : 'Draft') . '_' . $safeAppNo . ($cleanCandidateName ? '_' . $cleanCandidateName : '') . '.' . $elExt;
            $eligibilityPath = $request->file('eligibility_file')->storeAs('decisions', $elFileName, 'public');
        }

        // 3. If both files are PDFs, merge them into one comprehensive package
        if ($eligibilityPath && strtolower($ext) === 'pdf' && strtolower($request->file('eligibility_file')->getClientOriginalExtension()) === 'pdf') {
            try {
                $merger = new \Jurosh\PDFMerge\PDFMerger;
                $mainPdfPath = Storage::disk('public')->path($path);
                $eligibilityPdfPath = Storage::disk('public')->path($eligibilityPath);

                $merger->addPDF($eligibilityPdfPath, 'all');
                $merger->addPDF($mainPdfPath, 'all');

                $combinedFileName = 'Foreign_Master_Theoretical_Package_No' . $safeDecNo . '_' . $safeAppNo . ($cleanCandidateName ? '_' . $cleanCandidateName : '') . '.pdf';
                $combinedFullPath = Storage::disk('public')->path('decisions/' . $combinedFileName);
                $merger->merge('file', $combinedFullPath, 'P');

                $path = 'decisions/' . $combinedFileName;
            } catch (\Throwable $e) {}
        }

        // 4. Create Decision Record with eligibility
        $decision = ApplicationDecision::create([
            'application_id'           => $request->application_id,
            'decision_no'              => $request->decision_no,
            'decision_date'            => $request->decision_date,
            'file_path'                => $path,
            'eligibility_decision_no'   => $request->eligibility_decision_no,
            'eligibility_decision_date' => $request->eligibility_decision_date,
            'eligibility_file_path'    => $eligibilityPath,
            'notes'                    => $request->notes ?? 'قرار تعادل ماجستير خارجي نظري صادر رسمياً',
        ]);

        // 5. Automatically update application status
        $app->status = 'تم الصدور';
        $app->save();

        // 6. Send automated notification message to university
        $candidateFullName = $app->candidate ? $app->candidate->full_name : '';
        ApplicationMessage::create([
            'application_id' => $app->id,
            'sender_id' => Auth::id() ?? 1,
            'message' => "📜 [إشعار رسمي - صدور قرار تعادل ماجستير خارجي نظري والأهلية]: تم صدور قرار تعادل الماجستير الخارجي (المسار النظري) وقرار الأهلية رسمياً برقم ({$request->decision_no}) للطلب رقم (#{$app->application_no}) للمرشح ({$candidateFullName}). يمكنك الاطلاع على نسخة القرار وتحميلها أصولاً.",
            'is_read' => false,
        ]);

        return redirect()->route('admin.foreign_master_theoretical_decisions.index')->with('success', 'تم تسجيل وإرسال قرار تعادل الماجستير الخارجي النظري والأهلية وإشعار الجامعة المعنية بنجاح.');
    }

    // Legacy methods
    public function foreignMasterIndex(Request $request)
    {
        return $this->foreignMasterAppliedIndex($request);
    }

    public function foreignMasterStore(Request $request)
    {
        return $this->foreignMasterAppliedStore($request);
    }
}
