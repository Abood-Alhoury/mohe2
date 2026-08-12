<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ApplicationMessage;
use App\Models\LookupUniversity;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InterviewsController extends Controller
{
    /**
     * Display listing of candidate applications awaiting interviews ('بانتظار المقابلة').
     */
    public function index(Request $request)
    {
        $searchQuery = $request->query('search');
        $universityFilter = $request->query('university_id');

        $query = Application::with([
            'candidate.nationality',
            'workUniversity',
            'educations.level',
            'educations.university',
            'messages',
            'decisions',
        ])->where('status', 'بانتظار المقابلة');

        // Apply Search Filter
        if ($searchQuery) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('application_no', 'like', "%{$searchQuery}%")
                  ->orWhere('new_uni_request_no', 'like', "%{$searchQuery}%")
                  ->orWhereHas('candidate', function ($cq) use ($searchQuery) {
                      $cq->where('full_name', 'like', "%{$searchQuery}%")
                         ->orWhere('national_id', 'like', "%{$searchQuery}%");
                  });
            });
        }

        // Apply University Filter
        if ($universityFilter) {
            $query->where('work_university_id', $universityFilter);
        }

        $applications = $query->latest()->paginate(20);
        $universities = LookupUniversity::all();
        $totalAwaitingCount = Application::where('status', 'بانتظار المقابلة')->count();
        $scheduledCount = Application::where('status', 'بانتظار المقابلة')->whereNotNull('interview_date')->count();

        return view('admin.interviews.index', compact(
            'applications',
            'universities',
            'searchQuery',
            'universityFilter',
            'totalAwaitingCount',
            'scheduledCount'
        ));
    }

    /**
     * Batch Schedule / Update Interview Date and Time for Selected Candidates.
     */
    public function batchSchedule(Request $request)
    {
        $request->validate([
            'application_ids' => 'required|array|min:1',
            'application_ids.*' => 'exists:applications,id',
            'interview_date' => 'required|date',
            'interview_time' => 'required|string',
            'interview_notes' => 'nullable|string',
        ]);

        $ids = $request->input('application_ids');
        $date = $request->input('interview_date');
        $time = $request->input('interview_time');
        $notes = $request->input('interview_notes');

        $apps = Application::with('candidate')->whereIn('id', $ids)->get();

        foreach ($apps as $app) {
            $app->update([
                'interview_date' => $date,
                'interview_time' => $time,
                'interview_notes' => $notes,
            ]);

            $formattedDate = format_sys_date($date);
            $candidateName = $app->candidate->full_name ?? 'المرشح';
            $locationText = $notes ?: 'مبنى وزارة التعليم العالي والبحث العلمي - القاعة الرئيسية للمقابلات';

            // Send Automated Notification Message to University
            ApplicationMessage::create([
                'application_id' => $app->id,
                'sender_type'    => 'admin',
                'message'        => "تنبيه رسمي: تم تحديد موعد المقابلة الشفهية والعملية للمرشح ({$candidateName}) بتاريخ {$formattedDate} - الساعة ({$time}) - المكان/الملاحظات: {$locationText}.",
                'is_read'        => false,
            ]);
        }

        $formattedDate = format_sys_date($date);
        $count = count($ids);

        return redirect()->back()->with('success', "تم تحديد وتثبيت موعد المقابلة بنجاح لـ {$count} مرشحين وإرسال إشعارات رسمية إلى جامعاتهم عبر نظام المحادثات.");
    }



    /**
     * Update candidate interview outcome (Pass -> 'بانتظار إصدار القرار' or Fail -> 'مرفوض').
     */
    public function decideOutcome(Request $request, $id)
    {
        $request->validate([
            'outcome' => 'required|in:pass,fail',
            'notes'   => 'nullable|string',
        ]);

        $app = Application::findOrFail($id);

        if ($request->outcome === 'pass') {
            $app->status = 'بانتظار إصدار القرار';
            $message = 'تم رصد نجاح المرشح (' . ($app->candidate->full_name ?? '') . ') في المقابلة بنجاح، وتحويل حالة الطلب تلقائياً إلى (بانتظار إصدار القرار).';
        } else {
            $app->status = 'مرفوض';
            $message = 'تم رصد عدم اجتياز المرشح (' . ($app->candidate->full_name ?? '') . ') للمقابلة، وتحويل حالة الطلب إلى (مرفوض).';
        }

        if ($request->filled('notes')) {
            $app->interview_notes = $request->notes;
        }

        $app->save();

        return redirect()->back()->with('success', $message);
    }
}
