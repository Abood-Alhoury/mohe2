<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
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

        Application::whereIn('id', $ids)->update([
            'interview_date' => $date,
            'interview_time' => $time,
            'interview_notes' => $notes,
        ]);

        $formattedDate = format_sys_date($date);
        $count = count($ids);

        return redirect()->back()->with('success', "تم تحديد وتثبيت موعد المقابلة بنجاح لـ {$count} مرشحين في تاريخ {$formattedDate} الساعة {$time}.");
    }

    /**
     * Generate Official Qualification Decision for Interview Candidate.
     */
    public function generateEligibilityDecision(Request $request, $id)
    {
        $application = Application::with([
            'candidate.nationality',
            'workUniversity',
            'educations.level',
            'educations.university',
        ])->findOrFail($id);

        $candidate = $application->candidate;
        $bachelorEd = $application->educations->where('level.name', 'إجازة جامعية')->first();
        $masterEd = $application->educations->where('level.name', 'ماجستير')->first();

        $candidateName = $candidate->full_name ?? 'غ/م';
        $uniName = $application->workUniversity->name ?? 'الجامعة الخاصة المعنية';
        $uniReqNo = $application->new_uni_request_no ?? '---';
        $uniReqDate = $application->new_uni_request_date ? format_sys_date($application->new_uni_request_date) : format_sys_date(now());

        $masterSpec = $masterEd->exact_specialization ?? ($masterEd->general_specialization ?? ($masterEd->section_name ?? 'الماجستير'));
        $masterYear = $masterEd && $masterEd->grant_date ? Carbon::parse($masterEd->grant_date)->format('Y') : date('Y');
        $masterUniRaw = $masterEd->university->name ?? ($masterEd->university_other ?? 'جامعة معترف بها');
        $masterUni = preg_replace('/^(جامعة|جامعه)\s+/u', '', trim($masterUniRaw));

        $baSpec = $bachelorEd->exact_specialization ?? ($bachelorEd->general_specialization ?? ($bachelorEd->section_name ?? 'الإجازة الجامعية'));
        $baYear = $bachelorEd && $bachelorEd->grant_date ? Carbon::parse($bachelorEd->grant_date)->format('Y') : (date('Y') - 5);
        $baUniRaw = $bachelorEd->university->name ?? ($bachelorEd->university_other ?? 'جامعة معترف بها');
        $baUni = preg_replace('/^(جامعة|جامعه)\s+/u', '', trim($baUniRaw));

        $teachingDept = $application->work_department ?: ($application->work_faculty ?: $masterSpec);

        $interviewDate = $application->interview_date ? format_sys_date($application->interview_date) : 'يحدد لاحقاً';
        $interviewTime = $application->interview_time ?: '10:00 صباحاً';

        return view('admin.interviews.eligibility_decision', compact(
            'application',
            'candidate',
            'bachelorEd',
            'masterEd',
            'candidateName',
            'uniName',
            'uniReqNo',
            'uniReqDate',
            'masterSpec',
            'masterYear',
            'masterUni',
            'baSpec',
            'baYear',
            'baUni',
            'teachingDept',
            'interviewDate',
            'interviewTime'
        ));
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
