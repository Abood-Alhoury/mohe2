<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\LookupUniversity;
use App\Models\ApplicationMessage;
use App\Models\ApplicationDecision;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ApplicationsController extends Controller
{
    public function index(Request $request)
    {
        $statusFilter = $request->query('status');
        $universityFilter = $request->query('university_id');
        $searchQuery = $request->query('search');

        $query = Application::where('status', '!=', 'مسودة')->with(['candidate.applications', 'workUniversity', 'user', 'messages', 'decisions']);

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        if ($universityFilter) {
            $query->where('work_university_id', $universityFilter);
        }

        if ($searchQuery) {
            $query->where(function($q) use ($searchQuery) {
                $q->whereHas('candidate', function($cq) use ($searchQuery) {
                    $cq->where('full_name', 'like', '%'.$searchQuery.'%');
                })
                ->orWhere('application_no', 'like', '%'.$searchQuery.'%')
                ->orWhere('work_faculty', 'like', '%'.$searchQuery.'%');
            });
        }

        // Handle marking notification messages as read when clicked from header notification bell
        if ($request->has('open_message')) {
            $openAppId = $request->query('open_message');
            ApplicationMessage::where('application_id', $openAppId)
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }

        $applications = $query->latest()->paginate(15);
        $universities = LookupUniversity::all();

        $statusesList = [
            'تحت التدقيق الأولي',
            'بانتظار الوثائق',
            'لجنة عامة',
            'بانتظار لجنة إنتاج علمي',
            'بانتظار المقابلة',
            'بانتظار إصدار القرار',
        ];

        return view('admin.applications.index', compact(
            'applications',
            'universities',
            'statusesList',
            'statusFilter',
            'universityFilter',
            'searchQuery'
        ));
    }

    // Action 1: Update Application Status & Optionally Attach Decision File
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string',
            'decision_no' => 'nullable|string',
            'decision_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $app = Application::findOrFail($id);
        $forbiddenStatuses = ['بانتظار الوثائق', 'مرفوض', 'معلق'];

        // Requirement 4: If attaching decision, enforce forbidden status check
        if ($request->hasFile('decision_file') || $request->status === 'تم الصدور') {
            if (in_array($app->status, $forbiddenStatuses)) {
                return redirect()->back()->with('error', 'لا يمكن إرفاق قرار تعادل لطلب حالته حالياً (' . $app->status . ').');
            }
        }

        $oldStatus = $app->status;
        $app->status = $request->status;
        $app->save();

        // If uploading Equivalence Decision File, force status to "تم الصدور"
        if ($request->hasFile('decision_file')) {
            $app->status = 'تم الصدور';
            $app->save();

            $file = $request->file('decision_file');
            $safeAppNo = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $app->application_no ?? ('App_' . $app->id));
            $safeDecNo = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $request->decision_no ?? $app->application_no);
            $candidateName = $app->candidate ? preg_replace('/[^\p{L}\p{N}\s\-_]/u', '', $app->candidate->full_name) : '';
            $cleanCandidateName = trim(preg_replace('/\s+/', '_', $candidateName));
            $ext = $file->getClientOriginalExtension();

            $decisionFileName = 'Official_Decision_No' . $safeDecNo . '_' . $safeAppNo . ($cleanCandidateName ? '_' . $cleanCandidateName : '') . '.' . $ext;
            $path = $file->storeAs('decisions', $decisionFileName, 'public');

            $decision = ApplicationDecision::create([
                'application_id' => $app->id,
                'decision_no' => $request->decision_no ?? 'قرار-' . $app->application_no,
                'decision_date' => now(),
                'file_path' => $path,
                'notes' => $request->notes ?? 'تم الصدور والتوقيع من رئيس مجلس التعليم العالي',
            ]);
        }

        // Send automated notification message to university for any status update
        $app->notifyUniversityOfStatusChange($app->status, $request->notes);

        return redirect()->back()->with('success', 'تم تحديث حالة الطلب إلى (' . $app->status . ') بنجاح وإشعار الجامعة.');
    }

    // Action 2: Send Message to University regarding specific application
    public function sendMessage(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $app = Application::findOrFail($id);

        ApplicationMessage::create([
            'application_id' => $app->id,
            'sender_id' => Auth::id() ?? 1,
            'message' => $request->message,
            'is_read' => false,
        ]);

        return redirect()->back()->with('success', 'تم إرسال الرسالة والإشعار بنجاح للجامعة بخصوص الطلب رقم ' . $app->application_no);
    }

    // Action 3: Dedicated Admin Messages Log & Center Page
    public function messagesLog(Request $request)
    {
        $selectedAppId = $request->query('application_id');
        $search = $request->query('search');
        $uniFilter = $request->query('university_id');

        // Query applications that have messages
        $appsQuery = Application::whereHas('messages')
            ->with(['candidate', 'workUniversity', 'messages' => function($q) {
                $q->latest();
            }]);

        if ($uniFilter) {
            $appsQuery->where('work_university_id', $uniFilter);
        }

        if ($search) {
            $appsQuery->where(function($q) use ($search) {
                $q->whereHas('candidate', function($cq) use ($search) {
                    $cq->where('full_name', 'like', "%{$search}%")
                      ->orWhere('national_id', 'like', "%{$search}%");
                })
                ->orWhere('application_no', 'like', "%{$search}%");
            });
        }

        $applicationsList = $appsQuery->get()->sortByDesc(function($app) {
            return optional($app->messages->first())->created_at;
        });

        // Determine selected application
        $selectedApp = null;
        if ($selectedAppId) {
            $selectedApp = Application::with(['candidate', 'workUniversity', 'messages.sender'])->find($selectedAppId);
        }

        if (!$selectedApp && $applicationsList->count() > 0) {
            $selectedApp = Application::with(['candidate', 'workUniversity', 'messages.sender'])->find($applicationsList->first()->id);
        }

        // Mark unread messages as read for selected application
        if ($selectedApp) {
            ApplicationMessage::where('application_id', $selectedApp->id)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            // Reload fresh messages
            $selectedApp->load(['messages' => function($q) {
                $q->orderBy('created_at', 'asc')->with('sender');
            }]);
        }

        $universities = LookupUniversity::all();
        $totalMessagesCount = ApplicationMessage::count();
        $unreadCount = ApplicationMessage::where('sender_id', '!=', Auth::id() ?? 1)->where('is_read', false)->count();

        return view('admin.messages.index', compact(
            'applicationsList',
            'selectedApp',
            'universities',
            'selectedAppId',
            'search',
            'uniFilter',
            'totalMessagesCount',
            'unreadCount'
        ));
    }
}
