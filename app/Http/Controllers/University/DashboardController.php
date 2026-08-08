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
        
        $approvedCount = Application::where('work_university_id', $uniId)
            ->whereIn('status', ['تم الصدور', 'موافقة'])
            ->count();

        $query = Application::where('work_university_id', $uniId)
            ->with(['candidate']);

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
        } catch (\Throwable $e) {
            // fallback if ArPHP shaping fails
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setWarnings(false);

        $fileName = 'Report_' . ($application->application_no ?? $application->id) . '.pdf';
        return $pdf->download($fileName);
    }
}
