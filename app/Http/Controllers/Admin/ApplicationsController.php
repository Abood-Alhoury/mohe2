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

        $query = Application::with(['candidate', 'workUniversity', 'user', 'messages', 'decisions']);

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

        $applications = $query->latest()->paginate(15);
        $universities = LookupUniversity::all();

        $statusesList = [
            'تحت التدقيق الأولي',
            'بانتظار الوثائق',
            'معلق',
            'مرفوض',
            'تم الصدور',
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

        // If uploading Equivalence Decision File or status set to "تم الصدور"
        if ($request->hasFile('decision_file')) {
            $file = $request->file('decision_file');
            $path = $file->store('decisions', 'public');

            $decision = ApplicationDecision::create([
                'application_id' => $app->id,
                'decision_no' => $request->decision_no ?? 'قرار-' . $app->application_no,
                'decision_date' => now(),
                'file_path' => $path,
                'notes' => $request->notes ?? 'تم الصدور والتوقيع من رئيس مجلس التعليم العالي',
            ]);
        }

        // Requirement 3: Send automated message to university when decision is issued
        if ($app->status === 'تم الصدور' || $request->hasFile('decision_file')) {
            $candidateName = $app->candidate ? $app->candidate->full_name : '';
            ApplicationMessage::create([
                'application_id' => $app->id,
                'sender_id' => Auth::id() ?? 1,
                'message' => "📜 [إشعار رسمي - صدور قرار التعادل]: تم صدور قرار معادلة الشهادة العلمية رسمياً للطلب رقم (#{$app->application_no}) للمرشح ({$candidateName}). يمكنك الاطلاع على نسخة القرار وتحميلها أصولاً.",
                'is_read' => false,
            ]);
        }

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
}
