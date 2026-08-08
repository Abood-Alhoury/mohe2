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
        $app->status = $request->status;
        $app->save();

        // If uploading Equivalence Decision File
        if ($request->hasFile('decision_file')) {
            $file = $request->file('decision_file');
            $path = $file->store('decisions', 'public');

            ApplicationDecision::create([
                'application_id' => $app->id,
                'decision_no' => $request->decision_no ?? 'قرار-'.$app->application_no,
                'decision_date' => now(),
                'file_path' => $path,
                'notes' => $request->notes ?? 'تم الصدور والتوقيع من رئيس مجلس التعليم العالي',
            ]);
        }

        return redirect()->back()->with('success', 'تم تحديث حالة الطلب إلى ('.$app->status.') بنجاح');
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

        return redirect()->back()->with('success', 'تم إرسال الرسالة والإشعار بنجاح للجامعة بخصوص الطلب رقم '.$app->application_no);
    }
}
