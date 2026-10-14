<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\ApplicationDecision;
use App\Models\ApplicationMessage;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class GeneratedDecisionController extends Controller
{
    /**
     * 1. تنسيق نص الاختصاص الكامل للشهادة عند ذكرها في بداية المادة 1:
     * القاعدة: "في [الاختصاص العام] اختصاص [الاختصاص الدقيق]"
     */
    public static function formatDegreeSpecialization($edu, $isBachelor = false)
    {
        if (!$edu) return '';

        $general = trim($edu->general_specialization ?? '');
        $exact   = trim($edu->exact_specialization ?? ($edu->specialization ?? ''));
        $faculty = trim($edu->faculty ?? '');
        $dept    = trim($edu->department ?? ($edu->section_name ?? ''));

        // في مرحلة الإجازة (البكالوريوس):
        if ($isBachelor) {
            // تنظيف كلمة "كلية" أو "قسم" الزائدة للحصول على اسم الاختصاص الصافي
            $bachelorSpec = $exact ?: ($general ?: ($dept ?: $faculty));
            $bachelorSpec = preg_replace('/^(كلية|قسم)\s+/u', '', $bachelorSpec);
            return $bachelorSpec ?: '---';
        }

        // في مرحلتي الماجستير والدكتوراه:
        // تنظيف الاختصاص الدقيق إذا كان يكرر كلمة "قسم" بالخطأ
        $cleanGeneral = preg_replace('/^(كلية)\s+/u', '', $general ?: $faculty);
        $cleanExact   = preg_replace('/^(قسم)\s+/u', '', $exact);

        // إذا كان الاختصاص الدقيق موجوداً ومختلفاً عن الاختصاص العام وعن اسم الكلية:
        if (!empty($cleanExact) && $cleanExact !== $cleanGeneral && $cleanExact !== $faculty) {
            // إذا كان النص يبدأ بكلمة "شعبة" أو "اختصاص" لا نكرر كلمة "اختصاص"
            if (str_starts_with($cleanExact, 'شعبة') || str_starts_with($cleanExact, 'اختصاص')) {
                return "{$cleanGeneral} {$cleanExact}";
            }
            return "{$cleanGeneral} اختصاص {$cleanExact}";
        }

        return $cleanGeneral ?: ($cleanExact ?: '---');
    }

    /**
     * 2. استخراج اختصاص التعيين والتدريس النهائي (يوضع في آخر جملة من المادة 1):
     * القاعدة: نأخذ الاختصاص الدقيق للشهادة العليا (ماجستير أو دكتوراه)، وإن لم يوجد نأخذ الاختصاص العام.
     */
    public static function getTeachingSpecialization($edu, $fallback = '')
    {
        if (!$edu) return $fallback;
        $exact   = trim(preg_replace('/^(قسم)\s+/u', '', $edu->exact_specialization ?? ($edu->specialization ?? '')));
        $general = trim(preg_replace('/^(كلية)\s+/u', '', $edu->general_specialization ?? ''));
        if (!empty($exact)) return $exact;
        if (!empty($general)) return $general;
        $dept = trim(preg_replace('/^(قسم)\s+/u', '', $edu->department ?? ($edu->section_name ?? '')));
        if (!empty($dept)) return $dept;
        $faculty = trim(preg_replace('/^(كلية)\s+/u', '', $edu->faculty ?? ''));
        if (!empty($faculty)) return $faculty;
        return $fallback;
    }

    /**
     * 3. تنسيق اسم الجامعة والبلد وسنة المنح:
     * القاعدة: إذا كانت الدولة "سوريا" أو "سورية" أو "الجمهورية العربية السورية"، نكتب "من [اسم الجامعة]" فقط بدون "في سوريا".
     * أما إذا كانت دولة أخرى فنكتب: "من [اسم الجامعة] في [اسم الدولة]".
     */
    public static function formatUniAndCountry($edu)
    {
        if (!$edu) return '';
        $uniName = ($edu->university_name_manual ?? null) ?: ($edu->university?->name ?? ($edu->university_other ?? ''));
        $uniName = trim($uniName);
        if (!empty($uniName) && !preg_match('/^(جامعة|جامعه|الجامعة|المعهد|معهد|أكاديمية|اكاديمية|كلية|الكلية)\s+/u', $uniName)) {
            $uniName = 'جامعة ' . $uniName;
        }
        $countryName = trim($edu->country?->name ?? ($edu->country_other ?? ''));

        if (!empty($countryName) && !in_array($countryName, ['سوريا', 'سورية', 'الجمهورية العربية السورية'])) {
            return "من {$uniName} في {$countryName}";
        }
        return "من {$uniName}";
    }

    public static function getGrantYear($edu)
    {
        if (!$edu) return '';
        if (!empty($edu->grant_date)) {
            return Carbon::parse($edu->grant_date)->format('Y');
        }
        if (!empty($edu->graduation_date)) {
            return Carbon::parse($edu->graduation_date)->format('Y');
        }
        return $edu->graduation_year ?? '';
    }

    /**
     * قاموس التذكير والتأنيث الآلي (Gender Dictionary)
     */
    public static function getGenderDictionary($application, $candidate = null)
    {
        $candidate = $candidate ?? $application->profile ?? $application->candidate;
        $genderRaw = trim($application->profile?->gender ?? ($candidate?->gender ?? 'ذكر'));
        $isFemale  = in_array($genderRaw, ['أنثى', 'انثى', 'female', 'Female', 'F', 'f']);

        return [
            'is_female'            => $isFemale,
            'for_doctor'           => $isFemale ? 'للدكتورة' : 'للدكتور',
            'doctor'               => $isFemale ? 'الدكتورة' : 'الدكتور',
            'title'                => $isFemale ? 'السيدة' : 'السيد',
            'title_prep'           => $isFemale ? 'للسيدة' : 'للسيد',
            'holder'               => $isFemale ? 'الحائزة' : 'الحائز',
            'holder_on'            => $isFemale ? 'الحائزة عليها' : 'الحائز عليها',
            'dedication'           => $isFemale ? 'تفرغها' : 'تفرغه',
            'quota'                => $isFemale ? 'نصابها' : 'نصابه',
            'his_her_spec'         => $isFemale ? 'اختصاصها' : 'اختصاصه',
            'qualifies'            => $isFemale ? 'تؤهلها' : 'تؤهله',
            'promoted'             => $isFemale ? 'ترقى' : 'يرقى',
            'appointed_researcher' => $isFemale ? 'والمعينة باحثة' : 'والمعين باحث',
            'assign'               => $isFemale ? 'تكليفها' : 'تكليفه',
        ];
    }

    /**
     * إعداد كافة بيانات القرار والقوالب والنصوص بطريقة موحدة
     */
    public function prepareDecisionData(Application $application, Request $request)
    {
        $requestType = $application->request_type ?? '';
        $candidate   = $application->candidate ?? $application->profile;

        $bachelorEd = $application->educations->where('level.name', 'إجازة جامعية')->first() ?? $application->educations->where('education_level_id', 1)->first();
        $masterEd   = $application->educations->where('level.name', 'ماجستير')->first() ?? $application->educations->where('education_level_id', 2)->first();
        $phdEd      = $application->educations->where('level.name', 'دكتوراه')->first() ?? $application->educations->where('education_level_id', 3)->first();

        $isForeignDoctorate = str_contains($requestType, 'دكتورة خارجية') || 
                              str_contains($requestType, 'دكتوراه خارجية') || 
                              ((str_contains($requestType, 'دكتوراه') || str_contains($requestType, 'دكتورة')) && (str_contains($requestType, 'خارجي') || str_contains($requestType, 'غير سوري'))) ||
                              (isset($application->application_request_type_id) && $application->application_request_type_id == 7);

        $isFacultyPermission = str_contains($requestType, 'سماح') || str_contains($requestType, 'هيئة تدريسية');
        $isResearchCenter    = str_contains($requestType, 'بحوث') || str_contains($requestType, 'باحث');
        $isDoctorate         = !$isFacultyPermission && (str_contains($requestType, 'دكتوراه') || str_contains($requestType, 'دكتورة') || ($phdEd !== null) || $isForeignDoctorate);
        $isForeignMaster     = !$isForeignDoctorate && (str_contains($requestType, 'خارجي') || str_contains($requestType, 'غير سوري'));
        $isForeignApplied    = $isForeignMaster && (str_contains($requestType, 'تطبيقي') || ($masterEd && $masterEd->experience_from_year === null && !str_contains($requestType, 'نظري')));
        $isForeignTheoretical= $isForeignMaster && !$isForeignApplied;
        $isApplied           = str_contains($requestType, 'تطبيقي') || $isForeignApplied;

        $docType = $request->query('type', $request->input('doc_type', 'equivalence'));
        if ($isFacultyPermission || $isApplied || $isResearchCenter) {
            $docType = 'equivalence';
        } elseif (!in_array($docType, ['equivalence', 'eligibility'])) {
            $docType = 'equivalence';
        }

        if ($isFacultyPermission) {
            $decisionType = 'faculty_permission';
            $decisionTitle = 'قرار السماح بالتدريس (أعضاء الهيئة التدريسية)';
        } elseif ($isResearchCenter) {
            $decisionType = 'research_scientist';
            $decisionTitle = 'قرار معادلة شهادة دكتوراه واعتماد للتدريس (باحث في مراكز البحوث)';
        } elseif ($isForeignDoctorate) {
            $decisionType = 'foreign_doctorate';
            $decisionTitle = ($docType === 'eligibility')
                ? 'قرار أهلية للدكتوراه'
                : 'قرار تعادل دكتوراه غير سورية (خارجية)';
        } elseif ($isDoctorate) {
            $decisionType = 'syrian_doctorate';
            $decisionTitle = ($docType === 'eligibility')
                ? 'قرار أهلية للدكتوراه'
                : 'قرار تكليف دكتوراه سورية (داخلي)';
        } elseif ($isForeignApplied) {
            $decisionType = 'foreign_master_applied';
            $decisionTitle = 'قرار ماجستير خارجي (تدريس الجوانب التطبيقية)';
        } elseif ($isForeignTheoretical) {
            $decisionType = 'foreign_master_theoretical';
            $decisionTitle = ($docType === 'eligibility')
                ? 'قرار أهلية للماجستير الخارجي'
                : 'قرار ماجستير خارجي (تكليف بتدريس المقررات النظرية)';
        } elseif ($isApplied) {
            $decisionType = 'applied_master';
            $decisionTitle = ($docType === 'eligibility')
                ? 'قرار أهلية للماجستير'
                : 'قرار تعادل ماجستير تطبيقي (أقل من سنتين)';
        } else {
            $decisionType = 'syrian_master';
            $decisionTitle = ($docType === 'eligibility')
                ? 'قرار أهلية للماجستير'
                : 'قرار تكليف ماجستير سوري (داخلي نظري)';
        }

        // قاموس التذكير والتأنيث الذكي
        $g = self::getGenderDictionary($application, $candidate);
        $isFemale = $g['is_female'];

        // اسم المرشح مصاغاً بدقة
        $candidateName = $this->formatCandidateFullName($candidate);
        if (empty($candidateName) || $candidateName === 'غ/م') {
            $candidateName = trim($application->profile?->full_name ?? ($candidate?->full_name ?? ''));
        }

        // الألقاب والضمائر للتوافقية الكاملة
        $genderAttrs = $this->getGenderAttributes($candidate, ($isDoctorate || $isFacultyPermission || $isResearchCenter));
        $titlePrefix = $genderAttrs['titlePrefix'];
        $candidateTitle = $genderAttrs['candidateTitle'];
        $candidateTitlePrep = $isDoctorate ? $g['for_doctor'] : $g['title_prep'];
        $candidateTitleWord = $g['title'];
        $qualifiedWord = $isFemale ? 'مؤهلة' : 'مؤهل';
        $qualifierHolderWord = $g['holder'];
        $fullTimeWord = $g['dedication'];
        $quotaWord = $g['quota'];
        $appointedResearcherWord = $g['appointed_researcher'];
        $assignWord = $g['assign'];
        $genderTitle = $g['title'];
        $genderDegreeHolder = $g['holder'];
        $genderCommitment = $g['dedication'];
        $genderQuota = $g['quota'];

        // بيانات الجامعة والكتب الرسمية
        $rawUniName = trim($application->workUniversity->name ?? 'الجامعة الخاصة المعنية');
        $uniName = preg_match('/^(جامعة|الجامعة)\s+/u', $rawUniName) ? $rawUniName : 'جامعة ' . $rawUniName;
        $rawReqNo = $application->new_uni_request_no ?: ($application->parentApplication->new_uni_request_no ?? null);
        $uniReqNo = ($rawReqNo && $rawReqNo !== '---') ? $rawReqNo : '';
        $uniReqDate = $application->new_uni_request_date ? format_sys_date($application->new_uni_request_date) : '';
        $decisionDate = format_sys_date(now());
        $eligibilityDate = $application->interview_date ? format_sys_date($application->interview_date) : $decisionDate;
        $committeeDate = format_sys_date($application->latestDecision ? $application->latestDecision->decision_date : now());
        $decisionNo = $request->query('decision_no', $request->input('decision_no', ''));

        // بيانات الجامعة الحكومية (لعضو الهيئة التدريسية)
        $govEd = $application->educations->first(function($e) {
            return $e->thesis_title === 'عضو هيئة تدريسية في جامعة حكومية' || (optional($e->level)->name && str_contains(optional($e->level)->name, 'حكومية'));
        }) ?? $application->educations->first();

        $govUniRaw = $govEd->university->name ?? ($govEd->university_other ?? 'جامعة الفرات');
        $govUni = preg_replace('/^(جامعة|جامعه)\s+/u', '', trim($govUniRaw));
        $govFaculty = $govEd->faculty ?? ($application->work_faculty ?: 'كلية التربية');
        $govDepartment = $govEd->department ?? ($application->work_department ?: 'أصول التربية');

        // بيانات مركز البحوث
        $rcEd = $application->educations->first(function($e) {
            return $e->thesis_title === 'باحث في مركز بحوث' || (optional($e->level)->name && str_contains(optional($e->level)->name, 'بحوث'));
        });
        $rcCenterName = $rcEd ? ($rcEd->faculty ?: 'مركز الدراسات والبحوث العلمية') : 'مركز الدراسات والبحوث العلمية';
        $rcDepartment = $rcEd ? ($rcEd->department ?: '') : '';
        $rcRank = $rcEd ? ($rcEd->rank ?: 'باحث') : 'باحث';

        // 1. تنسيقات شهادة الدكتوراه
        $phdFormattedSpec = self::formatDegreeSpecialization($phdEd);
        $phdYear = self::getGrantYear($phdEd);
        if (empty($phdYear) && $phdEd) {
            $phdYear = $phdEd->graduation_year ?? date('Y');
        }
        $phdUniCountry = self::formatUniAndCountry($phdEd);
        $phdCountry = $phdEd && $phdEd->country ? $phdEd->country->name : ($phdEd->country_other ?? '');
        $phdGeneral = $phdEd ? ($phdEd->general_specialization ?: ($phdEd->faculty ?: '')) : '';
        $phdExact = $phdEd ? ($phdEd->exact_specialization ?: ($phdEd->specialization ?: ($phdEd->section_name ?: ''))) : '';
        $phdFaculty = $phdEd ? ($phdEd->faculty ?: '') : '';
        $phdDepartment = $phdEd ? ($phdEd->department ?: ($phdEd->faculty ?: '')) : '';
        $phdSpec = $phdFormattedSpec;
        $phdUniRaw = $phdEd ? ($phdEd->university_name_manual ?: (optional($phdEd->university)->name ?: ($phdEd->university_other ?? ''))) : '';
        $phdUni = preg_replace('/^(جامعة|جامعه)\s+/u', '', trim($phdUniRaw));

        // 2. تنسيقات شهادة الماجستير
        $masterFormattedSpec = self::formatDegreeSpecialization($masterEd);
        $masterYear = self::getGrantYear($masterEd);
        if (empty($masterYear) && $masterEd) {
            $masterYear = $masterEd->graduation_year ?? ($isDoctorate ? date('Y') - 3 : date('Y'));
        }
        $masterUniCountry = self::formatUniAndCountry($masterEd);
        $masterFaculty = $masterEd ? ($masterEd->faculty ?: '') : '';
        $masterCountry = $masterEd && $masterEd->country ? $masterEd->country->name : ($masterEd->country_other ?? (optional($masterEd->country)->name ?: ''));
        $masterDepartment = $masterEd ? ($masterEd->department ?: ($masterEd->faculty ?: '')) : '';
        $masterGeneral = $masterEd ? ($masterEd->general_specialization ?: ($masterEd->faculty ?: '')) : '';
        $masterExact = $masterEd ? ($masterEd->exact_specialization ?: ($masterEd->specialization ?: ($masterEd->section_name ?: ($masterEd->department ?: '')))) : '';
        $masterSpec = $masterFormattedSpec;
        $masterUniRaw = $masterEd ? ($masterEd->university_name_manual ?: ($masterEd->university_other ?: (optional($masterEd->university)->name ?: ''))) : '';
        $masterUni = preg_replace('/^(جامعة|جامعه)\s+/u', '', trim($masterUniRaw));
        $masterIsResearch = $masterEd && (str_contains(strtolower($masterEd->study_system ?? ''), 'بحثي') || str_contains($masterEd->thesis_title ?? '', 'بحثي') || str_contains($masterEd->notes ?? '', 'بحثي') || $isForeignTheoretical);

        // 3. تنسيقات شهادة الإجازة (البكالوريوس)
        $baFormattedSpec = self::formatDegreeSpecialization($bachelorEd, true);
        $baYear = self::getGrantYear($bachelorEd);
        if (empty($baYear) && $bachelorEd) {
            $baYear = $bachelorEd->graduation_year ?? ($isDoctorate ? date('Y') - 7 : date('Y') - 4);
        }
        $baUniCountry = self::formatUniAndCountry($bachelorEd);
        $baFaculty = $bachelorEd ? ($bachelorEd->faculty ?: ($bachelorEd->general_specialization ?: '')) : '';
        $baCountry = $bachelorEd && $bachelorEd->country ? $bachelorEd->country->name : ($bachelorEd->country_other ?? '');
        $baGeneral = $bachelorEd ? ($bachelorEd->general_specialization ?: ($bachelorEd->faculty ?: '')) : '';
        $baUniRaw = $bachelorEd ? ($bachelorEd->university_name_manual ?: (optional($bachelorEd->university)->name ?: ($bachelorEd->university_other ?: ''))) : '';
        $baUni = preg_replace('/^(جامعة|جامعه)\s+/u', '', trim($baUniRaw));
        $baSection = $baFormattedSpec;
        $baSpec = $baFormattedSpec;

        // 4. استخراج اختصاص التعيين والتدريس النهائي (يوضع في آخر جملة من المادة 1)
        // القاعدة: نأخذ الاختصاص الدقيق للشهادة العليا (ماجستير أو دكتوراه)، وإن لم يوجد نأخذ الاختصاص العام
        $higherEd = ($isDoctorate || $isForeignDoctorate) ? $phdEd : $masterEd;
        $appFallback = $application->work_department ?: ($application->work_faculty ?: '');
        $finalTeachingSpec = self::getTeachingSpecialization($higherEd, $appFallback);
        if (empty($finalTeachingSpec)) {
            $finalTeachingSpec = ($isDoctorate || $isForeignDoctorate)
                ? (self::getTeachingSpecialization($masterEd) ?: ($phdFormattedSpec ?: '---'))
                : ($masterFormattedSpec ?: '---');
        }
        $teachingDept = $finalTeachingSpec;

        // مسار التدريس
        $teachingType = ($isForeignTheoretical || str_contains($requestType, 'نظري'))
            ? 'المقررات النظرية'
            : 'الجوانب العملية والتطبيقية (عضو هيئة فنية)';

        $canEligibility = in_array($application->status, ['بانتظار إصدار القرار', 'بانتظار صدور القرار', 'تم الصدور']);

        return [
            'application'             => $application,
            'candidate'               => $candidate,
            'bachelorEd'              => $bachelorEd,
            'masterEd'                => $masterEd,
            'phdEd'                   => $phdEd,
            'isDoctorate'             => $isDoctorate,
            'isForeignDoctorate'      => $isForeignDoctorate,
            'isFacultyPermission'     => $isFacultyPermission,
            'isApplied'               => $isApplied,
            'isForeignMaster'         => $isForeignMaster,
            'isForeignApplied'        => $isForeignApplied,
            'isForeignTheoretical'    => $isForeignTheoretical,
            'isResearchCenter'        => $isResearchCenter,
            'rcCenterName'            => $rcCenterName,
            'rcDepartment'            => $rcDepartment,
            'rcRank'                  => $rcRank,
            'g'                       => $g,
            'isFemale'                => $isFemale,
            'fullTimeWord'            => $fullTimeWord,
            'quotaWord'               => $quotaWord,
            'appointedResearcherWord' => $appointedResearcherWord,
            'candidateTitleWord'      => $candidateTitleWord,
            'genderTitle'             => $genderTitle,
            'genderDegreeHolder'      => $genderDegreeHolder,
            'genderCommitment'        => $genderCommitment,
            'genderQuota'             => $genderQuota,
            'teachingType'            => $teachingType,
            'govUni'                  => $govUni,
            'govFaculty'              => $govFaculty,
            'govDepartment'           => $govDepartment,
            'docType'                 => $docType,
            'decisionType'            => $decisionType,
            'decisionTitle'           => $decisionTitle,
            'candidateName'           => $candidateName,
            'titlePrefix'             => $titlePrefix,
            'candidateTitle'          => $candidateTitle,
            'candidateTitlePrep'      => $candidateTitlePrep,
            'qualifiedWord'           => $qualifiedWord,
            'qualifierHolderWord'     => $qualifierHolderWord,
            'assignWord'              => $assignWord,
            'uniName'                 => $uniName,
            'uniReqNo'                => $uniReqNo,
            'uniReqDate'              => $uniReqDate,
            'eligibilityDate'         => $eligibilityDate,
            'committeeDate'           => $committeeDate,
            'decisionNo'              => $decisionNo,
            'decisionDate'            => $decisionDate,
            'masterIsResearch'        => $masterIsResearch,
            'canEligibility'          => $canEligibility,
            'genderAttrs'             => $genderAttrs,
            'phdFormattedSpec'        => $phdFormattedSpec,
            'phdUniCountry'           => $phdUniCountry,
            'phdGeneral'              => $phdGeneral,
            'phdExact'                => $phdExact,
            'phdSpec'                 => $phdSpec,
            'phdFaculty'              => $phdFaculty,
            'phdDepartment'           => $phdDepartment,
            'phdCountry'              => $phdCountry,
            'phdYear'                 => $phdYear,
            'phdUni'                  => $phdUni,
            'masterFormattedSpec'     => $masterFormattedSpec,
            'masterUniCountry'        => $masterUniCountry,
            'masterFaculty'           => $masterFaculty,
            'masterCountry'           => $masterCountry,
            'masterDepartment'        => $masterDepartment,
            'masterGeneral'           => $masterGeneral,
            'masterExact'             => $masterExact,
            'masterSpec'              => $masterSpec,
            'masterYear'              => $masterYear,
            'masterUni'               => $masterUni,
            'baFormattedSpec'         => $baFormattedSpec,
            'baUniCountry'            => $baUniCountry,
            'baFaculty'               => $baFaculty,
            'baCountry'               => $baCountry,
            'baGeneral'               => $baGeneral,
            'baSection'               => $baSection,
            'baSpec'                  => $baSpec,
            'baYear'                  => $baYear,
            'baUni'                   => $baUni,
            'teachingDept'            => $teachingDept,
            'finalTeachingSpec'       => $finalTeachingSpec,
            'customArticle'           => $request->input('custom_article', null),
            'customSignerDirector'    => $request->input('signer_director', null),
            'customSignerSecretary'   => $request->input('signer_secretary', null),
            'customSignerMinister'    => $request->input('signer_minister', null),
        ];
    }

    public function show(Request $request, $id)
    {
        $application = Application::with([
            'candidate.nationality',
            'workUniversity',
            'educations.level',
            'educations.country',
            'educations.university',
            'latestDecision',
        ])->findOrFail($id);

        $data = $this->prepareDecisionData($application, $request);

        if (in_array($application->status, ['مسودة', 'مرفوض', 'بانتظار الوثائق'])) {
            return redirect()->route('admin.applications.index')
                ->with('error', '⚠️ توليد القرار غير متاح للطلبات المرفوضة أو المسودات أو التي بانتظار استكمال الوثائق.');
        }

        $isSingleDecisionType = $data['isFacultyPermission'] || $data['isApplied'] || $data['isResearchCenter'];

        if (!$isSingleDecisionType && $data['docType'] === 'eligibility') {
            if (!in_array($application->status, ['بانتظار إصدار القرار', 'بانتظار صدور القرار', 'تم الصدور'])) {
                return redirect()->route('admin.applications.index')
                    ->with('error', '⚠️ قرار الأهلية متاح فقط للطلبات التي حالتها (بانتظار إصدار القرار) بعد اجتياز المقابلة بنجاح.');
            }
        }


        return view('admin.reports.generated_decision', $data);
    }

    public function adopt(Request $request, $id)
    {
        $application = Application::with([
            'candidate.nationality',
            'workUniversity',
            'educations.level',
            'educations.country',
            'educations.university',
            'latestDecision',
        ])->findOrFail($id);

        if (!in_array($application->status, ['بانتظار إصدار القرار', 'بانتظار صدور القرار', 'تم الصدور'])) {
            return redirect()->route('admin.applications.index')->with('error', 'توليد وإصدار القرارات متاح فقط للطلبات التي حالتها (بانتظار إصدار القرار).');
        }

        $data = $this->prepareDecisionData($application, $request);
        $decisionNo = $data['decisionNo'] ?: (rand(100, 999) . '/' . date('Y'));
        $data['decisionNo'] = $decisionNo;

        // 1. Render PDF HTML
        $html = view('admin.reports.generated_decision_pdf_template', $data)->render();

        // 2. Generate and store PDF file
        $pdf = Pdf::loadHTML($html)->setPaper('A4', 'portrait');
        $pdfContent = $pdf->output();
        $safeDecNo = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $decisionNo ?: 'Official');
        $safeAppNo = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $application->application_no ?? ('App_' . $application->id));
        $cleanCandidateName = trim(preg_replace('/\s+/', '_', preg_replace('/[^\p{L}\p{N}\s\-_]/u', '', $data['candidateName'])));
        $fileName = 'Official_Decision_No' . $safeDecNo . '_' . $safeAppNo . ($cleanCandidateName ? '_' . $cleanCandidateName : '') . '.pdf';
        $filePath = 'decisions/' . $fileName;

        Storage::disk('public')->put($filePath, $pdfContent);

        // 3. Update application status
        $application->status = 'تم الصدور';
        $application->save();

        // 4. Create ApplicationDecision record with file_path
        ApplicationDecision::create([
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
            'message' => "📜 [إشعار رسمي - صدور قرار التعادل]: تم صدور واعتماد قرار معادلة الشهادة العلمية رسمياً برقم ({$decisionNo}) للطلب رقم (#{$application->application_no}) للمرشح ({$data['candidateName']}). يمكنك الاطلاع على نص القرار وتحميله أصولاً.",
            'is_read' => false,
        ]);

        return redirect()->route('admin.applications.index')->with('success', 'تم اعتماد وإصدار قرار التعادل رسمياً بنجاح وتحديث حالة الطلب إلى (تم الصدور) وإشعار الجامعة.');
    }

    public function downloadPdf(Request $request, $id)
    {
        $application = Application::with([
            'candidate.nationality',
            'workUniversity',
            'educations.level',
            'educations.country',
            'educations.university',
            'latestDecision',
        ])->findOrFail($id);

        $data = $this->prepareDecisionData($application, $request);

        $html = view('admin.reports.generated_decision_pdf_template', $data)->render();

        $pdf = Pdf::loadHTML($html)->setPaper('A4', 'portrait');
        $decisionNo = $data['decisionNo'];
        $safeDecNo = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $decisionNo ?: 'Draft');
        $safeAppNo = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $application->application_no ?? ('App_' . $application->id));
        $cleanCandidateName = trim(preg_replace('/\s+/', '_', preg_replace('/[^\p{L}\p{N}\s\-_]/u', '', $data['candidateName'])));
        $fileName = 'Decision_' . $safeDecNo . '_' . $safeAppNo . ($cleanCandidateName ? '_' . $cleanCandidateName : '') . '.pdf';

        return $pdf->download($fileName);
    }

    private function getGenderAttributes($candidate, $isDoctorate = false)
    {
        $gender = $candidate ? ($candidate->gender ?? null) : null;
        if ($gender === 'أنثى') {
            return [
                'titlePrefix'             => $isDoctorate ? 'إن السيدة الدكتورة' : 'إن السيدة',
                'candidateTitle'          => $isDoctorate ? 'الدكتورة' : 'السيدة',
                'candidateTitlePrep'      => $isDoctorate ? 'للدكتورة' : 'للسيدة',
                'qualifiedWord'           => 'مؤهلة',
                'qualifierHolderWord'     => 'الحائزة',
                'promotionWord'           => 'ترقى',
                'fullTimeWord'            => 'تفرغها',
                'quotaWord'               => 'نصابها',
                'appointedResearcherWord' => 'والمعينة باحثة',
                'assignWord'              => 'تكليفها',
            ];
        } elseif ($gender === 'ذكر') {
            return [
                'titlePrefix'             => $isDoctorate ? 'إن السيد الدكتور' : 'إن السيد',
                'candidateTitle'          => $isDoctorate ? 'الدكتور' : 'السيد',
                'candidateTitlePrep'      => $isDoctorate ? 'للدكتور' : 'للسيد',
                'qualifiedWord'           => 'مؤهل',
                'qualifierHolderWord'     => 'الحائز',
                'promotionWord'           => 'يرقى',
                'fullTimeWord'            => 'تفرغه',
                'quotaWord'               => 'نصابه',
                'appointedResearcherWord' => 'والمعين باحث',
                'assignWord'              => 'تكليفه',
            ];
        } else {
            return [
                'titlePrefix'             => $isDoctorate ? 'إن السيد/ة الدكتور/ة' : 'إن السيد/السيدة',
                'candidateTitle'          => $isDoctorate ? 'الدكتور/ة' : 'السيد/السيدة',
                'candidateTitlePrep'      => $isDoctorate ? 'للدكتور/ة' : 'للسيد/السيدة',
                'qualifiedWord'           => 'مؤهل/ة',
                'qualifierHolderWord'     => 'الحائز/ة',
                'promotionWord'           => 'تـ/يرقى',
                'fullTimeWord'            => 'تفرغه/ا',
                'quotaWord'               => 'نصابه/ا',
                'appointedResearcherWord' => 'والمعين/ة باحث/ة',
                'assignWord'              => 'تكليفه/ا',
            ];
        }
    }

    private function formatCandidateFullName($candidate)
    {
        if (!$candidate) return 'غ/م';
        
        if (!empty($candidate->first_name)) {
            $first  = trim($candidate->first_name);
            $father = trim($candidate->father_name ?? '');
            $grand  = trim($candidate->grand_name ?? '');
            $last   = trim($candidate->last_name ?? '');
            
            $collected = array_filter([$first, $father, $grand, $last]);
            if (count($collected) >= 2) {
                return implode(' ', $collected);
            }
        }

        $fullName = trim($candidate->full_name ?? '');
        $fullName = preg_replace('/^(أ\.د\.?|د\.?|دكتور\/ة?|دكتورة|الدكتور|الدكتورة|السيد|السيدة|الآنسة)\s+/u', '', $fullName);
        
        $fatherName = trim($candidate->father_name ?? '');
        $fatherName = preg_replace('/^(أ\.د\.?|د\.?|دكتور\/ة?|دكتورة|الدكتور|الدكتورة|السيد|السيدة|الآنسة)\s+/u', '', $fatherName);

        if (empty($fatherName) || empty($fullName)) {
            return $fullName ?: 'غ/م';
        }

        if (mb_strpos($fullName, $fatherName) !== false) {
            return $fullName;
        }

        $parts = preg_split('/\s+/u', $fullName);
        
        if (count($parts) > 1) {
            $compoundPrefixes = ['عبد', 'أمة', 'نور', 'سيف', 'علاء', 'شمس', 'بهاء', 'جمال', 'حسام', 'صلاح', 'تقي', 'عماد', 'جاد'];
            
            if (in_array($parts[0], $compoundPrefixes) && count($parts) > 2) {
                $firstName = $parts[0] . ' ' . $parts[1];
                array_shift($parts);
                array_shift($parts);
                $lastName = implode(' ', $parts);
            } else {
                $firstName = array_shift($parts);
                $lastName = implode(' ', $parts);
            }

            return $firstName . ' ' . $fatherName . ' ' . $lastName;
        }

        return $fullName . ' ' . $fatherName;
    }
}