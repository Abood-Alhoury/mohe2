<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\ApplicationDecision;
use App\Models\ApplicationMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class GeneratedDecisionController extends Controller
{
    public function show(Request $request, $id)
    {
        $application = Application::with([
            'candidate.nationality',
            'workUniversity',
            'educations.level',
            'educations.country',
            'educations.university',
        ])->findOrFail($id);

        $candidate = $application->candidate;

        $bachelorEd = $application->educations->where('level.name', 'إجازة جامعية')->first();
        $masterEd = $application->educations->where('level.name', 'ماجستير')->first();

        // Determine Decision Type
        $requestType = $application->request_type ?? '';
        $isApplied = str_contains($requestType, 'تطبيقي');
        $decisionType = $isApplied ? 'applied_master' : 'syrian_master';
        $decisionTitle = $isApplied ? 'قرار تعادل ماجستير تطبيقي (أقل من سنتين)' : 'قرار تكليف ماجستير سوري (داخلي نظري)';

        // Prepare Dynamic Data
        $candidateName = $candidate->full_name ?? 'غ/م';
        $uniName = $application->workUniversity->name ?? 'الجامعة الخاصة المعنية';
        $uniReqNo = $application->new_uni_request_no ?? '---';
        $uniReqDate = $application->new_uni_request_date ? format_sys_date($application->new_uni_request_date) : format_sys_date(now());

        // Master info
        $masterSpec = $masterEd->exact_specialization ?? ($masterEd->general_specialization ?? ($masterEd->section_name ?? 'الماجستير'));
        $masterYear = $masterEd && $masterEd->grant_date ? Carbon::parse($masterEd->grant_date)->format('Y') : date('Y');
        $masterUniRaw = $masterEd->university->name ?? ($masterEd->university_other ?? 'جامعة معترف بها');
        $masterUni = preg_replace('/^(جامعة|جامعه)\s+/u', '', trim($masterUniRaw));

        // Bachelor info
        $baSpec = $bachelorEd->exact_specialization ?? ($bachelorEd->general_specialization ?? ($bachelorEd->section_name ?? 'الإجازة الجامعية'));
        $baYear = $bachelorEd && $bachelorEd->grant_date ? Carbon::parse($bachelorEd->grant_date)->format('Y') : (date('Y') - 5);
        $baUniRaw = $bachelorEd->university->name ?? ($bachelorEd->university_other ?? 'جامعة معترف بها');
        $baUni = preg_replace('/^(جامعة|جامعه)\s+/u', '', trim($baUniRaw));

        // Teaching spec
        $teachingDept = $application->work_department ?: ($application->work_faculty ?: $masterSpec);

        $decisionNo = $request->query('decision_no', '');
        $decisionDate = format_sys_date(now());

        return view('admin.reports.generated_decision', compact(
            'application',
            'candidate',
            'bachelorEd',
            'masterEd',
            'decisionType',
            'decisionTitle',
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
            'decisionNo',
            'decisionDate'
        ));
    }

    public function downloadPdf(Request $request, $id)
    {
        $application = Application::with([
            'candidate.nationality',
            'workUniversity',
            'educations.level',
            'educations.country',
            'educations.university',
        ])->findOrFail($id);

        $candidate = $application->candidate;
        $bachelorEd = $application->educations->where('level.name', 'إجازة جامعية')->first();
        $masterEd = $application->educations->where('level.name', 'ماجستير')->first();

        $requestType = $application->request_type ?? '';
        $isApplied = str_contains($requestType, 'تطبيقي');
        $decisionType = $isApplied ? 'applied_master' : 'syrian_master';
        $decisionTitle = $isApplied ? 'قرار تعادل ماجستير تطبيقي (أقل من سنتين)' : 'قرار تكليف ماجستير سوري (داخلي نظري)';

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

        $decisionNo = $request->query('decision_no', '');
        $decisionDate = format_sys_date(now());

        $html = view('admin.reports.generated_decision_pdf_template', compact(
            'application',
            'candidate',
            'bachelorEd',
            'masterEd',
            'decisionType',
            'decisionTitle',
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
            'decisionNo',
            'decisionDate'
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
                    $html = substr_replace($html, $utf8ar, $p[$i - 1], $p[$i] - $p[$i - 1]);
                }
            }

            foreach ($styleBlocks as $placeholder => $block) {
                $html = str_replace($placeholder, $block, $html);
            }
        } catch (\Exception $e) {
            // fallback
        }

        $pdf = Pdf::loadHTML($html)->setPaper('A4', 'portrait');
        $fileName = 'Official_Decision_' . ($isApplied ? 'Applied' : 'Syrian') . '_' . $application->id . '.pdf';
        return $pdf->stream($fileName);
    }

    public function adopt(Request $request, $id)
    {
        $application = Application::with([
            'candidate.nationality',
            'workUniversity',
            'educations.level',
            'educations.country',
            'educations.university',
        ])->findOrFail($id);

        $decisionNo = $request->input('decision_no', rand(100, 999) . '/' . date('Y'));
        $candidate = $application->candidate;
        $bachelorEd = $application->educations->where('level.name', 'إجازة جامعية')->first();
        $masterEd = $application->educations->where('level.name', 'ماجستير')->first();

        $requestType = $application->request_type ?? '';
        $isApplied = str_contains($requestType, 'تطبيقي');
        $decisionType = $isApplied ? 'applied_master' : 'syrian_master';
        $decisionTitle = $isApplied ? 'قرار تعادل ماجستير تطبيقي (أقل من سنتين)' : 'قرار تكليف ماجستير سوري (داخلي نظري)';

        $candidateName = $candidate->full_name ?? 'غ/م';
        $uniName = $application->workUniversity->name ?? 'الجامعة الخاصة المعنية';
        $uniReqNo = $application->new_uni_request_no ?? '---';
        $uniReqDate = $application->new_uni_request_date ? format_sys_date($application->new_uni_request_date) : format_sys_date(now());

        $masterSpec = $masterEd->exact_specialization ?? ($masterEd->general_specialization ?? ($masterEd->section_name ?? 'الماجستير'));
        $masterYear = $masterEd && $masterEd->grant_date ? Carbon::parse($masterEd->grant_date)->format('Y') : date('Y');
        $masterUni = $masterEd->university->name ?? ($masterEd->university_other ?? 'جامعة معترف بها');

        $baSpec = $bachelorEd->exact_specialization ?? ($bachelorEd->general_specialization ?? ($bachelorEd->section_name ?? 'الإجازة الجامعية'));
        $baYear = $bachelorEd && $bachelorEd->grant_date ? Carbon::parse($bachelorEd->grant_date)->format('Y') : (date('Y') - 5);
        $baUni = $bachelorEd->university->name ?? ($bachelorEd->university_other ?? 'جامعة معترف بها');

        $teachingDept = $application->work_department ?: ($application->work_faculty ?: $masterSpec);
        $decisionDate = format_sys_date(now());

        // 1. Render PDF HTML
        $html = view('admin.reports.generated_decision_pdf_template', compact(
            'application',
            'candidate',
            'bachelorEd',
            'masterEd',
            'decisionType',
            'decisionTitle',
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
            'decisionNo',
            'decisionDate'
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
                    $html = substr_replace($html, $utf8ar, $p[$i - 1], $p[$i] - $p[$i - 1]);
                }
            }

            foreach ($styleBlocks as $placeholder => $block) {
                $html = str_replace($placeholder, $block, $html);
            }
        } catch (\Exception $e) {
            // fallback
        }

        // 2. Generate and store PDF file
        $pdf = Pdf::loadHTML($html)->setPaper('A4', 'portrait');
        $safeDecNo = str_replace(['/', '\\'], '_', $decisionNo);
        $fileName = 'Official_Decision_' . $application->id . '_' . $safeDecNo . '_' . time() . '.pdf';
        $filePath = 'decisions/' . $fileName;

        Storage::disk('public')->put($filePath, $pdf->output());

        // 3. Update application status
        $application->status = 'تم الصدور';
        $application->save();

        // 4. Create ApplicationDecision record with file_path
        $decision = ApplicationDecision::create([
            'application_id' => $application->id,
            'decision_no' => $decisionNo,
            'decision_date' => now(),
            'file_path' => $filePath,
            'notes' => 'تم توليد واعتماد القرار رسمياً عبر النظام الإلكتروني للمجلس',
        ]);

        // 5. Send automated notification to university
        ApplicationMessage::create([
            'application_id' => $application->id,
            'sender_id' => Auth::id() ?? 1,
            'message' => "📜 [إشعار رسمي - صدور قرار التعادل]: تم صدور واعتماد قرار معادلة الشهادة العلمية رسمياً برقم ({$decisionNo}) للطلب رقم (#{$application->application_no}) للمرشح ({$candidateName}). يمكنك الاطلاع على نص القرار وتحميله أصولاً.",
            'is_read' => false,
        ]);

        return redirect()->route('admin.applications.index')->with('success', 'تم اعتماد وإصدار قرار التعادل رسمياً بنجاح وتحديث حالة الطلب إلى (تم الصدور) وإشعار الجامعة.');
    }
}
