<?php

namespace App\Http\Controllers\University;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Application;
use App\Models\ApplicationMessage;
use App\Models\LookupUniversity;

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

        $totalApps = Application::where('work_university_id', $uniId)->count();
        $underStudyCount = Application::where('work_university_id', $uniId)->where('status', 'تحت التدقيق الأولي')->count();
        $suspendedCount = Application::where('work_university_id', $uniId)->whereIn('status', ['معلق', 'بانتظار الوثائق', 'مواضيع اللجنة العامة (معلق)'])->count();
        $draftsCount = Application::where('work_university_id', $uniId)->where('status', 'مسودة')->count();
        
        $approvedCount = Application::where('work_university_id', $uniId)
            ->whereIn('status', ['تم الصدور', 'موافقة'])
            ->count();

        $draftApplications = Application::where('work_university_id', $uniId)
            ->where('status', 'مسودة')
            ->with(['candidate', 'educations.attachments'])
            ->latest()
            ->get();

        $query = Application::where('work_university_id', $uniId)
            ->with(['candidate', 'latestDecision', 'educations.level', 'educations.country', 'educations.university', 'educations.attachments.attachmentType']);

        if ($request->filled('search')) {
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

        $recentApplications = $query->latest()->take(15)->get();

        // Get unread notifications for the notifications center
        $notifications = ApplicationMessage::whereHas('application', function($q) use ($uniId) {
                $q->where('work_university_id', $uniId);
            })
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->with(['application.candidate', 'sender'])
            ->latest()
            ->get();

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
            'user'
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
        $application = Application::where('id', $appId)
            ->where('work_university_id', $user->university_id)
            ->with([
                'candidate.nationality',
                'workUniversity',
                'courses',
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
        $application = Application::where('id', $appId)
            ->where('work_university_id', $user->university_id)
            ->with([
                'candidate.nationality',
                'workUniversity',
                'courses',
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

        $safeAppNo = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $application->application_no ?? ('App_' . $application->id));
        $candidateName = $candidate ? preg_replace('/[^\p{L}\p{N}\s\-_]/u', '', $candidate->full_name) : '';
        $cleanCandidateName = trim(preg_replace('/\s+/', '_', $candidateName));

        $fileName = 'Mozhakkara_' . $safeAppNo . ($cleanCandidateName ? '_' . $cleanCandidateName : '') . '.pdf';
        return $pdf->download($fileName);
    }

    public function downloadConsolidatedPdf($appId)
    {
        $user = Auth::user();
        $application = Application::where('id', $appId)
            ->where('work_university_id', $user->university_id)
            ->with([
                'candidate.nationality',
                'workUniversity',
                'courses',
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

        // 2. Collect all uploaded PDF attachment file paths
        $attachmentPaths = [];
        foreach ($application->educations as $ed) {
            foreach ($ed->attachments as $att) {
                $fullPath = storage_path('app/public/' . $att->file_path);
                if (file_exists($fullPath) && strtolower(pathinfo($fullPath, PATHINFO_EXTENSION)) === 'pdf') {
                    $attachmentPaths[] = $fullPath;
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

        return response($mergedContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"; filename*=UTF-8\'\'' . rawurlencode($fileName),
        ]);
    }

    public function editApplication($appId)
    {
        $user = Auth::user();
        $application = Application::where('id', $appId)
            ->where('work_university_id', $user->university_id)
            ->firstOrFail();

        if ($application->status === 'مسودة') {
            return redirect()->route('university.apply.syrian_masters', ['draft_id' => $application->id]);
        }

        $application->load([
                'candidate',
                'workUniversity',
                'courses',
                'educations.level',
                'educations.country',
                'educations.university',
                'educations.attachments.attachmentType'
            ])->firstOrFail();

        $candidate = $application->candidate;

        $highSchoolEd = $application->educations->first(function($e) {
            return $e->level && str_contains($e->level->name, 'ثانوية');
        });
        $bachelorEd = $application->educations->first(function($e) {
            return $e->level && str_contains($e->level->name, 'إجازة');
        });
        $diplomaEd = $application->educations->first(function($e) {
            return $e->level && str_contains($e->level->name, 'دبلوم');
        });
        $masterEd = $application->educations->first(function($e) {
            return $e->level && str_contains($e->level->name, 'ماجستير');
        });
        $phdEd = $application->educations->first(function($e) {
            return $e->level && str_contains($e->level->name, 'دكتوراه');
        });

        $countries = \App\Models\LookupCountry::all();
        $universities = \App\Models\LookupUniversity::all();

        return view('university.applications.edit', compact(
            'application',
            'candidate',
            'highSchoolEd',
            'bachelorEd',
            'diplomaEd',
            'masterEd',
            'phdEd',
            'countries',
            'universities'
        ));
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

        // Automated notification message to admin
        $candidateName = $app->candidate ? $app->candidate->full_name : '';
        $uniName = $user->university ? $user->university->name : 'الجامعة';

        ApplicationMessage::create([
            'application_id' => $app->id,
            'sender_id' => $user->id,
            'message' => "📑 [استكمال وثائق]: قامت جامعة ({$uniName}) بتحديث بيانات وإضافة المرفقات والوثائق المطلوبة للطلب رقم (#{$app->application_no}) للمرشح ({$candidateName}).",
            'is_read' => false,
        ]);

        return redirect()->route('university.dashboard')
            ->with('success', 'تم تعديل البيانات وإضافة المرفقات والوثائق المطلوبة للطلب رقم (#' . ($app->application_no ?? $app->id) . ') بنجاح وإشعار مدير التعادل.');
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
        $application = Application::where('id', $appId)
            ->where('work_university_id', $user->university_id)
            ->with([
                'candidate.nationality',
                'workUniversity',
                'courses',
                'messages.sender',
                'latestDecision',
                'educations.level',
                'educations.country',
                'educations.university',
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
}

