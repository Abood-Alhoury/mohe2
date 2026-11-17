<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ApplicationMessage;
use App\Models\LookupUniversity;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InterviewsController extends Controller
{
    /**
     * الاستعلام الأساسي للطلبات المؤهلة لمرحلة المقابلات (نظري ودكتوراه).
     */
    private function awaitingInterviewsQuery()
    {
        return Application::with([
            'candidate.nationality',
            'workUniversity',
            'educations.level',
            'educations.university',
            'messages',
            'decisions',
        ])->where('status', 'بانتظار المقابلة')
          ->where('request_type', 'not like', '%سماح%')
          ->where('request_type', 'not like', '%تدريسية%')
          ->where('request_type', 'not like', '%بحوث%')
          ->where(function ($q) {
              $q->where('request_type', 'not like', '%تطبيقي%')
                ->orWhere('request_type', 'like', '%نظري%');
          });
    }

    /**
     * Display listing of candidate applications awaiting interviews ('بانتظار المقابلة').
     */
    public function index(Request $request)
    {
        $searchQuery = $request->query('search');
        $universityFilter = $request->query('university_id');

        $query = $this->awaitingInterviewsQuery();

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
        $totalAwaitingCount = $this->awaitingInterviewsQuery()->count();
        $scheduledCount = $this->awaitingInterviewsQuery()
            ->whereNotNull('interview_date')
            ->count();

        $passedCount = Application::where('status', 'بانتظار إصدار القرار')->count();
        $failedCount = Application::where('status', 'مرفوض')->count();

        return view('admin.interviews.index', compact(
            'applications',
            'universities',
            'searchQuery',
            'universityFilter',
            'totalAwaitingCount',
            'scheduledCount',
            'passedCount',
            'failedCount'
        ));
    }

    /**
     * صفحة تقرير وجدول المقابلات القابل للطباعة مباشرة بصيغة A4.
     */
    public function report(Request $request)
    {
        $apps = $this->awaitingInterviewsQuery()
            ->orderByRaw('CASE WHEN interview_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('interview_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $candidatesData = $apps->map(function (Application $app) {
            $reqType = (string) ($app->request_type ?? '');
            $isMasterTrack = str_contains($reqType, 'ماجستير') || str_contains($reqType, 'ماستر');

            $phdEd = $app->educations->first(function ($e) {
                return (int) $e->education_level_id === 3
                    && !str_contains((string) ($e->thesis_title ?? ''), 'هيئة تدريسية')
                    && !str_contains((string) ($e->thesis_title ?? ''), 'مركز بحوث');
            });
            $maEd = $app->educations->where('education_level_id', 2)->first();

            if ($isMasterTrack) {
                $targetEd = $maEd ?: $app->educations->last();
                $degreeLabel = 'ماجستير';
                $groupKey = 'masters';
            } elseif (str_contains($reqType, 'دكتور') || $phdEd) {
                $targetEd = $phdEd ?: $app->educations->last();
                $degreeLabel = 'دكتوراه';
                $groupKey = 'doctorate';
            } else {
                $targetEd = $maEd ?: $app->educations->last();
                $degreeLabel = 'ماجستير';
                $groupKey = 'masters';
            }

            $spec = GeneratedDecisionController::getTeachingSpecialization(
                $targetEd,
                optional($targetEd)->exact_specialization ?: (optional($targetEd)->general_specialization ?: (optional($targetEd)->faculty ?: '---'))
            );

            return [
                'id'              => $app->id,
                'app_no'          => $app->application_no ?? ('App-' . $app->id),
                'name'            => $app->candidate->full_name ?? '---',
                'specialization'  => $spec ?: '---',
                'degree'          => $degreeLabel,
                'group'           => $groupKey,
                'university'      => $app->workUniversity->name ?? '---',
                'interview_date'  => $app->interview_date ? Carbon::parse($app->interview_date)->format('Y-m-d') : '',
                'interview_time'  => (string) ($app->interview_time ?? ''),
                'interview_notes' => (string) ($app->interview_notes ?? ''),
            ];
        })->values();

        $rawIds = $request->query('ids');
        $requestedIds = [];
        if (is_string($rawIds) && trim($rawIds) !== '') {
            $requestedIds = array_values(array_filter(array_map('intval', explode(',', $rawIds))));
        } elseif (is_array($rawIds)) {
            $requestedIds = array_values(array_filter(array_map('intval', $rawIds)));
        }

        $availableIds = $candidatesData->pluck('id')->all();
        if (!empty($requestedIds)) {
            $selectedIds = array_values(array_intersect($requestedIds, $availableIds));
        } else {
            $selectedIds = $availableIds;
        }

        $selectedCandidates = $candidatesData->whereIn('id', $selectedIds)->values();
        $firstScheduled = $selectedCandidates->first(fn ($c) => $c['interview_date'] !== '') ?? $selectedCandidates->first();

        $initialDate = trim((string) $request->query('date', '')) ?: ($firstScheduled['interview_date'] ?? date('Y-m-d'));
        $rawTime = trim((string) $request->query('time', '')) ?: ($firstScheduled['interview_time'] ?? '');
        $rawPlace = trim((string) $request->query('place', '')) ?: ($firstScheduled['interview_notes'] ?? '');

        $initialPeriod = 'صباحاً';
        if (str_contains($rawTime, 'مساء')) {
            $initialPeriod = 'مساءً';
        } elseif (str_contains($rawTime, 'ظهر')) {
            $initialPeriod = 'ظهراً';
        }

        $cleanHour = preg_replace('/(الساعة|صباحاً|صباحا|مساءً|مساء|ظهراً|ظهرا)/u', '', $rawTime);
        $cleanHour = trim(preg_replace('/\s+/u', ' ', (string) $cleanHour));
        $initialHour = $cleanHour !== '' ? $cleanHour : '12';

        $initialPlace = $rawPlace !== '' ? $rawPlace : 'وزارة التعليم العالي والبحث العلمي ط ٦';
        $universities = $candidatesData->pluck('university')->filter()->unique()->values();
        $scheduledDates = $candidatesData->pluck('interview_date')->filter()->unique()->sort()->values();

        return view('admin.interviews.report', compact(
            'candidatesData',
            'selectedIds',
            'universities',
            'scheduledDates',
            'initialDate',
            'initialHour',
            'initialPeriod',
            'initialPlace'
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
                'sender_id'      => Auth::id() ?? 1,
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
            'outcome'                 => 'required|in:pass,fail',
            'eligibility_decision_no' => 'nullable|string|max:100',
            'eligibility_decision_date' => 'nullable|date',
            'notes'                   => 'nullable|string',
        ]);

        $app = Application::findOrFail($id);

        if ($request->outcome === 'pass') {
            $app->status = 'بانتظار إصدار القرار';
            $eligDate = $request->input('eligibility_decision_date') ?: ($app->interview_date ?: date('Y-m-d'));
            if (empty($app->interview_date) && $eligDate) {
                $app->interview_date = $eligDate;
            }
            if ($request->filled('eligibility_decision_no')) {
                $app->eligibility_decision_no = trim((string) $request->input('eligibility_decision_no'));
            }
            $app->eligibility_decision_date = $eligDate;
            if ($request->filled('notes')) {
                $app->interview_notes = $request->notes;
            }

            $eligNoNote = $app->eligibility_decision_no ? (' برقم قرار أهلية (' . $app->eligibility_decision_no . ')') : '';
            $message = 'تم اعتماد اجتياز المرشح (' . ($app->candidate->full_name ?? '') . ') للمقابلة' . $eligNoNote . '، وتحويل حالة الطلب إلى (بانتظار إصدار القرار).';
        } else {
            $app->status = 'مرفوض';
            if ($request->filled('notes')) {
                $app->interview_notes = $request->notes;
                $app->rejection_reason = $request->notes;
            }
            $message = 'تم رصد عدم اجتياز المرشح (' . ($app->candidate->full_name ?? '') . ') للمقابلة، وتحويل حالة الطلب إلى (مرفوض).';
        }

        $app->save();

        // Notify university of status change
        $app->notifyUniversityOfStatusChange($app->status, $request->notes);

        if ($request->outcome === 'fail') {
            return redirect()->back()->with('action_banner', [
                'variant'        => 'danger',
                'title'          => 'تم تسجيل عدم اجتياز المقابلة / عدم الموافقة للطلب (#'.($app->application_no ?? $app->id).') ونقله إلى إصدار القرارات',
                'message'        => 'اختفى الطلب من جدول المقابلات وأصبح جاهزاً في (إصدار القرارات ← قرارات عدم الموافقة) لتوليد الكتاب / Word وتوقيعه وختمه ثم رفعه وأرشفته.',
                'generate_url'   => route('admin.reports.generate_decision', $app->id),
                'generate_label' => 'توليد / Word كتاب عدم الموافقة',
                'issue_url'      => route('admin.rejected_decisions.index', ['app_id' => $app->id]),
                'issue_label'    => 'الانتقال لإصدار القرارات (رفع المختوم)',
            ]);
        }

        return redirect()->back()->with('success', $message);
    }
}
