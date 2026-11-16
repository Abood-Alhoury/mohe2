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
            case 'applied_master':
            case 'foreign_master_applied':
                // Both Syrian Applied (1) and Foreign Applied (5)
                $ids = ApplicationRequestType::where('name', 'like', '%تطبيقي%')
                    ->pluck('id')
                    ->toArray();
                return !empty($ids) ? $ids : [1, 5];

            case 'theoretical_master':
            case 'syrian_master':
            case 'foreign_master_theoretical':
                // Both Syrian Theoretical (2) and Foreign Theoretical (6)
                $ids = ApplicationRequestType::where(function($q) {
                    $q->where('name', 'like', '%ماجستير%')
                      ->orWhere('name', 'like', '%ماستر%');
                })
                ->where('name', 'not like', '%تطبيقي%')
                ->pluck('id')
                ->toArray();
                return !empty($ids) ? $ids : [2, 6];

            case 'faculty_permission':
                $ids = ApplicationRequestType::where(function($q) {
                    $q->where('name', 'like', '%سماح%')
                      ->orWhere('name', 'like', '%تدريس%');
                })
                ->pluck('id')
                ->toArray();
                return !empty($ids) ? $ids : [4];

            case 'doctorate':
            case 'syrian_doctorate':
            case 'foreign_doctorate':
                // Both Syrian Doctorate (3) and Foreign Doctorate (7)
                $ids = ApplicationRequestType::where(function($q) {
                    $q->where('name', 'like', '%دكتوراه%')
                      ->orWhere('name', 'like', '%دكتورة%');
                })
                ->pluck('id')
                ->toArray();
                return !empty($ids) ? $ids : [3, 7];

            default:
                return [];
        }
    }

    // =========================================================================
    // UNIFIED DECISION ISSUANCE WORKSPACE
    // =========================================================================
    public function renderUnifiedWorkspace(Request $request, string $defaultTrack = 'master-theo')
    {
        $activeTrack = $request->query('track', $defaultTrack);
        $search = $request->query('search');

        // Type IDs
        $mTheoIds = $this->getRequestTypeIds('theoretical_master');
        $mAppIds  = $this->getRequestTypeIds('applied_master');
        $phdIds   = $this->getRequestTypeIds('doctorate');
        $facIds   = $this->getRequestTypeIds('faculty_permission');

        $statusPending = ['بانتظار إصدار القرار', 'بانتظار صدور القرار', '7', 7];
        $statusRejected = ['مرفوض', '9', '10', 9, 10];

        // Query pending applications
        $pendingApps = Application::with(['candidate', 'workUniversity', 'educations.level', 'educations.university', 'latestDecision'])
            ->where(function ($q) use ($statusPending, $statusRejected) {
                $q->whereIn('status', $statusPending)
                  ->orWhere(function ($q2) use ($statusRejected) {
                      $q2->whereIn('status', $statusRejected)->whereDoesntHave('decisions');
                  });
            })
            ->latest()
            ->get();

        // If specific app_id requested, include it
        if ($request->filled('app_id')) {
            $targetApp = Application::with(['candidate', 'workUniversity', 'educations.level', 'educations.university', 'latestDecision'])->find($request->query('app_id'));
            if ($targetApp && !$pendingApps->contains('id', $targetApp->id)) {
                $pendingApps->prepend($targetApp);
            }
        }

        // Map into JavaScript array format and calculate counts
        $applicationsData = [];
        $cntMtheo = 0;
        $cntMapp  = 0;
        $cntPhd   = 0;
        $cntFac   = 0;
        $cntRej   = 0;

        foreach ($pendingApps as $app) {
            $reqType = $app->request_type;
            $status = $app->status;
            $rawType = $app->getRawOriginal('request_type');

            // Determine track
            if (in_array($status, $statusRejected)) {
                $track = 'rejected';
                $cntRej++;
            } elseif (in_array($rawType, $mAppIds) || str_contains($reqType, 'تطبيقي')) {
                $track = 'master-app';
                $cntMapp++;
            } elseif (in_array($rawType, $phdIds) || str_contains($reqType, 'دكتور')) {
                $track = 'phd';
                $cntPhd++;
            } elseif (in_array($rawType, $facIds) || str_contains($reqType, 'سماح') || str_contains($reqType, 'تدريس')) {
                $track = 'faculty';
                $cntFac++;
            } else {
                $track = 'master-theo';
                $cntMtheo++;
            }

            // Auto-switch track if specific app_id is passed and no track is explicitly set
            if ($request->filled('app_id') && $request->query('app_id') == $app->id && !$request->has('track')) {
                $activeTrack = $track;
            }

            // Smart Degree Extraction
            $targetEdu = null;
            if (str_contains($reqType, 'دكتور') || str_contains($reqType, 'سماح') || str_contains($reqType, 'تدريس') || str_contains($reqType, 'بحوث')) {
                $targetEdu = $app->educations->first(function($e) {
                    return $e->education_level_id == 3 && !str_contains($e->thesis_title ?? '', 'هيئة تدريسية') && !str_contains($e->thesis_title ?? '', 'مركز بحوث');
                });
            }
            if (!$targetEdu && str_contains($reqType, 'ماجستير')) {
                $targetEdu = $app->educations->where('education_level_id', 2)->first();
            }
            if (!$targetEdu) {
                $targetEdu = $app->educations->whereIn('education_level_id', [3, 2, 1])->sortByDesc('education_level_id')->first();
            }

            $degree = $reqType;
            if ($targetEdu) {
                $levelName = $targetEdu->level->name ?? $reqType;
                $spec = $targetEdu->exact_specialization ?: ($targetEdu->general_specialization ?: '');
                $uni = $targetEdu->university_name ?: ($targetEdu->university->name ?? ($targetEdu->university_other ?? ''));
                if ($spec && $uni) {
                    $degree = "{$levelName} في {$spec} ({$uni})";
                } elseif ($spec) {
                    $degree = "{$levelName} في {$spec}";
                } elseif ($uni) {
                    $degree = "{$levelName} ({$uni})";
                }
            }

            $applicationsData[] = [
                'id'       => $app->id,
                'appNo'    => $app->application_no ?? ('App_' . $app->id),
                'name'     => $app->candidate->full_name ?? 'المرشح',
                'natId'    => $app->candidate->national_id ?? ($app->candidate->passport_no ?? 'غير متوفر'),
                'track'    => $track,
                'degree'   => $degree,
                'uni'      => $app->workUniversity->name ?? 'غير محدد',
                'status'   => $status,
                'reason'   => $app->rejection_reason ?? '',
                'eligNo'   => $app->latestDecision?->eligibility_decision_no ?: ($app->eligibility_decision_no ?? ''),
                'eligDate' => $app->latestDecision?->eligibility_decision_date
                    ? \Carbon\Carbon::parse($app->latestDecision->eligibility_decision_date)->format('Y-m-d')
                    : ($app->eligibility_decision_date
                        ? \Carbon\Carbon::parse($app->eligibility_decision_date)->format('Y-m-d')
                        : ($app->interview_date ? \Carbon\Carbon::parse($app->interview_date)->format('Y-m-d') : date('Y-m-d'))),
            ];
        }

        $issuedCount = ApplicationDecision::count();

        return view('admin.decisions.index', compact(
            'applicationsData',
            'cntMtheo',
            'cntMapp',
            'cntPhd',
            'cntFac',
            'cntRej',
            'activeTrack',
            'issuedCount',
            'search'
        ));
    }

    // =========================================================================
    // 1. PAGE FOR MASTER EQUIVALENCE DECISIONS (تعادل الماجستير الداخلي والخارجي النظري)
    // =========================================================================
    public function index(Request $request)
    {
        return $this->renderUnifiedWorkspace($request, 'master-theo');
    }

    // =========================================================================
    // 2. PAGE FOR DOCTORATE EQUIVALENCE DECISIONS (تعادل الدكتوراه الداخلية والخارجية)
    // =========================================================================
    public function doctorateIndex(Request $request)
    {
        return $this->renderUnifiedWorkspace($request, 'phd');
    }

    // =========================================================================
    // 3. PAGE FOR APPLIED MASTER EQUIVALENCE DECISIONS (ماجستير تطبيقي)
    // =========================================================================
    public function appliedIndex(Request $request)
    {
        return $this->renderUnifiedWorkspace($request, 'master-app');
    }

    public function appliedStore(Request $request)
    {
        $request->validate([
            'application_id' => 'required|exists:applications,id',
            'decision_no'    => 'required|string',
            'decision_date'  => 'required|date',
            'decision_file'  => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'notes'          => 'nullable|string',
        ]);

        $app = Application::findOrFail($request->application_id);

        if (!in_array($app->status, ['بانتظار إصدار القرار', 'بانتظار صدور القرار', '7', 7])) {
            return redirect()->back()->with('error', 'لا يمكن إرفاق قرار لطلب حالته حالياً (' . $app->status . '). إصدار القرارات متاح فقط للطلبات بحالة (بانتظار إصدار القرار).');
        }

        $safeAppNo = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $app->application_no ?? ('App_' . $app->id));
        $safeDecNo = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $request->decision_no);
        $candidateName = $app->candidate ? preg_replace('/[^\p{L}\p{N}\s\-_]/u', '', $app->candidate->full_name) : '';
        $cleanCandidateName = trim(preg_replace('/\s+/', '_', $candidateName));

        // 1. Process Applied Equivalence Decision File (Single Document - No Eligibility)
        $ext = $request->file('decision_file')->getClientOriginalExtension();
        $decisionFileName = 'Applied_Decision_No' . $safeDecNo . '_' . $safeAppNo . ($cleanCandidateName ? '_' . $cleanCandidateName : '') . '.' . $ext;
        $path = $request->file('decision_file')->storeAs('decisions', $decisionFileName, 'public');

        // 2. Create Decision Record (No Eligibility)
        $decision = ApplicationDecision::create([
            'application_id'            => $request->application_id,
            'eligibility_decision_no'   => null,
            'eligibility_decision_date' => null,
            'eligibility_file_path'     => null,
            'decision_no'               => $request->decision_no,
            'decision_date'             => $request->decision_date,
            'file_path'                 => $path,
            'notes'                     => $request->notes ?? 'قرار معادلة ماجستير تطبيقي صادر رسمياً من مجلس التعليم العالي',
        ]);

        // Automatically update application status
        $app->status = 'تم الصدور';
        $app->save();

        // Send automated notification message to university
        $candidateFullName = $app->candidate ? $app->candidate->full_name : '';
        ApplicationMessage::create([
            'application_id' => $app->id,
            'sender_id'      => Auth::id() ?? 1,
            'message'        => "📜 [إشعار رسمي - صدور قرار تعادل ماجستير تطبيقي]: تم صدور قرار معادلة الماجستير التطبيقي رسمياً برقم ({$request->decision_no}) للطلب رقم (#{$app->application_no}) للمرشح ({$candidateFullName}). يمكنك الاطلاع على نسخة القرار وتحميلها أصولاً.",
            'is_read'        => false,
        ]);

        return redirect()->route('admin.applied_decisions.index')->with('success', 'تم تسجيل وإرسال قرار تعادل الماجستير التطبيقي وإشعار الجامعة المعنية بنجاح.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'application_id' => 'required|exists:applications,id',
        ]);

        $app = Application::findOrFail($request->application_id);
        $requiresElig = $app->requiresEligibility();

        $request->validate([
            'eligibility_decision_no'   => ($requiresElig ? 'required' : 'nullable') . '|string',
            'eligibility_decision_date' => ($requiresElig ? 'required' : 'nullable') . '|date',
            'decision_no'               => 'required|string',
            'decision_date'             => 'required|date',
            'decision_file'             => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'notes'                     => 'nullable|string',
        ]);

        if (!in_array($app->status, ['بانتظار إصدار القرار', 'بانتظار صدور القرار', '7', 7])) {
            return redirect()->back()->with('error', 'لا يمكن إرفاق قرار لطلب حالته حالياً (' . $app->status . '). إصدار القرارات متاح فقط للطلبات بحالة (بانتظار إصدار القرار).');
        }

        $safeAppNo = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $app->application_no ?? ('App_' . $app->id));
        $safeDecNo = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $request->decision_no);
        $candidateName = $app->candidate ? preg_replace('/[^\p{L}\p{N}\s\-_]/u', '', $app->candidate->full_name) : '';
        $cleanCandidateName = trim(preg_replace('/\s+/', '_', $candidateName));

        // 1. Process Unified Decision File (Single PDF/Image - No separate eligibility file)
        $ext = $request->file('decision_file')->getClientOriginalExtension();
        $decisionFileName = 'Official_Decision_No' . $safeDecNo . '_' . $safeAppNo . ($cleanCandidateName ? '_' . $cleanCandidateName : '') . '.' . $ext;
        $path = $request->file('decision_file')->storeAs('decisions', $decisionFileName, 'public');

        // 2. Create Decision Record (keep eligibility no & date, set eligibility_file_path to null)
        $decision = ApplicationDecision::create([
            'application_id'            => $request->application_id,
            'eligibility_decision_no'   => $requiresElig ? $request->eligibility_decision_no : null,
            'eligibility_decision_date' => $requiresElig ? $request->eligibility_decision_date : null,
            'eligibility_file_path'     => null,
            'decision_no'               => $request->decision_no,
            'decision_date'             => $request->decision_date,
            'file_path'                 => $path,
            'notes'                     => $request->notes ?? 'قرار التعادل والأهلية الموحد صادر رسمياً من مجلس التعليم العالي',
        ]);

        // Automatically update application status
        $app->status = 'تم الصدور';
        $app->save();

        // Send automated notification message to university
        $candidateFullName = $app->candidate ? $app->candidate->full_name : '';
        $eligibilityInfoText = ($requiresElig && $request->eligibility_decision_no)
            ? " متضمناً قرار الأهلية رقم ({$request->eligibility_decision_no}) تاريخ ({$request->eligibility_decision_date})"
            : "";

        ApplicationMessage::create([
            'application_id' => $app->id,
            'sender_id' => Auth::id() ?? 1,
            'message' => "📜 [إشعار رسمي - صدور قرار التعادل والأهلية الموحد]: تم صدور القرار الرسمي الموحد للماجستير برقم ({$request->decision_no}) تاريخ ({$request->decision_date}){$eligibilityInfoText} للطلب رقم (#{$app->application_no}) للمرشح ({$candidateFullName}). يمكنك الاطلاع على نسخة القرار وتحميلها أصولاً.",
            'is_read' => false,
        ]);

        return redirect()->route('admin.decisions.index')->with('success', 'تم تسجيل وإرسال قرار التعادل والأهلية الموحد وإشعار الجامعة المعنية بنجاح.');
    }

    public function doctorateStore(Request $request)
    {
        $request->validate([
            'application_id' => 'required|exists:applications,id',
        ]);

        $app = Application::findOrFail($request->application_id);
        $requiresElig = $app->requiresEligibility();

        $request->validate([
            'eligibility_decision_no'   => ($requiresElig ? 'required' : 'nullable') . '|string',
            'eligibility_decision_date' => ($requiresElig ? 'required' : 'nullable') . '|date',
            'decision_no'               => 'required|string',
            'decision_date'             => 'required|date',
            'decision_file'             => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'notes'                     => 'nullable|string',
        ]);

        if (!in_array($app->status, ['بانتظار إصدار القرار', 'بانتظار صدور القرار', '7', 7])) {
            return redirect()->back()->with('error', 'لا يمكن إرفاق قرار لطلب حالته حالياً (' . $app->status . '). إصدار القرارات متاح فقط للطلبات بحالة (بانتظار إصدار القرار).');
        }

        $safeAppNo = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $app->application_no ?? ('App_' . $app->id));
        $safeDecNo = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $request->decision_no);
        $candidateName = $app->candidate ? preg_replace('/[^\p{L}\p{N}\s\-_]/u', '', $app->candidate->full_name) : '';
        $cleanCandidateName = trim(preg_replace('/\s+/', '_', $candidateName));

        // 1. Process Unified Doctorate Decision File (Single PDF/Image)
        $ext = $request->file('decision_file')->getClientOriginalExtension();
        $decisionFileName = 'Doctorate_Official_Decision_No' . $safeDecNo . '_' . $safeAppNo . ($cleanCandidateName ? '_' . $cleanCandidateName : '') . '.' . $ext;
        $path = $request->file('decision_file')->storeAs('decisions', $decisionFileName, 'public');

        // 2. Create Decision Record (keep eligibility no & date, set eligibility_file_path to null)
        $decision = ApplicationDecision::create([
            'application_id'            => $request->application_id,
            'eligibility_decision_no'   => $requiresElig ? $request->eligibility_decision_no : null,
            'eligibility_decision_date' => $requiresElig ? $request->eligibility_decision_date : null,
            'eligibility_file_path'     => null,
            'decision_no'               => $request->decision_no,
            'decision_date'             => $request->decision_date,
            'file_path'                 => $path,
            'notes'                     => $request->notes ?? 'قرار التعادل والأهلية الموحد للدكتوراه صادر رسمياً من مجلس التعليم العالي',
        ]);

        // Automatically update application status
        $app->status = 'تم الصدور';
        $app->save();

        // Send automated notification message to university
        $candidateFullName = $app->candidate ? $app->candidate->full_name : '';
        $eligibilityInfoText = ($requiresElig && $request->eligibility_decision_no)
            ? " متضمناً قرار الأهلية رقم ({$request->eligibility_decision_no}) تاريخ ({$request->eligibility_decision_date})"
            : "";

        ApplicationMessage::create([
            'application_id' => $app->id,
            'sender_id' => Auth::id() ?? 1,
            'message' => "📜 [إشعار رسمي - صدور قرار التعادل والأهلية الموحد]: تم صدور القرار الرسمي الموحد للدكتوراه برقم ({$request->decision_no}) تاريخ ({$request->decision_date}){$eligibilityInfoText} للطلب رقم (#{$app->application_no}) للمرشح ({$candidateFullName}). يمكنك الاطلاع على نسخة القرار وتحميلها أصولاً.",
            'is_read' => false,
        ]);

        return redirect()->route('admin.doctorate_decisions.index')->with('success', 'تم تسجيل وإرسال قرار التعادل والأهلية الموحد للدكتوراه وإشعار الجامعة المعنية بنجاح.');
    }

    // =========================================================================
    // 2. PAGE FOR FACULTY TEACHING PERMISSION DECISIONS (سماح بالتدريس)
    // =========================================================================
    public function facultyIndex(Request $request)
    {
        return $this->renderUnifiedWorkspace($request, 'faculty');
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

        if (!in_array($app->status, ['بانتظار إصدار القرار', 'بانتظار صدور القرار', '7', 7])) {
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
            'application_id' => 'required|exists:applications,id',
        ]);

        $app = Application::findOrFail($request->application_id);
        $requiresElig = $app->requiresEligibility();

        $request->validate([
            'decision_no'               => 'required|string',
            'decision_date'             => 'required|date',
            'decision_file'             => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'eligibility_decision_no'   => ($requiresElig ? 'required' : 'nullable') . '|string',
            'eligibility_decision_date' => ($requiresElig ? 'required' : 'nullable') . '|date',
            'notes'                     => 'nullable|string',
        ]);

        if (!in_array($app->status, ['بانتظار إصدار القرار', 'بانتظار صدور القرار'])) {
            return redirect()->back()->with('error', 'لا يمكن إرفاق قرار لطلب حالته حالياً (' . $app->status . '). إصدار القرارات متاح فقط للطلبات بحالة (بانتظار إصدار القرار).');
        }

        $safeAppNo = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $app->application_no ?? ('App_' . $app->id));
        $safeDecNo = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $request->decision_no);
        $candidateName = $app->candidate ? preg_replace('/[^\p{L}\p{N}\s\-_]/u', '', $app->candidate->full_name) : '';
        $cleanCandidateName = trim(preg_replace('/\s+/', '_', $candidateName));

        // 1. Process Unified Equivalence & Eligibility Decision File (Single Document)
        $ext = $request->file('decision_file')->getClientOriginalExtension();
        $decisionFileName = 'Foreign_Master_Theoretical_Decision_No' . $safeDecNo . '_' . $safeAppNo . ($cleanCandidateName ? '_' . $cleanCandidateName : '') . '.' . $ext;
        $path = $request->file('decision_file')->storeAs('decisions', $decisionFileName, 'public');

        // 2. Create Decision Record (keep eligibility no & date, no separate eligibility file)
        $decision = ApplicationDecision::create([
            'application_id'            => $request->application_id,
            'decision_no'               => $request->decision_no,
            'decision_date'             => $request->decision_date,
            'file_path'                 => $path,
            'eligibility_decision_no'   => $requiresElig ? $request->eligibility_decision_no : null,
            'eligibility_decision_date' => $requiresElig ? $request->eligibility_decision_date : null,
            'eligibility_file_path'     => null,
            'notes'                     => $request->notes ?? 'قرار التعادل والأهلية الموحد للماجستير الخارجي النظري صادر رسمياً',
        ]);

        // 3. Automatically update application status
        $app->status = 'تم الصدور';
        $app->save();

        // 4. Send automated notification message to university
        $candidateFullName = $app->candidate ? $app->candidate->full_name : '';
        $eligibilityInfoText = ($requiresElig && $request->eligibility_decision_no)
            ? " متضمناً قرار الأهلية رقم ({$request->eligibility_decision_no}) تاريخ ({$request->eligibility_decision_date})"
            : "";

        ApplicationMessage::create([
            'application_id' => $app->id,
            'sender_id' => Auth::id() ?? 1,
            'message' => "📜 [إشعار رسمي - صدور قرار التعادل والأهلية الموحد]: تم صدور القرار الرسمي الموحد للماجستير الخارجي (المسار النظري) برقم ({$request->decision_no}) تاريخ ({$request->decision_date}){$eligibilityInfoText} للطلب رقم (#{$app->application_no}) للمرشح ({$candidateFullName}). يمكنك الاطلاع على نسخة القرار وتحميلها أصولاً.",
            'is_read' => false,
        ]);

        return redirect()->route('admin.foreign_master_theoretical_decisions.index')->with('success', 'تم تسجيل وإرسال قرار التعادل والأهلية الموحد للماجستير الخارجي النظري وإشعار الجامعة المعنية بنجاح.');
    }

    // =========================================================================
    // FOREIGN DOCTORATE EQUIVALENCE DECISIONS (تعادل الدكتوراه الخارجية غير السورية)
    // =========================================================================
    public function foreignDoctorateIndex(Request $request)
    {
        $typeIds = $this->getRequestTypeIds('foreign_doctorate');

        // Applications ready for Foreign Doctorate decision issuing
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

        return view('admin.decisions.foreign_doctorate_index', compact('approvedApps', 'issuedDecisions', 'search'));
    }

    public function foreignDoctorateStore(Request $request)
    {
        $request->validate([
            'application_id' => 'required|exists:applications,id',
        ]);

        $app = Application::findOrFail($request->application_id);
        $requiresElig = $app->requiresEligibility();

        $request->validate([
            'eligibility_decision_no'   => ($requiresElig ? 'required' : 'nullable') . '|string',
            'eligibility_decision_date' => ($requiresElig ? 'required' : 'nullable') . '|date',
            'decision_no'               => 'required|string',
            'decision_date'             => 'required|date',
            'decision_file'             => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'notes'                     => 'nullable|string',
        ]);

        if (!in_array($app->status, ['بانتظار إصدار القرار', 'بانتظار صدور القرار'])) {
            return redirect()->back()->with('error', 'لا يمكن إرفاق قرار لطلب حالته حالياً (' . $app->status . '). إصدار القرارات متاح فقط للطلبات بحالة (بانتظار إصدار القرار).');
        }

        $safeAppNo = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $app->application_no ?? ('App_' . $app->id));
        $safeDecNo = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $request->decision_no);
        $candidateName = $app->candidate ? preg_replace('/[^\p{L}\p{N}\s\-_]/u', '', $app->candidate->full_name) : '';
        $cleanCandidateName = trim(preg_replace('/\s+/', '_', $candidateName));

        // 1. Process Unified Foreign Doctorate Decision File (Single Document)
        $ext = $request->file('decision_file')->getClientOriginalExtension();
        $decisionFileName = 'Foreign_Doctorate_Official_Decision_No' . $safeDecNo . '_' . $safeAppNo . ($cleanCandidateName ? '_' . $cleanCandidateName : '') . '.' . $ext;
        $path = $request->file('decision_file')->storeAs('decisions', $decisionFileName, 'public');

        // 2. Create Decision Record (keep eligibility no & date, no separate eligibility file)
        $decision = ApplicationDecision::create([
            'application_id'            => $request->application_id,
            'eligibility_decision_no'   => $requiresElig ? $request->eligibility_decision_no : null,
            'eligibility_decision_date' => $requiresElig ? $request->eligibility_decision_date : null,
            'eligibility_file_path'     => null,
            'decision_no'               => $request->decision_no,
            'decision_date'             => $request->decision_date,
            'file_path'                 => $path,
            'notes'                     => $request->notes ?? 'قرار التعادل والأهلية الموحد للدكتوراه غير السورية (خارجية) صادر رسمياً من مجلس التعليم العالي',
        ]);

        // Automatically update application status
        $app->status = 'تم الصدور';
        $app->save();

        // Send automated notification message to university
        $candidateFullName = $app->candidate ? $app->candidate->full_name : '';
        $eligibilityInfoText = ($requiresElig && $request->eligibility_decision_no)
            ? " متضمناً قرار الأهلية رقم ({$request->eligibility_decision_no}) تاريخ ({$request->eligibility_decision_date})"
            : "";

        ApplicationMessage::create([
            'application_id' => $app->id,
            'sender_id' => Auth::id() ?? 1,
            'message' => "📜 [إشعار رسمي - صدور قرار التعادل والأهلية الموحد]: تم صدور القرار الرسمي الموحد للدكتوراه الخارجية برقم ({$request->decision_no}) تاريخ ({$request->decision_date}){$eligibilityInfoText} للطلب رقم (#{$app->application_no}) للمرشح ({$candidateFullName}). يمكنك الاطلاع على نسخة القرار وتحميلها أصولاً.",
            'is_read' => false,
        ]);

        return redirect()->route('admin.foreign_doctorate_decisions.index')->with('success', 'تم تسجيل وإرسال قرار التعادل والأهلية الموحد للدكتوراه الخارجية وإشعار الجامعة المعنية بنجاح.');
    }

    // =========================================================================
    // 5. PAGE FOR REJECTION DECISIONS (قرارات / كتب عدم الموافقة المسببة)
    // =========================================================================
    public function rejectedIndex(Request $request)
    {
        return $this->renderUnifiedWorkspace($request, 'rejected');
    }

    public function rejectedStore(Request $request)
    {
        $request->validate([
            'application_id' => 'required|exists:applications,id',
            'decision_no'    => 'required|string',
            'decision_date'  => 'required|date',
            'decision_file'  => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'notes'          => 'nullable|string',
        ]);

        $app = Application::findOrFail($request->application_id);

        if (!in_array($app->status, ['مرفوض', 9, 10])) {
            return redirect()->back()->with('error', 'إصدار كتاب عدم الموافقة متاح فقط للطلبات بحالة (مرفوض). حالة الطلب الحالية: ' . $app->status);
        }

        $safeAppNo = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $app->application_no ?? ('App_' . $app->id));
        $safeDecNo = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $request->decision_no);
        $candidateName = $app->candidate ? preg_replace('/[^\p{L}\p{N}\s\-_]/u', '', $app->candidate->full_name) : '';
        $cleanCandidateName = trim(preg_replace('/\s+/', '_', $candidateName));

        // 1. Process Rejection Decision File (Single Document)
        $ext = $request->file('decision_file')->getClientOriginalExtension();
        $decisionFileName = 'Rejection_Book_No' . $safeDecNo . '_' . $safeAppNo . ($cleanCandidateName ? '_' . $cleanCandidateName : '') . '.' . $ext;
        $path = $request->file('decision_file')->storeAs('decisions', $decisionFileName, 'public');

        // 2. Create Decision Record
        $decision = ApplicationDecision::create([
            'application_id'            => $request->application_id,
            'decision_no'               => $request->decision_no,
            'decision_date'             => $request->decision_date,
            'file_path'                 => $path,
            'eligibility_decision_no'   => null,
            'eligibility_decision_date' => null,
            'eligibility_file_path'     => null,
            'notes'                     => $request->notes ?? ($app->rejection_reason ? 'سبب عدم الموافقة: ' . $app->rejection_reason : 'قرار / كتاب عدم موافقة مسبّب صادر رسمياً'),
        ]);

        // Keep status as مرفوض
        $app->status = 'مرفوض';
        $app->save();

        // 3. Send automated notification message to university
        $candidateFullName = $app->candidate ? $app->candidate->full_name : '';
        $rejectionNotes = $request->notes ?? $app->rejection_reason ?? 'عدم استيفاء الشروط والضوابط المعتمدة';
        ApplicationMessage::create([
            'application_id' => $app->id,
            'sender_id'      => Auth::id() ?? 1,
            'message'        => "📜 [إشعار رسمي - صدور كتاب عدم موافقة]: تم صدور كتاب عدم الموافقة المسبّب رسمياً برقم ({$request->decision_no}) وتاريخ ({$request->decision_date}) للطلب رقم (#{$app->application_no}) للمرشح ({$candidateFullName}). السبب: ({$rejectionNotes}). يمكنك الاطلاع على نسخة الكتاب وتحميلها أصولاً.",
            'is_read'        => false,
        ]);

        return redirect()->route('admin.rejected_decisions.index')
            ->with('action_banner', [
                'variant'     => 'success',
                'title'       => 'تم أرشفة وإصدار كتاب عدم الموافقة رقم (' . $request->decision_no . ') وإشعار الجامعة بنجاح',
                'message'     => 'انتقل الطلب الآن من قائمة الانتظار إلى الأرشيف العام للوزارة (قسم عدم الموافقة).',
                'issue_url'   => route('admin.archive.index', ['track' => 'rejected']),
                'issue_label' => 'عرض القرار في الأرشيف العام (عدم الموافقة)',
            ]);
    }

    // =========================================================================
    // Explicit Theoretical Aliases & Legacy Redirections
    // =========================================================================
    public function theoreticalIndex(Request $request)
    {
        return $this->index($request);
    }

    public function theoreticalStore(Request $request)
    {
        return $this->store($request);
    }

    public function foreignMasterIndex(Request $request)
    {
        return redirect()->route('admin.applied_decisions.index');
    }

    public function foreignMasterStore(Request $request)
    {
        return $this->appliedStore($request);
    }

    // =========================================================================
    // Decision Attachment Viewer & Bundle Downloader
    // =========================================================================

    /**
     * View a decision attachment inline in browser (avoiding IDM hijacking).
     */
    public function viewAttachment($id, $type)
    {
        $decision = ApplicationDecision::with('application.candidate')->findOrFail($id);

        $relativePath = null;
        if ($type === 'eligibility') {
            $relativePath = $decision->eligibility_file_path;
        } else {
            // 'equivalence' or rejection book file
            $relativePath = $decision->file_path;
        }

        if (!$relativePath || !Storage::disk('public')->exists($relativePath)) {
            abort(404, 'الملف المطلوب غير موجود أو لم يتم إرفاقه بعد.');
        }

        $fullPath = Storage::disk('public')->path($relativePath);
        $mime = Storage::disk('public')->mimeType($relativePath) ?: 'application/pdf';
        $fileName = basename($fullPath);

        return response()->file($fullPath, [
            'Content-Type'        => $mime,
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
            'Cache-Control'       => 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0',
            'Pragma'              => 'public',
        ]);
    }

    /**
     * Download decision bundle (Zip archive if 2 files exist, or direct PDF if 1 file).
     */
    public function downloadBundle($id)
    {
        $decision = ApplicationDecision::with('application.candidate')->findOrFail($id);

        $hasEquiv = $decision->file_path && Storage::disk('public')->exists($decision->file_path);
        $hasElig  = $decision->eligibility_file_path && Storage::disk('public')->exists($decision->eligibility_file_path);

        $candName = $decision->application?->candidate?->full_name ?? 'المرشح';
        $cleanName = trim(preg_replace('/[^\p{L}\p{N}\s\-_]/u', '', $candName));
        $cleanName = str_replace(' ', '_', $cleanName) ?: 'المرشح';

        // 1. Both Equivalence and Eligibility files exist -> Zip Archive Bundle
        if ($hasEquiv && $hasElig) {
            $tempDir = storage_path('app/temp');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $zipBaseName = 'حزمة_قرارات_' . $cleanName . '.zip';
            $zipPath = $tempDir . '/' . uniqid('bundle_') . '.zip';

            if (class_exists('ZipArchive')) {
                $zip = new \ZipArchive();
                if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                    $equivFullPath = Storage::disk('public')->path($decision->file_path);
                    $equivExt = pathinfo($equivFullPath, PATHINFO_EXTENSION) ?: 'pdf';
                    $equivInZipName = 'قرار_معادلة_' . $cleanName . '.' . $equivExt;
                    $zip->addFile($equivFullPath, $equivInZipName);

                    $eligFullPath = Storage::disk('public')->path($decision->eligibility_file_path);
                    $eligExt = pathinfo($eligFullPath, PATHINFO_EXTENSION) ?: 'pdf';
                    $eligInZipName = 'قرار_أهلية_التدريس_' . $cleanName . '.' . $eligExt;
                    $zip->addFile($eligFullPath, $eligInZipName);

                    $zip->close();

                    return response()->download($zipPath, $zipBaseName, [
                        'Content-Type' => 'application/zip',
                    ])->deleteFileAfterSend(true);
                }
            }
        }

        // 2. Only Equivalence / Rejection decision file exists -> Direct Download
        if ($hasEquiv) {
            $equivFullPath = Storage::disk('public')->path($decision->file_path);
            $ext = pathinfo($equivFullPath, PATHINFO_EXTENSION) ?: 'pdf';
            $downloadName = 'قرار_' . $cleanName . '.' . $ext;
            return response()->download($equivFullPath, $downloadName);
        }

        // 3. Only Eligibility file exists -> Direct Download
        if ($hasElig) {
            $eligFullPath = Storage::disk('public')->path($decision->eligibility_file_path);
            $ext = pathinfo($eligFullPath, PATHINFO_EXTENSION) ?: 'pdf';
            $downloadName = 'قرار_أهلية_' . $cleanName . '.' . $ext;
            return response()->download($eligFullPath, $downloadName);
        }

        abort(404, 'الملفات المطلوبة غير موجودة في التخزين.');
    }

    /**
     * Export Decisions Records to Excel / CSV with proper UTF-8 BOM and explicit Excel delimiter.
     */
    public function exportExcel(Request $request)
    {
        return app(\App\Http\Controllers\Admin\ArchiveController::class)->exportExcel($request);
    }
}

