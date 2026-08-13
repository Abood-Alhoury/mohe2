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

        if (!in_array($application->status, ['بانتظار إصدار القرار', 'بانتظار صدور القرار', 'تم الصدور'])) {
            return redirect()->route('admin.applications.index')->with('error', 'توليد وإصدار القرارات متاح فقط للطلبات التي حالتها (بانتظار إصدار القرار).');
        }

        $docType = $request->query('type', $request->input('doc_type', 'equivalence'));
        if (!in_array($docType, ['equivalence', 'eligibility'])) {
            $docType = 'equivalence';
        }

        $candidate = $application->candidate;
        $bachelorEd = $application->educations->where('level.name', 'إجازة جامعية')->first();
        $masterEd = $application->educations->where('level.name', 'ماجستير')->first();

        $requestType = $application->request_type ?? '';
        $isApplied = str_contains($requestType, 'تطبيقي');
        $decisionType = $isApplied ? 'applied_master' : 'syrian_master';
        $decisionTitle = ($docType === 'eligibility')
            ? 'قرار أهلية للماجستير'
            : ($isApplied ? 'قرار تعادل ماجستير تطبيقي (أقل من سنتين)' : 'قرار تكليف ماجستير سوري (داخلي نظري)');

        // Prepare Dynamic Data & Gender Attributes
        $candidateName = $this->formatCandidateFullName($candidate);
        $genderAttrs = $this->getGenderAttributes($candidate);
        $titlePrefix = $genderAttrs['titlePrefix'];
        $candidateTitle = $genderAttrs['candidateTitle'];
        $candidateTitlePrep = $genderAttrs['candidateTitlePrep'];
        $qualifiedWord = $genderAttrs['qualifiedWord'];
        $qualifierHolderWord = $genderAttrs['qualifierHolderWord'];

        $rawUniName = trim($application->workUniversity->name ?? 'الجامعة الخاصة المعنية');
        $uniName = preg_match('/^(جامعة|الجامعة)\s+/u', $rawUniName) ? $rawUniName : 'جامعة ' . $rawUniName;
        $rawReqNo = $application->new_uni_request_no ?: ($application->parentApplication->new_uni_request_no ?? null);
        $uniReqNo = ($rawReqNo && $rawReqNo !== '---') ? $rawReqNo : '';
        $uniReqDate = $application->new_uni_request_date ? format_sys_date($application->new_uni_request_date) : '';
        $decisionDate = format_sys_date(now());
        $eligibilityDate = $application->interview_date ? format_sys_date($application->interview_date) : $decisionDate;

        // Master info
        $masterGeneral = $masterEd->general_specialization ?? 'الاقتصاد';
        $masterExact = $masterEd->exact_specialization ?? ($masterEd->section_name ?? 'إدارة الأعمال');
        $masterSpec = $masterExact ?: $masterGeneral;
        $masterYear = $masterEd && $masterEd->grant_date ? Carbon::parse($masterEd->grant_date)->format('Y') : date('Y');
        $masterUniRaw = $masterEd->university->name ?? ($masterEd->university_other ?? 'جامعة حلب');
        $masterUni = preg_replace('/^(جامعة|جامعه)\s+/u', '', trim($masterUniRaw));

        // Bachelor info
        $baGeneral = $bachelorEd->general_specialization ?? 'العلوم الإدارية';
        $baSection = $bachelorEd->section_name ?? ($bachelorEd->exact_specialization ?? 'إدارة الأعمال');
        $baSpec = $baSection ?: $baGeneral;
        $baYear = $bachelorEd && $bachelorEd->grant_date ? Carbon::parse($bachelorEd->grant_date)->format('Y') : (date('Y') - 4);
        $baUniRaw = $bachelorEd->university->name ?? ($bachelorEd->university_other ?? 'جامعة إدلب');
        $baUni = preg_replace('/^(جامعة|جامعه)\s+/u', '', trim($baUniRaw));

        // Teaching spec
        $teachingDept = $application->work_department ?: ($application->work_faculty ?: $masterExact);

        $decisionNo = $request->query('decision_no', '');
        $decisionDate = format_sys_date(now());

        return view('admin.reports.generated_decision', compact(
            'application',
            'candidate',
            'bachelorEd',
            'masterEd',
            'docType',
            'decisionType',
            'decisionTitle',
            'candidateName',
            'titlePrefix',
            'candidateTitle',
            'candidateTitlePrep',
            'qualifiedWord',
            'qualifierHolderWord',
            'uniName',
            'uniReqNo',
            'uniReqDate',
            'eligibilityDate',
            'masterGeneral',
            'masterExact',
            'masterSpec',
            'masterYear',
            'masterUni',
            'baGeneral',
            'baSection',
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

        if (!in_array($application->status, ['بانتظار إصدار القرار', 'بانتظار صدور القرار', 'تم الصدور'])) {
            return redirect()->route('admin.applications.index')->with('error', 'توليد وإصدار القرارات متاح فقط للطلبات التي حالتها (بانتظار إصدار القرار).');
        }

        $docType = $request->query('type', $request->input('doc_type', 'equivalence'));
        if (!in_array($docType, ['equivalence', 'eligibility'])) {
            $docType = 'equivalence';
        }

        $candidate = $application->candidate;
        $bachelorEd = $application->educations->where('level.name', 'إجازة جامعية')->first();
        $masterEd = $application->educations->where('level.name', 'ماجستير')->first();

        $requestType = $application->request_type ?? '';
        $isApplied = str_contains($requestType, 'تطبيقي');
        $decisionType = $isApplied ? 'applied_master' : 'syrian_master';
        $decisionTitle = ($docType === 'eligibility')
            ? 'قرار أهلية للماجستير'
            : ($isApplied ? 'قرار تعادل ماجستير تطبيقي (أقل من سنتين)' : 'قرار تكليف ماجستير سوري (داخلي نظري)');

        $candidateName = $this->formatCandidateFullName($candidate);
        $genderAttrs = $this->getGenderAttributes($candidate);
        $titlePrefix = $genderAttrs['titlePrefix'];
        $candidateTitle = $genderAttrs['candidateTitle'];
        $candidateTitlePrep = $genderAttrs['candidateTitlePrep'];
        $qualifiedWord = $genderAttrs['qualifiedWord'];
        $qualifierHolderWord = $genderAttrs['qualifierHolderWord'];

        $rawUniName = trim($application->workUniversity->name ?? 'الجامعة الخاصة المعنية');
        $uniName = preg_match('/^(جامعة|الجامعة)\s+/u', $rawUniName) ? $rawUniName : 'جامعة ' . $rawUniName;
        $uniReqNo = $application->new_uni_request_no ?? '---';
        $uniReqDate = $application->new_uni_request_date ? format_sys_date($application->new_uni_request_date) : format_sys_date(now());
        $decisionDate = format_sys_date(now());
        $eligibilityDate = $application->interview_date ? format_sys_date($application->interview_date) : $decisionDate;

        $masterGeneral = $masterEd->general_specialization ?? 'الاقتصاد';
        $masterExact = $masterEd->exact_specialization ?? ($masterEd->section_name ?? 'إدارة الأعمال');
        $masterSpec = $masterExact ?: $masterGeneral;
        $masterYear = $masterEd && $masterEd->grant_date ? Carbon::parse($masterEd->grant_date)->format('Y') : date('Y');
        $masterUniRaw = $masterEd->university->name ?? ($masterEd->university_other ?? 'جامعة معترف بها');
        $masterUni = preg_replace('/^(جامعة|جامعه)\s+/u', '', trim($masterUniRaw));

        $baGeneral = $bachelorEd->general_specialization ?? 'العلوم الإدارية';
        $baSection = $bachelorEd->section_name ?? ($bachelorEd->exact_specialization ?? 'إدارة أعمال');
        $baSpec = $baSection ?: $baGeneral;
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
            'docType',
            'decisionType',
            'decisionTitle',
            'candidateName',
            'titlePrefix',
            'candidateTitle',
            'candidateTitlePrep',
            'qualifiedWord',
            'qualifierHolderWord',
            'uniName',
            'uniReqNo',
            'uniReqDate',
            'eligibilityDate',
            'masterGeneral',
            'masterExact',
            'masterSpec',
            'masterYear',
            'masterUni',
            'baGeneral',
            'baSection',
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
        $pdfContent = $pdf->output();
        $fileName = 'Official_Decision_' . ($isApplied ? 'Applied' : 'Syrian') . '_' . $application->id . '.pdf';
        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Length' => strlen($pdfContent),
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
        ]);
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

        if (!in_array($application->status, ['بانتظار إصدار القرار', 'بانتظار صدور القرار', 'تم الصدور'])) {
            return redirect()->route('admin.applications.index')->with('error', 'توليد وإصدار القرارات متاح فقط للطلبات التي حالتها (بانتظار إصدار القرار).');
        }

        $docType = $request->query('type', $request->input('doc_type', 'equivalence'));
        if (!in_array($docType, ['equivalence', 'eligibility'])) {
            $docType = 'equivalence';
        }

        $decisionNo = $request->input('decision_no', rand(100, 999) . '/' . date('Y'));
        $candidate = $application->candidate;
        $bachelorEd = $application->educations->where('level.name', 'إجازة جامعية')->first();
        $masterEd = $application->educations->where('level.name', 'ماجستير')->first();

        $requestType = $application->request_type ?? '';
        $isApplied = str_contains($requestType, 'تطبيقي');
        $decisionType = $isApplied ? 'applied_master' : 'syrian_master';
        $decisionTitle = ($docType === 'eligibility')
            ? 'قرار أهلية للماجستير'
            : ($isApplied ? 'قرار تعادل ماجستير تطبيقي (أقل من سنتين)' : 'قرار تكليف ماجستير سوري (داخلي نظري)');

        $candidateName = $this->formatCandidateFullName($candidate);
        $genderAttrs = $this->getGenderAttributes($candidate);
        $titlePrefix = $genderAttrs['titlePrefix'];
        $candidateTitle = $genderAttrs['candidateTitle'];
        $candidateTitlePrep = $genderAttrs['candidateTitlePrep'];
        $qualifiedWord = $genderAttrs['qualifiedWord'];
        $qualifierHolderWord = $genderAttrs['qualifierHolderWord'];

        $rawUniName = trim($application->workUniversity->name ?? 'الجامعة الخاصة المعنية');
        $uniName = preg_match('/^(جامعة|الجامعة)\s+/u', $rawUniName) ? $rawUniName : 'جامعة ' . $rawUniName;
        $uniReqNo = $application->new_uni_request_no ?? '---';
        $uniReqDate = $application->new_uni_request_date ? format_sys_date($application->new_uni_request_date) : format_sys_date(now());
        $decisionDate = format_sys_date(now());
        $eligibilityDate = $application->interview_date ? format_sys_date($application->interview_date) : $decisionDate;

        $masterGeneral = $masterEd->general_specialization ?? 'الاقتصاد';
        $masterExact = $masterEd->exact_specialization ?? ($masterEd->section_name ?? 'إدارة الأعمال');
        $masterSpec = $masterExact ?: $masterGeneral;
        $masterYear = $masterEd && $masterEd->grant_date ? Carbon::parse($masterEd->grant_date)->format('Y') : date('Y');
        $masterUniRaw = $masterEd->university->name ?? ($masterEd->university_other ?? 'جامعة معترف بها');
        $masterUni = preg_replace('/^(جامعة|جامعه)\s+/u', '', trim($masterUniRaw));

        $baGeneral = $bachelorEd->general_specialization ?? 'العلوم الإدارية';
        $baSection = $bachelorEd->section_name ?? ($bachelorEd->exact_specialization ?? 'إدارة الأعمال');
        $baSpec = $baSection ?: $baGeneral;
        $baYear = $bachelorEd && $bachelorEd->grant_date ? Carbon::parse($bachelorEd->grant_date)->format('Y') : (date('Y') - 5);
        $baUniRaw = $bachelorEd->university->name ?? ($bachelorEd->university_other ?? 'جامعة معترف بها');
        $baUni = preg_replace('/^(جامعة|جامعه)\s+/u', '', trim($baUniRaw));

        $teachingDept = $application->work_department ?: ($application->work_faculty ?: $masterSpec);
        $decisionDate = format_sys_date(now());

        // 1. Render PDF HTML
        $html = view('admin.reports.generated_decision_pdf_template', compact(
            'application',
            'candidate',
            'bachelorEd',
            'masterEd',
            'docType',
            'decisionType',
            'decisionTitle',
            'candidateName',
            'titlePrefix',
            'candidateTitle',
            'candidateTitlePrep',
            'qualifiedWord',
            'qualifierHolderWord',
            'uniName',
            'uniReqNo',
            'uniReqDate',
            'eligibilityDate',
            'masterGeneral',
            'masterExact',
            'masterSpec',
            'masterYear',
            'masterUni',
            'baGeneral',
            'baSection',
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
        $pdfContent = $pdf->output();
        $safeDecNo = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $decisionNo);
        $safeAppNo = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $application->application_no ?? ('App_' . $application->id));
        $cleanCandidateName = trim(preg_replace('/\s+/', '_', preg_replace('/[^\p{L}\p{N}\s\-_]/u', '', $candidateName)));
        $fileName = 'Official_Decision_No' . $safeDecNo . '_' . $safeAppNo . ($cleanCandidateName ? '_' . $cleanCandidateName : '') . '.pdf';
        $filePath = 'decisions/' . $fileName;

        Storage::disk('public')->put($filePath, $pdfContent);

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

    private function getGenderAttributes($candidate)
    {
        $gender = $candidate ? ($candidate->gender ?? null) : null;
        if ($gender === 'أنثى') {
            return [
                'titlePrefix' => 'إن السيدة',
                'candidateTitle' => 'السيدة',
                'candidateTitlePrep' => 'للسيدة',
                'qualifiedWord' => 'مؤهلة',
                'qualifierHolderWord' => 'الحائزة',
            ];
        } elseif ($gender === 'ذكر') {
            return [
                'titlePrefix' => 'إن السيد',
                'candidateTitle' => 'السيد',
                'candidateTitlePrep' => 'للسيد',
                'qualifiedWord' => 'مؤهل',
                'qualifierHolderWord' => 'الحائز',
            ];
        } else {
            return [
                'titlePrefix' => 'إن السيد/السيدة',
                'candidateTitle' => 'السيد/السيدة',
                'candidateTitlePrep' => 'للسيد/السيدة',
                'qualifiedWord' => 'مؤهل/ة',
                'qualifierHolderWord' => 'الحائز/ة',
            ];
        }
    }

    private function formatCandidateFullName($candidate)
    {
        if (!$candidate) return 'غ/م';
        $fullName = trim($candidate->full_name ?? '');
        $fatherName = trim($candidate->father_name ?? '');

        if (empty($fatherName) || empty($fullName)) {
            return $fullName ?: 'غ/م';
        }

        if (mb_strpos($fullName, $fatherName) !== false) {
            return $fullName;
        }

        $parts = preg_split('/\s+/u', $fullName);
        if (count($parts) > 1) {
            $firstName = array_shift($parts);
            $lastName = implode(' ', $parts);
            return $firstName . ' ' . $fatherName . ' ' . $lastName;
        }

        return $fullName . ' ' . $fatherName;
    }
}
