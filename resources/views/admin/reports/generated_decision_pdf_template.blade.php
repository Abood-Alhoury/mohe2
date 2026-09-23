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

@if(($docType ?? 'equivalence') === 'eligibility')
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
                - إن {{ $titlePrefix ?? 'السيد الدكتور' }} {{ $candidateName }} {{ $genderAttrs['promotionWord'] ?? 'يرقى' }} للتدريس في الجامعات السورية الخاصة عملاً بالقرارات الواردة أعلاه.
            @else
                - {{ $titlePrefix ?? 'إن السيد/السيدة' }} {{ $candidateName }} {{ $qualifiedWord ?? 'مؤهل/ة' }} لتدريس في الجامعات السورية الخاصة عملاً بالمقررات الواردة أعلاه.
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
        <div style="margin-bottom: 3px;">وعلى قرار مجلس التعليم العالي رقم /236/ تاريخ 2007/7/15.</div>
        <div style="margin-bottom: 3px;">وعلى قرار مجلس التعليم العالي رقم /175/ تاريخ 2022/6/16 وتعديلاته.</div>
        <div>وعلى كتاب {{ $uniName }} رقم {{ $uniReqNo ?: '        ' }} تاريخ {{ $uniReqDate ?: '        ' }}</div>
        @if($decisionType === 'foreign_doctorate' || $decisionType === 'foreign_master_theoretical')
            <div style="margin-top: 3px;">وعلى قرار لجنة التأهيل ومعادلة الدرجات العلمية المنعقدة في {{ $committeeDate ?? ($decisionDate ?? '') }}</div>
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
                <strong>المادة 1-</strong> السماح {{ $candidateTitlePrep ?? 'للسيد الدكتور' }} {{ $candidateName }} (عضو الهيئة التدريسية في {{ $govFaculty }} بجامعة {{ $govUni }}) بالتدريس باختصاص {{ $teachingDept }} في الجامعات الخاصة السورية.
            @elseif(!empty($isResearchCenter))
                <strong>المادة 1-</strong> تعدّ درجة الدكتوراه في {{ $phdSpec }} الممنوحة {{ $candidateTitlePrep ?? 'للدكتور' }} {{ $candidateName }} ({{ $appointedResearcherWord ?? 'والمعين باحث' }} لدى {{ $rcCenterName ?? 'مركز الدراسات والبحوث العلمية' }}) عام {{ $phdYear }} من {{ $phdUni }}، مؤهلة للتدريس باختصاص {{ $teachingDept }} بالجامعات الخاصة السورية.
            @elseif(!empty($isForeignDoctorate) || $decisionType === 'foreign_doctorate')
                <strong>المادة 1-</strong> تعدّ درجة الدكتوراه في {{ $phdDepartment ?: ($phdFaculty ?: $phdSpec) }} شعبة {{ $phdExact ?: $phdSpec }} الممنوحة {{ $candidateTitlePrep ?? 'للدكتور' }} <strong>{{ $candidateName }}</strong> عام {{ $phdYear }} من {{ preg_match('/^(جامعة|جامعه)\s+/u', $phdUni) ? $phdUni : 'جامعة ' . $phdUni }} في {{ $phdCountry ?: '---' }}، والمسبوقة بدرجة الماجستير في {{ $masterDepartment ?: ($masterFaculty ?: $masterGeneral) }} اختصاص {{ $masterExact ?: $masterSpec }} الممنوحة عام {{ $masterYear }} من {{ preg_match('/^(جامعة|جامعه)\s+/u', $masterUni) ? $masterUni : 'جامعة ' . $masterUni }}{{ !empty($masterCountry) ? ' في ' . $masterCountry : '' }}، ودرجة الإجازة في {{ $baSpec ?: ($baFaculty ?: $baGeneral) }} الممنوحة عام {{ $baYear }} من {{ preg_match('/^(جامعة|جامعه)\s+/u', $baUni) ? $baUni : 'جامعة ' . $baUni }}{{ (!empty($baCountry) && $baCountry !== 'سوريا') ? ' في ' . $baCountry : '' }}، معادلة ومؤهلة لدرجة الدكتوراه المطلوبة للتعيين والتدريس باختصاص {{ $teachingDept }} بالجامعات الخاصة السورية.
            @elseif(!empty($isDoctorate))
                <strong>المادة 1-</strong> تعدّ درجة الدكتوراه في {{ $phdSpec }} الممنوحة {{ $candidateTitlePrep ?? 'للدكتور' }} {{ $candidateName }} عام {{ $phdYear }} من جامعة {{ $phdUni }}، والمسبوقة بدرجة الماجستير في {{ $masterSpec }} الممنوحة عام {{ $masterYear }} من جامعة {{ $masterUni }}، ودرجة الإجازة في {{ $baGeneral }}{{ $baSection ? '/' . $baSection : '' }} الممنوحة عام {{ $baYear }} من جامعة {{ $baUni }}، مؤهلة للتعيين والتدريس باختصاص {{ $teachingDept }} بالجامعات الخاصة السورية.
            @elseif($decisionType === 'foreign_master_applied' || $decisionType === 'applied_master' || (!empty($isApplied) && empty($isDoctorate)))
                <strong>المادة-1</strong> تعدّ درجة الماجستير في {{ $masterFaculty ?: $masterGeneral }}{!! $masterSpec ? ' اختصاص ' . $masterSpec : '' !!} الممنوحة عام {{ $masterYear }} {{ $candidateTitlePrep ?? 'للسيد' }} <strong>{{ $candidateName }}</strong> من جامعة {{ $masterUni }}، والمسبوقة بدرجة الإجازة في {{ $baGeneral }}{{ $baSection ? ' قسم ' . $baSection : '' }} الممنوحة عام {{ $baYear }} من جامعة {{ $baUni }}، محققةً لشروط الشهادة والاختصاص من أجل تدريس <strong>الجوانب التطبيقية</strong> في اختصاص {{ $teachingDept }} بالجامعات الخاصة السورية.
            @elseif($decisionType === 'foreign_master_theoretical')
                <strong>المادة 1-</strong> الموافقة على تكليف {{ $candidateTitle ?? 'السيد' }} {{ $candidateName }}، {{ $qualifierHolderWord ?? 'الحائز' }} درجة الماجستير{{ !empty($masterIsResearch) ? ' البحثي' : '' }} في {{ $masterSpec ?? ($masterGeneral ?? ($masterFaculty ?? '')) }} الممنوحة عام {{ $masterYear }} من {{ $masterUni }}{{ !empty($masterCountry) ? ' في ' . $masterCountry : '' }}، والمسبوقة بدرجة الإجازة في {{ $baSpec ?? ($baGeneral ?? '') }} الممنوحة عام {{ $baYear }} من {{ $baUni }}، بتدريس المقررات النظرية في اختصاص {{ $teachingDept }} في الجامعات الخاصة السورية على أن يكون {{ $fullTimeWord ?? 'تفرغه' }} كلياً فيها، وألا يقل {{ $quotaWord ?? 'نصابه' }} التدريسي عن /12/ ساعة أسبوعياً.
            @else
                <strong>المادة 1-</strong> الموافقة على {{ $assignWord ?? 'تكليف' }} {{ $candidateTitle ?? 'السيد' }} {{ $candidateName }}، {{ $qualifierHolderWord ?? 'الحائز' }} درجة الماجستير في {{ $masterGeneral }}{{ $masterExact ? ' اختصاص ' . $masterExact : '' }} الممنوحة عام {{ $masterYear }} من جامعة {{ $masterUni }}، والمسبوقة بدرجة الإجازة في {{ $baGeneral }}{{ $baSection ? ' قسم ' . $baSection : '' }} الممنوحة عام {{ $baYear }} من جامعة {{ $baUni }}، بتدريس المقررات النظرية في اختصاص {{ $teachingDept }} في الجامعات الخاصة السورية على أن يكون {{ $fullTimeWord ?? 'تفرغه' }} كلياً فيها، وألا يقل {{ $quotaWord ?? 'نصابه' }} التدريسي عن /12/ ساعة أسبوعياً.
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
                <div>{{ $customSignerSecretary ?: 'الدكتور علي جاسم' }}</div>
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
    <div class="copies-div">
        <div style="text-decoration: underline; font-weight: bold; margin-bottom: 3px;">صورة إلى:</div>
        <div>- مجلس التعليم العالي: مكتب التعادل – الديوان.</div>
        <div>- أمانة سر المجلس (للتعميم على الجامعة المعنية عبر البريد الالكتروني).</div>
        <div>- مؤسسات التعليمية الخاصة.</div>
    </div>

</body>
</html>