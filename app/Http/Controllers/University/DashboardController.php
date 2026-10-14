<?php

namespace App\Http\Controllers\University;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Application;
use App\Models\ApplicationMessage;
use App\Models\LookupUniversity;
use App\Models\SiteSetting;
use App\Models\User;
use setasign\Fpdi\Fpdi;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $uniId = $user->university_id;

        if (!$uniId) {
            Auth::logout();
            return redirect()->route('login')->withErrors(['email' => 'هذا المستخدم ليس مرتبطاً بأي جامعة.']);
        }

        $universityName = $user->university ? $user->university->name : 'الجامعة';

        $totalApps = Application::where('work_university_id', $uniId)->where('status', '!=', 'مسودة')->count();
        $underStudyCount = Application::where('work_university_id', $uniId)->where('status', 'تحت التدقيق الأولي')->count();
        $suspendedCount = Application::where('work_university_id', $uniId)->whereIn('status', ['معلق', 'بانتظار الوثائق', 'مواضيع اللجنة العامة (معلق)'])->count();
        $draftsCount = Application::where('work_university_id', $uniId)->where('status', 'مسودة')->count();
        
        $archivedStatuses = ['تم الصدور', 8, '8', 'موافقة', 'مرفوض', 9, '9', 10, '10'];

        $approvedCount = Application::where('work_university_id', $uniId)
            ->where(function ($q) {
                $q->whereIn('status', ['تم الصدور', 8, '8', 'موافقة'])
                  ->orWhereHas('decisions');
            })
            ->count();

        $draftApplications = Application::where('work_university_id', $uniId)
            ->where('status', 'مسودة')
            ->with(['candidate', 'educations.attachments'])
            ->latest()
            ->get();

        // Operational/Interactive applications only (exclude drafts and finalized/archived applications)
        $query = Application::where('work_university_id', $uniId)
            ->whereNotIn('status', array_merge(['مسودة', 1, '1'], $archivedStatuses))
            ->whereDoesntHave('decisions')
            ->with(['candidate', 'latestDecision', 'educations.level', 'educations.country', 'educations.university', 'educations.attachments.attachmentType']);

        $isSearch = $request->filled('search');
        if ($isSearch) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('application_no', 'like', "%{$search}%")
                  ->orWhere('work_faculty', 'like', "%{$search}%")
                  ->orWhere('work_department', 'like', "%{$search}%")
                  ->orWhereHas('candidate', function($cq) use ($search) {
                      $cq->where('full_name', 'like', "%{$search}%")
                        ->orWhere('national_id', 'like', "%{$search}%");
                  });
            });
        }

        $recentApplications = $isSearch ? $query->latest()->take(50)->get() : $query->latest()->take(15)->get();
        $search = $request->get('search', '');

        if ($request->ajax() || $request->has('ajax')) {
            return view('university.partials._recent_applications_table', compact('recentApplications', 'search'))->render();
        }

        // Get unread notifications for the notifications center
        $notifications = ApplicationMessage::whereHas('application', function($q) use ($uniId) {
                $q->where('work_university_id', $uniId);
            })
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->with(['application.candidate', 'sender'])
            ->latest()
            ->get();

        $siteLocked = SiteSetting::get('site_locked', '0') === '1';
        $siteNotice = SiteSetting::get('site_notice', '');

        return view('university.dashboard', compact(
            'totalApps',
            'underStudyCount',
            'suspendedCount',
            'draftsCount',
            'draftApplications',
            'approvedCount',
            'recentApplications',
            'notifications',
            'universityName',
            'user',
            'siteLocked',
            'siteNotice'
        ));
    }

    public function drafts(Request $request)
    {
        $user = Auth::user();
        $uniId = $user->university_id;

        if (!$uniId) {
            Auth::logout();
            return redirect()->route('login')->withErrors(['email' => 'هذا المستخدم ليس مرتبطاً بأي جامعة.']);
        }

        $universityName = $user->university ? $user->university->name : 'الجامعة';

        $query = Application::where('work_university_id', $uniId)
            ->where('status', 'مسودة')
            ->with(['candidate', 'educations.level', 'educations.country', 'educations.university', 'educations.attachments']);

        $search = trim($request->get('search', ''));
        if ($search !== '') {
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('application_no', 'like', "%{$search}%")
                  ->orWhere('request_type', 'like', "%{$search}%")
                  ->orWhere('work_faculty', 'like', "%{$search}%")
                  ->orWhere('work_department', 'like', "%{$search}%")
                  ->orWhereHas('candidate', function($cq) use ($search) {
                      $cq->where('full_name', 'like', "%{$search}%")
                        ->orWhere('national_id', 'like', "%{$search}%");
                  });
            });
        }

        $drafts = $query->latest('updated_at')->paginate(20)->withQueryString();
        $draftsCount = Application::where('work_university_id', $uniId)->where('status', 'مسودة')->count();

        $notifications = ApplicationMessage::whereHas('application', function($q) use ($uniId) {
                $q->where('work_university_id', $uniId);
            })
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->with(['application.candidate', 'sender'])
            ->latest()
            ->get();

        $siteLocked = SiteSetting::get('site_locked', '0') === '1';
        $siteNotice = SiteSetting::get('site_notice', '');

        if ($request->ajax() || $request->has('ajax')) {
            return view('university.drafts.partials._table', compact(
                'drafts',
                'draftsCount',
                'search',
                'siteLocked'
            ))->render();
        }

        return view('university.drafts.index', compact(
            'drafts',
            'draftsCount',
            'search',
            'notifications',
            'universityName',
            'user',
            'siteLocked',
            'siteNotice'
        ));
    }

    public function messages()
    {
        $user = Auth::user();
        $uniId = $user->university_id;

        // Fetch all conversations/messages for this university's applications
        $messages = ApplicationMessage::whereHas('application', function($q) use ($uniId) {
                $q->where('work_university_id', $uniId);
            })
            ->with(['application.candidate', 'sender'])
            ->latest()
            ->paginate(15);

        // Mark all as read when visiting messages list
        ApplicationMessage::whereHas('application', function($q) use ($uniId) {
                $q->where('work_university_id', $uniId);
            })
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        // Get unread notifications again (should be empty now)
        $notifications = collect();

        return view('university.messages.index', compact('messages', 'notifications'));
    }

    public function replyMessage(Request $request, $appId)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $app = Application::where('id', $appId)
            ->where('work_university_id', Auth::user()->university_id)
            ->firstOrFail();

        if ($app->isArchived()) {
            return redirect()->back()->with('error', 'المعاملة مؤرشفة رسمياً ومغلقة (للاطلاع فقط). لا يمكن إرسال ردود جديدة.');
        }

        ApplicationMessage::create([
            'application_id' => $app->id,
            'sender_id' => Auth::id(),
            'message' => $request->message,
            'is_read' => false,
        ]);

        return redirect()->back()->with('success', 'تم إرسال ردكم إلى مدير التعادل بنجاح.');
    }

    public function nudgeApplication($appId)
    {
        $user = Auth::user();
        $app = Application::where('id', $appId)
            ->where('work_university_id', $user->university_id)
            ->firstOrFail();

        if ($app->isArchived()) {
            return redirect()->back()->with('error', 'المعاملة مؤرشفة رسمياً ومغلقة (للاطلاع فقط).');
        }

        $uniName = $user->university ? $user->university->name : 'الجامعة';
        $candidateName = $app->candidate ? $app->candidate->full_name : 'المرشح';

        ApplicationMessage::create([
            'application_id' => $app->id,
            'sender_id' => $user->id,
            'message' => "📌 [طلب حث واستعجال]: تطلب جامعة ({$uniName}) تسريع واستعجال دراسة معاملة المرشح ({$candidateName}) رقم الطلب (#" . ($app->application_no ?? $app->id) . ").",
            'is_read' => false,
        ]);

        return redirect()->back()->with('success', 'تم إرسال تذكير وحث المعاملة رقم (#' . ($app->application_no ?? $app->id) . ') إلى مدير التعادل بوزارة التعليم العالي بنجاح.');
    }

    public function showMozhakkara($appId)
    {
        $user = Auth::user();
        $query = Application::where('id', $appId);
        $isAdmin = $user && $user->role && $user->role->name === 'admin';
        if (!$isAdmin && $user && $user->university_id) {
            $query->where('work_university_id', $user->university_id);
        }
        $application = $query->with([
                'candidate.nationality',
                'workUniversity',
                'courses',
                'experiences',
                'parentApplication.workUniversity',
                'parentApplication.latestDecision',
                'educations.level',
                'educations.country',
                'educations.university',
                'educations.residences',
                'educations.attachments.attachmentType'
            ])->firstOrFail();

        $candidate = $application->candidate;

        $highSchoolEd = $application->educations->where('level.name', 'ثانوية عامة')->first();
        $bachelorEd   = $application->educations->where('level.name', 'إجازة جامعية')->first();
        $diplomaEd    = $application->educations->where('level.name', 'دبلوم دراسات عليا')->first();
        $masterEd     = $application->educations->where('level.name', 'ماجستير')->first();
        $phdEd        = $application->educations->where('level.name', 'دكتوراه')->first();

        return view('admin.reports.mozhakkara', compact(
            'application',
            'candidate',
            'highSchoolEd',
            'bachelorEd',
            'diplomaEd',
            'masterEd',
            'phdEd'
        ));
    }

    public function downloadPdf($appId)
    {
        $user = Auth::user();
        $query = Application::where('id', $appId);
        $isAdmin = $user && $user->role && $user->role->name === 'admin';
        if (!$isAdmin && $user && $user->university_id) {
            $query->where('work_university_id', $user->university_id);
        }
        $application = $query->with([
                'candidate.nationality',
                'workUniversity',
                'courses',
                'experiences',
                'parentApplication.workUniversity',
                'parentApplication.latestDecision',
                'educations.level',
                'educations.country',
                'educations.university',
                'educations.residences'
            ])->firstOrFail();

        $candidate = $application->candidate;

        $highSchoolEd = $application->educations->where('level.name', 'ثانوية عامة')->first();
        $bachelorEd   = $application->educations->where('level.name', 'إجازة جامعية')->first();
        $diplomaEd    = $application->educations->where('level.name', 'دبلوم دراسات عليا')->first();
        $masterEd     = $application->educations->where('level.name', 'ماجستير')->first();
        $phdEd        = $application->educations->where('level.name', 'دكتوراه')->first();

        $html = view('admin.reports.pdf_template', compact(
            'application',
            'candidate',
            'highSchoolEd',
            'bachelorEd',
            'diplomaEd',
            'masterEd',
            'phdEd'
        ))->render();

        try {
            $arabic = new \ArPHP\I18N\Arabic();
            $styleBlocks = [];
            $html = preg_replace_callback('/<style[^>]*>.*?<\/style>/si', function ($m) use (&$styleBlocks) {
                $placeholder = '___STYLE_BLOCK_' . count($styleBlocks) . '___';
                $styleBlocks[$placeholder] = $m[0];
                return $placeholder;
            }, $html);

            $p = $arabic->arIdentify($html);
            if ($p && count($p) > 0) {
                for ($i = count($p) - 1; $i >= 0; $i -= 2) {
                    $utf8ar = $arabic->utf8Glyphs(substr($html, $p[$i - 1], $p[$i] - $p[$i - 1]), 1000000);
                    $html   = substr_replace($html, $utf8ar, $p[$i - 1], $p[$i] - $p[$i - 1]);
                }
            }

            foreach ($styleBlocks as $placeholder => $block) {
                $html = str_replace($placeholder, $block, $html);
            }
        } catch (\Throwable $e) {}

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setWarnings(false);

        $pdfContent = $pdf->output();

        $safeAppNo = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $application->application_no ?? ('App_' . $application->id));
        $candidateName = $candidate ? preg_replace('/[^\p{L}\p{N}\s\-_]/u', '', $candidate->full_name) : '';
        $cleanCandidateName = trim(preg_replace('/\s+/', '_', $candidateName));

        $fileName = 'Mozhakkara_' . $safeAppNo . ($cleanCandidateName ? '_' . $cleanCandidateName : '') . '.pdf';
        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Length' => strlen($pdfContent),
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    public function downloadConsolidatedPdf($appId)
    {
        $user = Auth::user();
        $query = Application::where('id', $appId);

        $isAdmin = $user && $user->role && $user->role->name === 'admin';
        if (!$isAdmin && $user && $user->university_id) {
            $query->where('work_university_id', $user->university_id);
        }

        $application = $query->with([
            'candidate.nationality',
            'workUniversity',
            'courses',
            'experiences',
            'parentApplication.workUniversity',
            'parentApplication.latestDecision',
            'parentApplication.courses',
            'parentApplication.educations.attachments.attachmentType',
            'educations.level',
            'educations.country',
            'educations.university',
            'educations.residences',
            'educations.attachments.attachmentType'
        ])->firstOrFail();

        $candidate = $application->candidate;

        $highSchoolEd = $application->educations->where('level.name', 'ثانوية عامة')->first();
        $bachelorEd   = $application->educations->where('level.name', 'إجازة جامعية')->first();
        $diplomaEd    = $application->educations->where('level.name', 'دبلوم دراسات عليا')->first();
        $masterEd     = $application->educations->where('level.name', 'ماجستير')->first();
        $phdEd        = $application->educations->where('level.name', 'دكتوراه')->first();

        // 1. Generate Mozhakkara PDF using DomPDF
        $html = view('admin.reports.pdf_template', compact(
            'application', 'candidate', 'highSchoolEd', 'bachelorEd', 'diplomaEd', 'masterEd', 'phdEd'
        ))->render();

        try {
            $arabic = new \ArPHP\I18N\Arabic();
            $styleBlocks = [];
            $html = preg_replace_callback('/<style[^>]*>.*?<\/style>/si', function ($m) use (&$styleBlocks) {
                $placeholder = '___STYLE_BLOCK_' . count($styleBlocks) . '___';
                $styleBlocks[$placeholder] = $m[0];
                return $placeholder;
            }, $html);

            $p = $arabic->arIdentify($html);
            if ($p && count($p) > 0) {
                for ($i = count($p) - 1; $i >= 0; $i -= 2) {
                    $utf8ar = $arabic->utf8Glyphs(substr($html, $p[$i - 1], $p[$i] - $p[$i - 1]), 1000000);
                    $html   = substr_replace($html, $utf8ar, $p[$i - 1], $p[$i] - $p[$i - 1]);
                }
            }

            foreach ($styleBlocks as $placeholder => $block) {
                $html = str_replace($placeholder, $block, $html);
            }
        } catch (\Throwable $e) {}

        $mozhakkaraPdf = \Barryvdh\DomPDF\Facade\Pdf::loadHtml($html)->setPaper('a4', 'portrait');
        $mozhakkaraTmpPath = storage_path('app/tmp_mozhakkara_uni_' . $appId . '.pdf');
        file_put_contents($mozhakkaraTmpPath, $mozhakkaraPdf->output());

        // 2. Collect all uploaded PDF attachment file paths (Latest application attachments first!)
        $attachmentPaths = [];
        foreach ($application->educations as $ed) {
            foreach ($ed->attachments->sortByDesc('id') as $att) {
                $fullPath = storage_path('app/public/' . $att->file_path);
                if (file_exists($fullPath) && strtolower(pathinfo($fullPath, PATHINFO_EXTENSION)) === 'pdf') {
                    if (!in_array($fullPath, $attachmentPaths)) {
                        $attachmentPaths[] = $fullPath;
                    }
                }
            }
        }

        if ($application->parentApplication) {
            foreach ($application->parentApplication->educations as $pEd) {
                foreach ($pEd->attachments->sortByDesc('id') as $att) {
                    $fullPath = storage_path('app/public/' . $att->file_path);
                    if (file_exists($fullPath) && strtolower(pathinfo($fullPath, PATHINFO_EXTENSION)) === 'pdf') {
                        if (!in_array($fullPath, $attachmentPaths)) {
                            $attachmentPaths[] = $fullPath;
                        }
                    }
                }
            }
        }

        // 3. Merge all PDFs using FPDI
        $merger = new \setasign\Fpdi\Fpdi();

        // Add Mozhakkara pages
        $mozhakkaraPageCount = $merger->setSourceFile($mozhakkaraTmpPath);
        for ($p = 1; $p <= $mozhakkaraPageCount; $p++) {
            $tplId = $merger->importPage($p);
            $size = $merger->getTemplateSize($tplId);
            $merger->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $merger->useTemplate($tplId);
        }

        // Add each attachment PDF pages
        foreach ($attachmentPaths as $pdfPath) {
            try {
                $pageCount = $merger->setSourceFile($pdfPath);
                for ($p = 1; $p <= $pageCount; $p++) {
                    $tplId = $merger->importPage($p);
                    $size = $merger->getTemplateSize($tplId);
                    $merger->AddPage($size['orientation'], [$size['width'], $size['height']]);
                    $merger->useTemplate($tplId);
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        // 4. Output merged PDF with clean, descriptive filename
        $safeAppNo = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $application->application_no ?? ('App_' . $application->id));
        $candidateName = $candidate ? preg_replace('/[^\p{L}\p{N}\s\-_]/u', '', $candidate->full_name) : '';
        $cleanCandidateName = trim(preg_replace('/\s+/', '_', $candidateName));

        $fileName = 'Merged_Package_' . $safeAppNo . ($cleanCandidateName ? '_' . $cleanCandidateName : '') . '.pdf';

        $mergedContent = $merger->Output('S');
        @unlink($mozhakkaraTmpPath);

        $disposition = request()->has('download') ? 'attachment' : 'inline';

        return response($mergedContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition . '; filename="' . $fileName . '"; filename*=UTF-8\'\'' . rawurlencode($fileName),
        ]);
    }

    public function editApplication($appId)
    {
        $user = Auth::user();
        $application = Application::where('id', $appId)
            ->where('work_university_id', $user->university_id)
            ->firstOrFail();

        if (str_contains($application->request_type, 'سماح') || str_contains($application->request_type, 'هيئة تدريسية')) {
            return redirect()->route('university.apply.faculty_permission', ['draft_id' => $application->id]);
        }

        $isDoctorate = str_contains($application->request_type, 'دكتوراه') || str_contains($application->request_type, 'دكتورة') || $application->educations()->whereHas('level', function($q) { $q->where('name', 'like', '%دكتور%'); })->exists();
        $isForeign = str_contains($application->request_type, 'خارجي') || str_contains($application->request_type, 'غير سوري');

        if ($isDoctorate && $isForeign) {
            return redirect()->route('university.apply.foreign_doctorate', ['draft_id' => $application->id]);
        }

        if ($isForeign) {
            return redirect()->route('university.apply.foreign_masters', ['draft_id' => $application->id]);
        }

        if ($isDoctorate) {
            return redirect()->route('university.apply.syrian_doctorate', ['draft_id' => $application->id]);
        }

        return redirect()->route('university.apply.syrian_masters', ['draft_id' => $application->id]);
    }

    public function updateApplication(Request $request, $appId)
    {
        $user = Auth::user();
        $app = Application::where('id', $appId)
            ->where('work_university_id', $user->university_id)
            ->firstOrFail();

        // Update candidate details if provided
        if ($request->has('candidate')) {
            $candidate = $app->candidate;
            if ($candidate) {
                $candidate->update(array_filter($request->input('candidate', [])));
            }
        }

        // Update educations if provided
        if ($request->has('educations')) {
            foreach ($request->input('educations', []) as $edId => $edData) {
                $ed = \App\Models\Education::where('id', $edId)->where('application_id', $app->id)->first();
                if ($ed) {
                    $ed->update(array_filter($edData, function($v) { return $v !== null; }));
                }
            }
        }

        // Handle attachment files uploaded by university
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $edId => $files) {
                $ed = \App\Models\Education::where('id', $edId)->where('application_id', $app->id)->first();
                if ($ed) {
                    if (is_array($files)) {
                        foreach ($files as $fileKey => $file) {
                            if ($file && $file->isValid()) {
                                $path = $file->store('attachments/' . $app->id, 'public');
                                \App\Models\EducationAttachment::create([
                                    'education_id' => $ed->id,
                                    'attachment_type_id' => 3,
                                    'file_path' => $path,
                                    'notes' => 'وثيقة مستكملة مرفوعة من صفحة الجامعة',
                                ]);
                            }
                        }
                    } elseif ($files && $files->isValid()) {
                        $path = $files->store('attachments/' . $app->id, 'public');
                        \App\Models\EducationAttachment::create([
                            'education_id' => $ed->id,
                            'attachment_type_id' => 3,
                            'file_path' => $path,
                            'notes' => 'وثيقة مستكملة مرفوعة من صفحة الجامعة',
                        ]);
                    }
                }
            }
        }

        // Update status to تحت التدقيق الأولي
        $app->status = 'تحت التدقيق الأولي';
        $app->save();

        // Automated notification message to admin & university
        $candidateName = $app->candidate ? $app->candidate->full_name : '';
        $uniName = $user->university ? $user->university->name : 'الجامعة';
        $appNo = $app->application_no ?? ('App_' . $app->id);

        // 1. Notification to Admin
        ApplicationMessage::create([
            'application_id' => $app->id,
            'sender_id' => $user->id,
            'message' => "📑 [استكمال وتعديل وثائق]: قامت جامعة ({$uniName}) بالانتهاء من تعديل واستكمال الوثائق والبيانات المطلوبة للطلب رقم (#{$appNo}) للمرشح ({$candidateName}). تم تحويل حالة المعاملة تلقائياً إلى (تحت التدقيق الأولي).",
            'is_read' => false,
        ]);

        // 2. Notification to University
        $systemAdminId = User::whereHas('role', function($q) {
            $q->where('name', 'admin');
        })->where('id', '!=', $user->id)->value('id') ?? 1;

        $app->notifyUniversityOfStatusChange('تحت التدقيق الأولي', 'تم استلام التعديلات والمرفقات المطلوبة بنجاح من الجامعة وتحويل المعاملة للتدقيق الأولي.', $systemAdminId);

        return redirect()->route('university.dashboard')
            ->with('success', 'تم تعديل البيانات وإضافة المرفقات والوثائق المطلوبة للطلب رقم (#' . $appNo . ') بنجاح! تم تحويل حالة المعاملة إلى (تحت التدقيق الأولي) وإشعار وزارة التعليم العالي.');
    }

    public function requiredDocuments()
    {
        $user = Auth::user();
        $uniId = $user->university_id;
        $notifications = ApplicationMessage::whereHas('application', function($q) use ($uniId) {
                $q->where('work_university_id', $uniId);
            })
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->with(['application.candidate', 'sender'])
            ->latest()
            ->get();

        return view('university.required_documents', compact('notifications'));
    }

    public function deleteDraft($appId)
    {
        $user = Auth::user();
        $app = Application::where('id', $appId)
            ->where('work_university_id', $user->university_id)
            ->where('status', 'مسودة')
            ->firstOrFail();

        $app->delete();

        return redirect()->back()->with('success', 'تم حذف مسودة الطلب بنجاح.');
    }

    public function showApplication($appId)
    {
        $user = Auth::user();
        $query = Application::where('id', $appId);

        $isAdmin = $user && $user->role && $user->role->name === 'admin';
        if (!$isAdmin && $user && $user->university_id) {
            $query->where('work_university_id', $user->university_id);
        }

        $application = $query->with([
            'candidate.nationality',
            'workUniversity',
            'courses',
            'experiences',
            'messages.sender',
            'latestDecision',
            'decisions',
            'parentApplication.educations.attachments.attachmentType',
            'educations.level',
            'educations.country',
            'educations.university',
            'educations.residences',
            'educations.attachments.attachmentType'
        ])->firstOrFail();

        $candidate = $application->candidate;

        $highSchoolEd = $application->educations->where('level.name', 'ثانوية عامة')->first();
        $bachelorEd   = $application->educations->where('level.name', 'إجازة جامعية')->first();
        $diplomaEd    = $application->educations->where('level.name', 'دبلوم دراسات عليا')->first();
        $masterEd     = $application->educations->where('level.name', 'ماجستير')->first();
        $phdEd        = $application->educations->where('level.name', 'دكتوراه')->first();

        $notifications = ApplicationMessage::whereHas('application', function($q) use ($user) {
                $q->where('work_university_id', $user->university_id);
            })
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->get();

        return view('university.applications.show', compact(
            'application',
            'candidate',
            'highSchoolEd',
            'bachelorEd',
            'diplomaEd',
            'masterEd',
            'phdEd',
            'notifications'
        ));
    }

    /**
     * View the issued official decisions for an application inline in the browser.
     * Merges both Equivalence and Eligibility decisions into a single PDF if both exist,
     * or opens the single PDF decision directly.
     */
    public function viewDecisionPdf($appId)
    {
        $user = Auth::user();
        $query = Application::with(['candidate', 'decisions', 'latestDecision']);

        $isAdmin = $user && $user->role && $user->role->name === 'admin';
        if (!$isAdmin && $user && $user->university_id) {
            $query->where('work_university_id', $user->university_id);
        }

        $app = $query->findOrFail($appId);

        $decision = $app->latestDecision ?? $app->decisions()->latest()->first();
        if (!$decision) {
            abort(404, 'لم يتم العثور على قرارات رسمية صادرة لهذه المعاملة.');
        }

        $hasEquiv = !empty($decision->file_path) && Storage::disk('public')->exists($decision->file_path);
        $hasElig  = !empty($decision->eligibility_file_path) && Storage::disk('public')->exists($decision->eligibility_file_path);

        if (!$hasEquiv && !$hasElig) {
            abort(404, 'ملفات القرارات الصادرة غير متوفرة في التخزين.');
        }

        $candName = $app->candidate?->full_name ?? 'المرشح';
        $cleanName = trim(preg_replace('/[^\p{L}\p{N}\s\-_]/u', '', $candName));
        $cleanName = str_replace(' ', '_', $cleanName) ?: 'المرشح';

        // 1. Both Equivalence and Eligibility Decisions exist -> Merge into a single PDF
        if ($hasEquiv && $hasElig) {
            $merger = new Fpdi();

            $equivFullPath = Storage::disk('public')->path($decision->file_path);
            $eligFullPath  = Storage::disk('public')->path($decision->eligibility_file_path);

            // Add Equivalence decision pages
            try {
                $extEquiv = strtolower(pathinfo($equivFullPath, PATHINFO_EXTENSION));
                if ($extEquiv === 'pdf') {
                    $pageCount = $merger->setSourceFile($equivFullPath);
                    for ($p = 1; $p <= $pageCount; $p++) {
                        $tplId = $merger->importPage($p);
                        $size = $merger->getTemplateSize($tplId);
                        $merger->AddPage($size['orientation'], [$size['width'], $size['height']]);
                        $merger->useTemplate($tplId);
                    }
                } else {
                    $merger->AddPage('P', 'A4');
                    $merger->Image($equivFullPath, 10, 10, 190);
                }
            } catch (\Throwable $e) {}

            // Add Eligibility decision pages
            try {
                $extElig = strtolower(pathinfo($eligFullPath, PATHINFO_EXTENSION));
                if ($extElig === 'pdf') {
                    $pageCount = $merger->setSourceFile($eligFullPath);
                    for ($p = 1; $p <= $pageCount; $p++) {
                        $tplId = $merger->importPage($p);
                        $size = $merger->getTemplateSize($tplId);
                        $merger->AddPage($size['orientation'], [$size['width'], $size['height']]);
                        $merger->useTemplate($tplId);
                    }
                } else {
                    $merger->AddPage('P', 'A4');
                    $merger->Image($eligFullPath, 10, 10, 190);
                }
            } catch (\Throwable $e) {}

            $mergedPdfContent = $merger->Output('S');
            $fileName = 'حزمة_القرارات_الرسمية_' . $cleanName . '.pdf';

            return response($mergedPdfContent, 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $fileName . '"; filename*=UTF-8\'\'' . rawurlencode($fileName),
                'Cache-Control'       => 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0',
                'Pragma'              => 'public',
            ]);
        }

        // 2. Only ONE Decision exists (e.g. Applied Master or Faculty Permission) -> Serve single PDF directly
        $singleRelPath = $hasEquiv ? $decision->file_path : $decision->eligibility_file_path;
        $singleFullPath = Storage::disk('public')->path($singleRelPath);
        $ext = strtolower(pathinfo($singleFullPath, PATHINFO_EXTENSION));

        $fileName = ($hasEquiv ? 'قرار_التعادل_' : 'قرار_الأهلية_') . $cleanName . '.' . $ext;
        $mime = Storage::disk('public')->mimeType($singleRelPath) ?: 'application/pdf';

        return response()->file($singleFullPath, [
            'Content-Type'        => $mime,
            'Content-Disposition' => 'inline; filename="' . $fileName . '"; filename*=UTF-8\'\'' . rawurlencode($fileName),
            'Cache-Control'       => 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0',
            'Pragma'              => 'public',
        ]);
    }

    /**
     * University Archive: View all finalized and issued decisions & academic dossiers for this university.
     */
    public function archive(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user && $user->role && $user->role->name === 'admin';
        $uniId = $user->university_id;

        if (!$isAdmin && !$uniId) {
            Auth::logout();
            return redirect()->route('login')->withErrors(['email' => 'هذا المستخدم ليس مرتبطاً بأي جامعة.']);
        }

        $universityName = $user->university ? $user->university->name : ($isAdmin ? 'الجامعات (إشراف إدارة)' : 'الجامعة');

        $query = Application::where(function ($q) {
                $q->whereIn('status', ['تم الصدور', 8, '8', 'موافقة', 'مرفوض', 9, '9', 10, '10'])
                  ->orWhereHas('decisions');
            })
            ->with([
                'candidate',
                'decisions',
                'latestDecision',
                'educations.level',
                'educations.country',
                'educations.university',
                'educations.attachments.attachmentType'
            ]);

        if (!$isAdmin) {
            $query->where('work_university_id', $uniId);
        }

        $search = trim($request->get('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('application_no', 'like', "%{$search}%")
                  ->orWhere('work_faculty', 'like', "%{$search}%")
                  ->orWhere('work_department', 'like', "%{$search}%")
                  ->orWhereHas('candidate', function ($cq) use ($search) {
                      $cq->where('full_name', 'like', "%{$search}%")
                        ->orWhere('national_id', 'like', "%{$search}%");
                  })
                  ->orWhereHas('decisions', function ($dq) use ($search) {
                      $dq->where('decision_no', 'like', "%{$search}%");
                  });
            });
        }

        $trackFilter = $request->get('track', 'all');
        if ($trackFilter && $trackFilter !== 'all') {
            if ($trackFilter === 'phd') {
                $query->where(function ($q) {
                    $q->where('request_type', 'like', '%دكتور%')->orWhere('request_type', 'like', '%دكتورة%');
                });
            } elseif ($trackFilter === 'master-theo') {
                $query->where('request_type', 'like', '%ماجستير%')->where('request_type', 'like', '%نظري%');
            } elseif ($trackFilter === 'master-app') {
                $query->where('request_type', 'like', '%ماجستير%')->where('request_type', 'like', '%تطبيقي%');
            } elseif ($trackFilter === 'faculty') {
                $query->where(function ($q) {
                    $q->where('request_type', 'like', '%سماح%')->orWhere('request_type', 'like', '%تدريس%');
                });
            } elseif ($trackFilter === 'rejected') {
                $query->whereIn('status', ['مرفوض', 9, '9', 10, '10']);
            }
        }

        $yearFilter = $request->get('year');
        if ($yearFilter) {
            $query->whereYear('updated_at', $yearFilter);
        }

        $archivedApplications = $query->latest('updated_at')->paginate(15)->withQueryString();

        $baseCountQuery = Application::where(function ($q) {
                $q->whereIn('status', ['تم الصدور', 8, '8', 'موافقة', 'مرفوض', 9, '9', 10, '10'])
                  ->orWhereHas('decisions');
            });

        if (!$isAdmin) {
            $baseCountQuery->where('work_university_id', $uniId);
        }

        $kpi = [
            'total'       => (clone $baseCountQuery)->count(),
            'phd'         => (clone $baseCountQuery)->where(fn($q) => $q->where('request_type', 'like', '%دكتور%')->orWhere('request_type', 'like', '%دكتورة%'))->count(),
            'master_theo' => (clone $baseCountQuery)->where('request_type', 'like', '%ماجستير%')->where('request_type', 'like', '%نظري%')->count(),
            'master_app'  => (clone $baseCountQuery)->where('request_type', 'like', '%ماجستير%')->where('request_type', 'like', '%تطبيقي%')->count(),
            'faculty'     => (clone $baseCountQuery)->where(fn($q) => $q->where('request_type', 'like', '%سماح%')->orWhere('request_type', 'like', '%تدريس%'))->count(),
            'rejected'    => (clone $baseCountQuery)->whereIn('status', ['مرفوض', 9, '9', 10, '10'])->count(),
        ];

        $notificationsQuery = ApplicationMessage::where('sender_id', '!=', $user->id)->where('is_read', false);
        if (!$isAdmin) {
            $notificationsQuery->whereHas('application', fn($q) => $q->where('work_university_id', $uniId));
        }
        $notifications = $notificationsQuery->with(['application.candidate', 'sender'])->latest()->get();

        $draftsCount = $isAdmin ? Application::where('status', 'مسودة')->count() : Application::where('work_university_id', $uniId)->where('status', 'مسودة')->count();
        $approvedCount = $kpi['total'];

        return view('university.archive.index', compact(
            'archivedApplications',
            'kpi',
            'search',
            'trackFilter',
            'yearFilter',
            'universityName',
            'notifications',
            'draftsCount',
            'approvedCount',
            'user'
        ));
    }
}

