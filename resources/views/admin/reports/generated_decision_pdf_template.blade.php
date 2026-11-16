@php
    $logoFile = file_exists(public_path('assets/report_logo.png')) 
        ? public_path('assets/report_logo.png') 
        : (file_exists(public_path('report_logo.png')) ? public_path('report_logo.png') : null);
    $logoBase64 = $logoFile ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoFile)) : '';

    $fontPath = str_replace('\\', '/', public_path('fonts/IBMPlexSansArabic-Regular.ttf'));
    $fontBold = str_replace('\\', '/', public_path('fonts/IBMPlexSansArabic-Bold.ttf'));
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>{{ $decisionTitle ?? 'قرار رسمي' }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap');

        @font-face {
            font-family: 'IBMPlexArabicLocal';
            src: url("file:///{{ $fontPath }}") format('truetype');
            font-weight: normal;
        }
        @font-face {
            font-family: 'IBMPlexArabicLocal';
            src: url("file:///{{ $fontBold }}") format('truetype');
            font-weight: bold;
        }

        @page {
            size: A4 portrait;
            margin: 10mm 15mm 10mm 15mm;
        }

        body {
            font-family: 'IBMPlexArabicLocal', 'IBM Plex Sans Arabic', 'Traditional Arabic', Tahoma, sans-serif;
            direction: rtl;
            text-align: right;
            color: #000000;
            font-size: 14.5px;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background: #ffffff;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .decision-title-div {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin: 12px 0;
        }

        .addressee-div {
            text-align: center;
            font-size: 15.5px;
            margin: 12px 0;
        }

        .preamble-div {
            font-size: 14px;
            margin-bottom: 12px;
            text-align: justify;
            line-height: 1.65;
        }

        .decree-header-div {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin: 14px 0 10px;
            text-decoration: underline;
        }

        .article-div {
            font-size: 15px;
            margin-bottom: 14px;
            text-align: justify;
            line-height: 2.1;
        }

        .signatures-table td {
            font-size: 14.5px;
            line-height: 1.5;
            font-weight: bold;
            text-align: center;
        }

        .copies-div {
            font-size: 12px;
            line-height: 1.5;
            margin-top: 22px;
            text-align: right;
        }
    </style>
</head>
<body>

@if(($docType ?? 'equivalence') === 'rejection' || !empty($isRejected))
    {{-- ==========================================
         0. قرار / كتاب عدم الموافقة (الطلبات المرفوضة)
    ========================================== --}}
    <table style="margin-bottom: 12px;">
        <tr>
            <td style="width: 40%; text-align: right; font-size: 13px; line-height: 1.45; font-weight: bold;">
                الجمهورية العربية السورية<br>
                مجلس التعليم العالي<br>
                لجنة التأهيل ومعادلة الدرجات العلمية<br>
                الرقم: <span style="font-weight: bold;">{!! ($decisionNo && $decisionNo !== '---') ? $decisionNo : '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</span><br>
                التاريخ: {{ $decisionDate }}
            </td>
            <td style="width: 20%; text-align: center; vertical-align: middle;">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" style="width: 120px; height: auto;" alt="الشعار">
                @endif
            </td>
            <td dir="ltr" style="width: 40%; text-align: left; font-size: 12px; line-height: 1.4; font-weight: bold; font-family: sans-serif;">
                Syrian Arab Republic<br>
                Council of Higher Education
            </td>
        </tr>
    </table>

    <div class="addressee-div">
        السيد الدكتور رئيس {{ $uniName }}
    </div>

    <div class="preamble-div">
        <div>بناءً على أحكام قانون تنظيم الجامعات رقم /6/ لعام 2006، ولائحته التنفيذية.</div>
        <div>والمرسوم التشريعي رقم /36/ لعام 2001 وتعليماته التنفيذية وتعديلاتهما.</div>
        <div>وقرارات مجلس التعليم العالي ولجنة التأهيل ومعادلة الدرجات العلمية ولاسيما القرار رقم /175/ تاريخ 16/2/2022.</div>
    </div>

    <div class="decree-header-div" style="text-align: right; text-decoration: none; font-weight: bold;">
        نفيدكم بالآتي:
    </div>

    <div class="article-div" style="line-height: 1.9;">
        @if(!empty($customArticle))
            {!! $customArticle !!}
        @else
            "عدم الموافقة على تعيين {{ $rejectionTargetTitle ?? ($isFemale ? 'السيدة' : 'السيد') }} <strong>{{ $candidateName }}</strong> {{ $rejectionReasonText ?? 'نظراً لعدم تحقيق شروط معادلة الشهادات الواردة في قرارات مجلس التعليم العالي .' }}"
        @endif
    </div>

    <div style="text-align: center; font-weight: bold; margin: 10px 0 18px;">
        يرجى الاطلاع
    </div>

@elseif(($docType ?? 'equivalence') === 'eligibility')
    {{-- ==========================================
         1. قرارات الأهلية (دكتوراه / ماجستير)
    ========================================== --}}
    
    <!-- ترويسة قرار الأهلية -->
    <table style="margin-bottom: 10px;">
        <tr>
            <td style="width: 40%; text-align: right; font-size: 12.5px; line-height: 1.45;">
                الجمهورية العربية السورية<br>
                مجلس التعليم العالي<br>
                لجنة التأهيل ومعادلة الدرجات العلمية<br>
                الرقم: <span style="color: #0d6efd; font-weight: bold;">{!! ($decisionNo && $decisionNo !== '---') ? $decisionNo : '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</span><br>
                التاريخ: {{ $decisionDate }}
            </td>
            <td style="width: 20%; text-align: center; vertical-align: middle;">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" style="width: 130px; height: auto;" alt="الشعار">
                @endif
            </td>
            <td dir="ltr" style="width: 40%; text-align: left; font-size: 11px; line-height: 1.35; font-family: sans-serif;">
                Syrian Arab Republic<br>
                Council of Higher Education
            </td>
        </tr>
    </table>

    <div class="addressee-div">
        السيد الدكتور رئيس {{ $uniName }} المحترم
    </div>

    <!-- ديباجة الأهلية -->
    <div class="preamble-div">
        @if(!empty($isDoctorate))
            <div>بناءً على أحكام قانون تنظيم الجامعات رقم /6/ لعام 2006، ولائحته التنفيذية.</div>
            <div>والمرسوم التشريعي رقم /36/ لعام 2001 وتعليماته التنفيذية وتعديلاتهما.</div>
            <div>وقرارات مجلس التعليم العالي ولجنة التأهيل ومعادلة الدرجات العلمية ولاسيما القرار رقم /175/ تاريخ 2022/2/16.</div>
            <div>وقرار لجنة التأهيل ومعادلة الدرجات العلمية رقم /<span style="color: #0d6efd; font-weight: bold;">{!! ($decisionNo && $decisionNo !== '---') ? $decisionNo : '678' !!}</span>/ تاريخ {{ $decisionDate }}.</div>
            <div>وما قررته لجنة الأهلية المنعقدة بتاريخ {{ $eligibilityDate }}</div>
        @else
            <div>بناءً على أحكام قانون تنظيم الجامعات رقم /6/ لعام 2006 وتعديلاته.</div>
            <div>وبالمرسوم التشريعي رقم /36/ لعام 2001 وتعديلاته ومستلزماته.</div>
            <div>وبقرار مجلس التعليم العالي لجنة التأهيل ومعادلة الدرجات العلمية بالإجماع القرار رقم /170/ تاريخ 2022/6/16.</div>
            <div>وبقرار لجنة التأهيل ومعادلة الدرجات العلمية رقم /<span style="color: #0d6efd; font-weight: bold;">{!! ($decisionNo && $decisionNo !== '---') ? $decisionNo : '89' !!}</span>/ تاريخ {{ $decisionDate }}.</div>
            <div>وبناءً على ثبوت الأهلية العلمية بتاريخ {{ $eligibilityDate }}</div>
        @endif
    </div>

    <div class="decree-header-div">
        {{ !empty($isDoctorate) ? 'نفيدكم بالآتي:' : 'المشار بالآتي:' }}
    </div>

    <!-- نص قرار الأهلية (يقبل التعديل المباشر إن وجد) -->
    <div class="article-div">
        @if(!empty($customArticle))
            {!! $customArticle !!}
        @else
            @if(!empty($isDoctorate))
                - إن {{ $g['doctor'] ?? ($candidateTitle ?? 'الدكتور') }} {{ $candidateName }} {{ $g['promoted'] ?? ($genderAttrs['promotionWord'] ?? 'يرقى') }} للتدريس في الجامعات السورية الخاصة عملاً بالقرارات الواردة أعلاه.
            @else
                - إن {{ $g['title'] ?? ($candidateTitle ?? 'السيد') }} {{ $candidateName }} {{ $isFemale ? 'مؤهلة' : 'مؤهل' }} للتدريس في الجامعات السورية الخاصة عملاً بالمقررات الواردة أعلاه.
            @endif
        @endif
    </div>

@else
    {{-- ==========================================
         2. قرارات المعادلة والتعادل (كافة الحالات)
    ========================================== --}}

    <!-- الترويسة الموحدة للقرارات -->
    <table style="margin-bottom: 10px;">
        <tr>
            <td style="width: 40%; text-align: right; font-size: 14.5px; font-weight: bold; line-height: 1.4;">
                الجمهورية العربية السورية<br>
                مجلس التعليم العالي<br>
                لجنة التأهيل ومعادلة الدرجات العلمية
            </td>
            <td style="width: 20%; text-align: center; vertical-align: middle;">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" style="width: 140px; height: auto;" alt="الشعار">
                @endif
            </td>
            <td dir="ltr" style="width: 40%; text-align: left; font-size: 14.5px; font-weight: bold; line-height: 1.4; font-family: sans-serif;">
                Syrian Arab Republic<br>
                Council of Higher Education
            </td>
        </tr>
    </table>

    <!-- رقم القرار مع فراغ ملائم ولون أزرق -->
    <div class="decision-title-div">
        القرار رقم / <span style="color: #0d6efd; font-size: 16.5px; min-width: 60px; display: inline-block; text-align: center;">{{ ($decisionNo && $decisionNo !== '---') ? $decisionNo : '        ' }}</span> / ل.م
    </div>

    <!-- ديباجة القرار -->
    <div class="preamble-div">
        <div style="margin-bottom: 3px;">رئيس لجنة التأهيل ومعادلة الدرجات العلمية.</div>
        <div style="margin-bottom: 3px;">بناءً على أحكام قانون تنظيم الجامعات رقم 6 لعام 2006 ولائحته التنفيذية وتعديلاتهما.</div>
        <div style="margin-bottom: 3px;">وعلى قرار مجلس التعليم العالي رقم /236/ تاريخ 15/7/2007.</div>
        <div style="margin-bottom: 3px;">وعلى قرار مجلس التعليم العالي رقم /175/ تاريخ 16/6/2022 وتعديلاته.</div>
        <div>وعلى كتاب {{ $uniName }} رقم {{ $uniReqNo ?: '        ' }} تاريخ {{ $uniReqDate ?: '        ' }}</div>
        @if($decisionType === 'foreign_doctorate' || $decisionType === 'foreign_master_theoretical' || $decisionType === 'foreign_master_applied' || !empty($isForeignMaster))
            <div style="margin-top: 3px;">وعلى قرار لجنة التأهيل ومعادلة الدرجات العلمية المنعقدة في {{ $committeeDate ?? ($decisionDate ?? '') }}</div>
        @endif
        @if(!empty($requiresEligibility))
            <div style="margin-top: 3px;">وعلى قرار الأهلية رقم / {{ $eligibilityDecisionNo ?: '        ' }} / تاريخ {{ $eligibilityDecisionDate ?: '   /   /   ' }}</div>
        @endif
    </div>

    <div class="decree-header-div">
        يقرر ما يأتي:
    </div>

    <!-- المادة 1: تأخذ النص المعدل على الشاشة أولاً، أو التوليد التلقائي حسب نوع القرار -->
    <div class="article-div">
        @if(!empty($customArticle))
            {!! $customArticle !!}
        @else
            @if(!empty($isFacultyPermission))
                <strong>المادة 1-</strong> السماح {{ $g['for_doctor'] ?? ($candidateTitlePrep ?? 'للسيد الدكتور') }} <strong>{{ $candidateName }}</strong> (عضو الهيئة التدريسية في {{ $govFaculty }} بجامعة {{ $govUni }}) بالتدريس باختصاص {{ $finalTeachingSpec ?? $teachingDept }} في الجامعات الخاصة السورية.
            @elseif(!empty($isResearchCenter))
                <strong>المادة 1-</strong> تعدّ درجة الدكتوراه في {{ $phdFormattedSpec ?? $phdSpec }} الممنوحة {{ $g['for_doctor'] ?? ($candidateTitlePrep ?? 'للدكتور') }} <strong>{{ $candidateName }}</strong> ({{ $g['appointed_researcher'] ?? ($appointedResearcherWord ?? 'والمعين باحث') }} لدى {{ $rcCenterName ?? 'مركز الدراسات والبحوث العلمية' }}) عام {{ $phdYear }} {{ $phdUniCountry ?? ('من ' . $phdUni) }}، مؤهلة للتدريس باختصاص {{ $finalTeachingSpec ?? $teachingDept }} بالجامعات الخاصة السورية.
            @elseif(!empty($isForeignDoctorate) || $decisionType === 'foreign_doctorate')
                <strong>المادة 1-</strong> تعدّ درجة الدكتوراه في {{ $phdFormattedSpec ?? ($phdDepartment ?: ($phdFaculty ?: $phdSpec)) }} الممنوحة {{ $g['for_doctor'] ?? ($candidateTitlePrep ?? 'للدكتور') }} <strong>{{ $candidateName }}</strong> عام {{ $phdYear }} {{ $phdUniCountry }}، والمسبوقة بدرجة الماجستير في {{ $masterFormattedSpec }} الممنوحة عام {{ $masterYear }} {{ $masterUniCountry }}، ودرجة الإجازة في {{ $baFormattedSpec }} الممنوحة عام {{ $baYear }} {{ $baUniCountry }}، معادلة ومؤهلة لدرجة الدكتوراه المطلوبة للتعيين والتدريس باختصاص {{ $finalTeachingSpec ?? $teachingDept }} بالجامعات الخاصة السورية.
            @elseif(!empty($isDoctorate))
                <strong>المادة 1-</strong> تعدّ درجة الدكتوراه في {{ $phdFormattedSpec ?? $phdSpec }} الممنوحة {{ $g['for_doctor'] ?? ($candidateTitlePrep ?? 'للدكتور') }} <strong>{{ $candidateName }}</strong> عام {{ $phdYear }} {{ $phdUniCountry }}، والمسبوقة بدرجة الماجستير في {{ $masterFormattedSpec }} الممنوحة عام {{ $masterYear }} {{ $masterUniCountry }}، ودرجة الإجازة في {{ $baFormattedSpec }} الممنوحة عام {{ $baYear }} {{ $baUniCountry }}، مؤهلة للتعيين والتدريس باختصاص {{ $finalTeachingSpec ?? $teachingDept }} بالجامعات الخاصة السورية.
            @elseif($decisionType === 'foreign_master_theoretical')
                <strong>المادة 1-</strong> الموافقة على تكليف {{ $g['title'] ?? ($genderTitle ?? 'السيد') }} <strong>{{ $candidateName }}</strong>، {{ $g['holder'] ?? ($genderDegreeHolder ?? 'الحائز') }} درجة الماجستير{{ !empty($masterIsResearch) ? ' البحثي' : '' }} في {{ $masterFormattedSpec }} الممنوحة عام {{ $masterYear }} {{ $masterUniCountry }}، والمسبوقة بدرجة الإجازة في {{ $baFormattedSpec }} الممنوحة عام {{ $baYear }} {{ $baUniCountry }}، بتدريس المقررات النظرية في اختصاص {{ $finalTeachingSpec ?? $teachingDept }} في الجامعات الخاصة السورية على أن يكون {{ $g['dedication'] ?? ($genderCommitment ?? 'تفرغه') }} كلياً فيها، وألا يقل {{ $g['quota'] ?? ($genderQuota ?? 'نصابه') }} التدريسي عن /12/ ساعة أسبوعياً.
            @elseif($decisionType === 'foreign_master_applied')
                <strong>المادة 1-</strong> تعد درجة الماجستير في {{ $masterFormattedSpec }} الممنوحة عام {{ $masterYear }} {{ $g['title_prep'] ?? ($candidateTitlePrep ?? 'للسيد') }} <strong>{{ $candidateName }}</strong> {{ $masterUniCountry }}، والمسبوقة بدرجة الإجازة في {{ $baFormattedSpec }} الممنوحة عام {{ $baYear }} {{ $baUniCountry }}، محققتين لشروط الشهادة والاختصاص من أجل تدريس <strong>الجوانب التطبيقية (عضو هيئة فنية)</strong> في اختصاص {{ $finalTeachingSpec ?? $teachingDept }} بالجامعات الخاصة السورية.
            @elseif($decisionType === 'applied_master' || (!empty($isApplied) && empty($isDoctorate)))
                <strong>المادة 1-</strong> تعد درجة الماجستير في {{ $masterFormattedSpec }} الممنوحة عام {{ $masterYear }} {{ $g['title_prep'] ?? ($candidateTitlePrep ?? 'للسيد') }} <strong>{{ $candidateName }}</strong> {{ $masterUniCountry }}، والمسبوقة بدرجة الإجازة في {{ $baFormattedSpec }} الممنوحة عام {{ $baYear }} {{ $baUniCountry }}، محققتين لشروط الشهادة والاختصاص من أجل تدريس <strong>الجوانب التطبيقية</strong> في اختصاص {{ $finalTeachingSpec ?? $teachingDept }} بالجامعات الخاصة السورية.
            @else
                <strong>المادة 1-</strong> الموافقة على تكليف {{ $g['title'] ?? ($candidateTitle ?? 'السيد') }} <strong>{{ $candidateName }}</strong>، {{ $g['holder'] ?? ($qualifierHolderWord ?? 'الحائز') }} درجة الماجستير في {{ $masterFormattedSpec }} الممنوحة عام {{ $masterYear }} {{ $masterUniCountry }}، والمسبوقة بدرجة الإجازة في {{ $baFormattedSpec }} الممنوحة عام {{ $baYear }} {{ $baUniCountry }}، بتدريس المقررات النظرية في اختصاص {{ $finalTeachingSpec ?? $teachingDept }} في الجامعات الخاصة السورية على أن يكون {{ $g['dedication'] ?? ($fullTimeWord ?? 'تفرغه') }} كلياً فيها، وألا يقل {{ $g['quota'] ?? ($quotaWord ?? 'نصابه') }} التدريسي عن /12/ ساعة أسبوعياً.
            @endif
        @endif
    </div>


    <!-- المادة 2 وتاريخ دمشق -->
    <div class="article-div">
        <strong>المادة-2</strong> يبلغ هذا القرار من يلزم لتنفيذه.
        <div style="text-align: right; margin-top: 8px;">دمشق في {{ $decisionDate }}</div>
    </div>
@endif

    <!-- قسم التواقيع (موحد لجميع أنواع القرارات) -->
    <table class="signatures-table" style="margin-top: 20px;">
        <tr>
            <td style="width: 50%;">
                <div>مدير التعادل والإنتاج العلمي</div>
                <div style="height: 35px;"></div>
                <div>{{ $customSignerDirector ?: 'المهندس عمار هلال' }}</div>
            </td>
            <td style="width: 50%;">
                <div>أمين مجلس التعليم العالي</div>
                <div style="height: 35px;"></div>
                <div>{{ $customSignerSecretary ?: 'الدكتور علي الجاسم' }}</div>
            </td>
        </tr>
        <tr>
            <td style="width: 50%;"></td>
            <td style="width: 50%; padding-top: 20px;">
                <div>رئيس لجنة التأهيل ومعادلة الدرجات العلمية</div>
                <div>معاون وزير التعليم العالي والبحث العلمي</div>
                <div style="height: 30px;"></div>
                <div>{{ $customSignerMinister ?: 'الدكتور عبد الحميد الخالد' }}</div>
            </td>
        </tr>
    </table>

    <!-- قسم صورة إلى -->
    <div class="copies-div" style="border-top: 1.5px solid #1e293b; padding-top: 8px; margin-top: 24px;">
        <div style="text-decoration: underline; font-weight: bold; margin-bottom: 3px;">صورة إلى:</div>
        <div>- مكتب التعادل – الديوان</div>
        <div>- المؤسسات التعليمية الخاصة</div>
        <div>- أمانة سر المجلس (للتعميم على الجامعة المعنية عبر البريد الالكتروني)</div>
    </div>

</body>
</html>