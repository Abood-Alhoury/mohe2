<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\ApplicationDecision;
use App\Models\University;
use Carbon\Carbon;

class ArchiveController extends Controller
{
    /**
     * Display the Central Archive and Academic Decisions Registry.
     */
    public function index(Request $request)
    {
        // 1. Fetch all finalized applications (Issued Decisions or Rejected)
        $applications = Application::with([
                'candidate',
                'workUniversity',
                'educations.level',
                'decisions',
                'latestDecision'
            ])
            ->where(function ($query) {
                $query->whereIn('status', ['تم الصدور', 8, '8'])
                      ->orWhereHas('decisions');
            })
            ->latest()
            ->get();

        // 2. Classify and map each application to its archive track
        $archiveItems = $applications->map(function ($app) {
            $isRejected = in_array($app->status, ['مرفوض', 9, '9', 10, '10']);
            $reqType = (string)$app->request_type;
            $reqTypeId = (int)$app->request_type_id;

            $track = 'other';
            if ($isRejected) {
                $track = 'rejected';
            } elseif ($reqTypeId === 3 || $reqTypeId === 7 || str_contains($reqType, 'دكتور') || str_contains($reqType, 'دكتورة')) {
                $track = 'phd';
            } elseif ($reqTypeId === 2 || $reqTypeId === 6 || (str_contains($reqType, 'ماجستير') && str_contains($reqType, 'نظري'))) {
                $track = 'master-theo';
            } elseif ($reqTypeId === 1 || $reqTypeId === 5 || (str_contains($reqType, 'ماجستير') && str_contains($reqType, 'تطبيقي'))) {
                $track = 'master-app';
            } elseif ($reqTypeId === 4 || str_contains($reqType, 'سماح') || str_contains($reqType, 'تدريس')) {
                $track = 'faculty';
            }

            // Determine latest decision
            $decision = $app->latestDecision ?? $app->decisions->first();

            // Determine issuance date and year
            $rawDate = $decision?->decision_date ?? $app->updated_at ?? $app->created_at;
            $dateFormatted = $rawDate ? Carbon::parse($rawDate)->format('d/m/Y') : '—';
            $year = $rawDate ? Carbon::parse($rawDate)->format('Y') : date('Y');

            // Determine university name
            $uniName = $app->workUniversity->name ?? 'غير محدد';

            return [
                'app'           => $app,
                'candidate'     => $app->candidate,
                'decision'      => $decision,
                'track'         => $track,
                'year'          => $year,
                'dateFormatted' => $dateFormatted,
                'uniName'       => $uniName,
                'isRejected'    => $isRejected,
            ];
        });

        // 3. Compute KPI Counts
        $kpi = [
            'all'         => $archiveItems->count(),
            'phd'         => $archiveItems->where('track', 'phd')->count(),
            'master_theo' => $archiveItems->where('track', 'master-theo')->count(),
            'master_app'  => $archiveItems->where('track', 'master-app')->count(),
            'faculty'     => $archiveItems->where('track', 'faculty')->count(),
            'rejected'    => $archiveItems->where('track', 'rejected')->count(),
        ];

        // 4. Unique Universities and Years for Filtering
        $universities = $archiveItems->pluck('uniName')->unique()->filter()->values();
        if ($universities->isEmpty()) {
            $universities = \App\Models\LookupUniversity::pluck('name');
        }

        $years = $archiveItems->pluck('year')->unique()->sortDesc()->values();
        if ($years->isEmpty()) {
            $years = collect([date('Y'), date('Y') - 1]);
        }

        return view('admin.archive.index', compact('archiveItems', 'kpi', 'universities', 'years'));
    }

    public function viewAttachment($id, $type)
    {
        return app(\App\Http\Controllers\Admin\DecisionsController::class)->viewAttachment($id, $type);
    }

    public function downloadBundle($id)
    {
        return app(\App\Http\Controllers\Admin\DecisionsController::class)->downloadBundle($id);
    }

    /**
     * Export Archive Records to Excel / CSV with proper UTF-8 BOM and explicit Excel separator.
     */
    public function exportExcel(Request $request)
    {
        $applications = Application::with([
                'candidate',
                'workUniversity',
                'educations.level',
                'decisions',
                'latestDecision'
            ])
            ->where(function ($query) {
                $query->whereIn('status', ['تم الصدور', 8, '8'])
                      ->orWhereHas('decisions');
            })
            ->latest()
            ->get();

        $filename = 'كشف_القرارات_والأرشيف_المركزي_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($applications) {
            $handle = fopen('php://output', 'w');

            // 1. Output UTF-8 BOM for Microsoft Excel Arabic compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // 2. Write Headers (Without sep=, which breaks Excel BOM detection on Windows)
            fputcsv($handle, [
                'رقم المعاملة',
                'اسم المرشح',
                'الرقم الوطني',
                'المسار الأكاديمي',
                'الجامعة الطالبة',
                'رقم قرار المعادلة',
                'تاريخ قرار المعادلة',
                'رقم قرار الأهلية',
                'تاريخ قرار الأهلية',
                'تاريخ الصدور',
                'الحالة النهائية'
            ], ',', '"', "\\");

            // 3. Write Records
            foreach ($applications as $app) {
                $cand = $app->candidate;
                $dec = $app->latestDecision ?? $app->decisions->first();
                $isRejected = in_array($app->status, ['مرفوض', 9, '9', 10, '10']);

                $rawDate = $dec?->decision_date ?? $app->updated_at ?? $app->created_at;
                $dateFormatted = $rawDate ? Carbon::parse($rawDate)->format('Y-m-d') : '—';

                // Prevent scientific notation in Excel for national ID and application tracking numbers
                $natId = $cand?->national_id ? '="' . $cand->national_id . '"' : '';
                $appNo = '="' . ($app->application_no ?? ('APP-' . $app->id)) . '"';

                $equivDecNo = $dec?->decision_no ? '="' . $dec->decision_no . '"' : '—';
                $equivDecDate = $dec?->decision_date ? Carbon::parse($dec->decision_date)->format('Y-m-d') : '—';

                $eligDecNo = $dec?->eligibility_decision_no ? '="' . $dec->eligibility_decision_no . '"' : '—';
                $eligDecDate = $dec?->eligibility_decision_date ? Carbon::parse($dec->eligibility_decision_date)->format('Y-m-d') : '—';

                fputcsv($handle, [
                    $appNo,
                    $cand?->full_name ?? 'غير محدد',
                    $natId,
                    $app->request_type ?? 'غير محدد',
                    $app->workUniversity?->name ?? 'غير محدد',
                    $equivDecNo,
                    $equivDecDate,
                    $eligDecNo,
                    $eligDecDate,
                    $dateFormatted,
                    $isRejected ? 'مرفوض' : 'معتمد (تم الصدور)'
                ], ',', '"', "\\");
            }

            fclose($handle);
        }, 200, $headers);
    }
}

