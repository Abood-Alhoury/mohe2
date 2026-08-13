<!DOCTYPE html>
<html lang="ar">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<style>
@page {
    size: A4 portrait;
    margin: 12mm 16mm;
}

@font-face {
    font-family: 'Segoe UI';
    font-weight: normal;
    src: url("{{ str_replace('\\','/',public_path('fonts/segoeui.ttf')) }}") format("truetype");
}
@font-face {
    font-family: 'Segoe UI';
    font-weight: bold;
    src: url("{{ str_replace('\\','/',public_path('fonts/segoeuib.ttf')) }}") format("truetype");
}

* { 
    font-family: 'Segoe UI', 'DejaVu Sans', sans-serif; 
    box-sizing: border-box;
}

body { 
    direction: rtl; 
    text-align: right; 
    font-size: 13px; 
    color: #000000; 
    margin: 0; 
    padding: 0; 
    background: #ffffff; 
    line-height: 1.6;
}

.pdf-header {
    width: 100%;
    margin-bottom: 15px;
}

.pdf-header td {
    vertical-align: top;
}

.pdf-addressee {
    text-align: center;
    font-size: 16px;
    font-weight: bold;
    color: #000000;
    margin: 18px 0 18px;
}

.pdf-title {
    text-align: center;
    font-size: 17px;
    font-weight: bold;
    color: #000000;
    margin: 15px 0 15px;
}

.pdf-preamble {
    font-size: 13px;
    margin-bottom: 15px;
    text-align: right;
    line-height: 1.8;
    color: #000000;
}

.pdf-decree-header {
    text-align: center;
    font-size: 15px;
    font-weight: bold;
    margin: 14px 0 16px;
    text-decoration: underline;
    color: #000000;
}

.pdf-article {
    font-size: 14px;
    margin-bottom: 20px;
    text-align: right;
    line-height: 2.0;
    color: #000000;
}

.pdf-signatures {
    width: 100%;
    margin-top: 25px;
    margin-bottom: 20px;
    page-break-inside: avoid;
    color: #000000;
}

.pdf-copies {
    font-size: 11.5px;
    line-height: 1.6;
    border-top: 1px solid #cccccc;
    padding-top: 10px;
    page-break-inside: avoid;
    color: #000000;
    text-align: right;
}
</style>
</head>
<body>

@if(($docType ?? 'equivalence') === 'eligibility')
    {{-- =========================================================================
         1. PDF FORMAT A: MASTER ELIGIBILITY DECISION (قرار أهلية للماجستير)
    ========================================================================= --}}

    <!-- TOP HEADER TABLE -->
    <table class="pdf-header" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <!-- Left in DomPDF = Visual Left: English Header -->
            <td style="width: 40%; text-align: left; font-weight: bold; font-size: 11.5px; line-height: 1.35; direction: ltr; color: #000000;">
                Syrian Arab Republic<br>
                council of Higher Education
            </td>

            <!-- Center: Official Logo -->
            <td style="width: 20%; text-align: center; vertical-align: top;">
                <img src="{{ public_path('assets/logo.jpg') }}" alt="شعار المجلس" style="width: 75px; height: 75px; object-fit: contain;">
            </td>

            <!-- Right in DomPDF = Visual Right: Arabic Header -->
            <td style="width: 40%; text-align: right; font-weight: bold; font-size: 13.5px; line-height: 1.45; color: #000000;">
                الجمهورية العربية السورية<br>
                مجلس التعليم العالي<br>
                لجنة التأهيل ومعادلة الدرجات العلمية<br>
                الرقم: {{ ($decisionNo && $decisionNo !== '---') ? $decisionNo : '' }}<br>
                التاريخ: {{ $decisionDate }}
            </td>
        </tr>
    </table>

    <!-- ADDRESSEE TITLE -->
    <div class="pdf-addressee">
        السيد الدكتور رئيس {{ $uniName }} المحترم
    </div>

    <!-- PREAMBLE -->
    <div class="pdf-preamble">
        <div>بناءً على أحكام قانون تنظيم الجامعات رقم /6/ لعام 2006 وتعديلاته.</div>
        <div>وبالمرسوم التشريعي رقم /36/ لعام 2001 وتعديلاته ومستلزماته.</div>
        <div>وبقرار مجلس التعليم العالي لجنة التأهيل ومعادلة الدرجات العلمية بالإجماع القرار رقم /170/ تاريخ 2022/6/16.</div>
        <div>وبقرار لجنة التأهيل ومعادلة الدرجات العلمية رقم /{{ ($decisionNo && $decisionNo !== '---') ? $decisionNo : '89' }}/ تاريخ {{ $decisionDate }}.</div>
        <div>وبناءً على ثبوت الأهلية العلمية بتاريخ <strong style="color: #000000;">{{ $eligibilityDate }}</strong></div>
    </div>

    <!-- DECREE HEADER -->
    <div class="pdf-decree-header">
        المشار بالآتي:
    </div>

    <!-- ARTICLE BODY -->
    <div class="pdf-article">
        - {{ $titlePrefix ?? 'إن السيد/السيدة' }} <strong style="color: #000000;">{{ $candidateName }}</strong> {{ $qualifiedWord ?? 'مؤهل/ة' }} لتدريس في الجامعات السورية الخاصة عملاً بالمقررات الواردة أعلاه.
    </div>

@else
    {{-- =========================================================================
         2. PDF FORMAT B: MASTER EQUIVALENCE DECISION (قرار المعادلة والتعادل)
    ========================================================================= --}}

    <!-- TOP HEADER TABLE -->
    <table class="pdf-header" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <!-- Left in DomPDF = Visual Left: English Header -->
            <td style="width: 40%; text-align: left; font-weight: bold; font-size: 11.5px; line-height: 1.35; direction: ltr; color: #000000;">
                Syrian Arab Republic<br>
                council of Higher Education
            </td>

            <!-- Center: Official Logo -->
            <td style="width: 20%; text-align: center; vertical-align: middle;">
                <img src="{{ public_path('assets/logo.jpg') }}" alt="شعار المجلس" style="width: 75px; height: 75px; object-fit: contain;">
            </td>

            <!-- Right in DomPDF = Visual Right: Arabic Header -->
            <td style="width: 40%; text-align: right; font-weight: bold; font-size: 13.5px; line-height: 1.45; color: #000000;">
                الجمهورية العربية السورية<br>
                مجلس التعليم العالي<br>
                لجنة التأهيل ومعادلة الدرجات العلمية
            </td>
        </tr>
    </table>

    <!-- DECISION NUMBER & TITLE -->
    <div class="pdf-title">
        القرار رقم / &nbsp;{{ ($decisionNo && $decisionNo !== '---') ? $decisionNo : '' }}&nbsp; / ل.م
    </div>

    <!-- PREAMBLE -->
    <div class="pdf-preamble">
        <div style="font-weight: bold; margin-bottom: 5px;">رئيس لجنة التأهيل ومعادلة الدرجات العلمية.</div>
        <div>بناءً على أحكام قانون تنظيم الجامعات رقم 6 لعام 2006 ولائحته التنفيذية وتعديلاتهما.</div>
        <div>وقرار مجلس التعليم العالي رقم /236/ تاريخ 2007/7/15</div>
        <div>وقرار مجلس التعليم العالي رقم /175/ تاريخ 2022/6/16</div>
        <div>وكتاب {{ $uniName }} رقم /{{ $uniReqNo }}/ تاريخ {{ $uniReqDate }}</div>
    </div>

    <!-- DECREE HEADER -->
    <div class="pdf-decree-header">
        يقرر ما يأتي:
    </div>

    <!-- ARTICLE 1 -->
    <div class="pdf-article">
        @if($decisionType === 'applied_master')
            <strong>المادة -1</strong> تعد درجة الماجستير في <strong>{{ $masterSpec }}</strong> الممنوحة عام <strong>{{ $masterYear }}</strong> {{ $candidateTitlePrep ?? 'للسيد' }} <strong>{{ $candidateName }}</strong> من جامعة <strong>{{ $masterUni }}</strong>، والمسبوقة بدرجة الإجازة في <strong>{{ $baSpec }}</strong> الممنوحة عام <strong>{{ $baYear }}</strong> من جامعة <strong>{{ $baUni }}</strong>، محققةً لشروط الشهادة والاختصاص من أجل تدريس الجوانب التطبيقية في اختصاص <strong>{{ $teachingDept }}</strong> بالجامعات الخاصة السورية.
        @else
            <strong>المادة -1</strong> الموافقة على تكليف {{ $candidateTitle ?? 'السيد' }} <strong>{{ $candidateName }}</strong>، {{ $qualifierHolderWord ?? 'الحائز' }} درجة الماجستير في <strong>{{ $masterGeneral }}</strong> اختصاص <strong>{{ $masterExact }}</strong> الممنوحة عام <strong>{{ $masterYear }}</strong> من جامعة <strong>{{ $masterUni }}</strong>، والمسبوقة بدرجة الإجازة في <strong>{{ $baGeneral }}</strong> قسم <strong>{{ $baSection }}</strong> الممنوحة عام <strong>{{ $baYear }}</strong> من جامعة <strong>{{ $baUni }}</strong>، بتدريس المقررات النظرية في اختصاص <strong>{{ $teachingDept }}</strong> في الجامعات الخاصة السورية على أن يكون تفرغه فيها كلياً، وألا يقل نصابه التدريسي عن /12/ ساعة أسبوعياً.
        @endif
    </div>

    <!-- ARTICLE 2 -->
    <div class="pdf-article">
        <strong>المادة -2</strong> يبلغ هذا القرار من يلزم لتنفيذه.
        <div style="margin-top: 10px; margin-right: 30px;">دمشق في {{ $decisionDate }}</div>
    </div>

@endif

    <!-- SIGNATURES TABLE -->
    <table class="pdf-signatures" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <!-- Left in DomPDF = Visual Left: Amin & Chairman -->
            <td style="width: 50%; text-align: right; vertical-align: top; padding-right: 25px; font-size: 13px; font-weight: bold; line-height: 1.6;">
                <!-- Top: Secretary General (Amin) -->
                <div>
                    <div>أمين مجلس التعليم العالي</div>
                    <div style="margin-top: 8px; font-size: 13.5px;">الدكتور علي الجاسم</div>
                </div>

                <!-- Bottom: Chairman / Vice Minister -->
                <div style="margin-top: 25px; line-height: 1.4;">
                    <div>رئيس لجنة التأهيل ومعادلة الدرجات العلمية</div>
                    <div style="margin-top: 2px;">معاون وزير التعليم العالي والبحث العلمي</div>
                    <div style="margin-top: 8px; font-size: 14px;">الدكتور عبد الحميد الخالد</div>
                </div>
            </td>

            <!-- Right in DomPDF = Visual Right: Director of Equivalence -->
            <td style="width: 50%; text-align: right; vertical-align: top; padding-right: 15px; font-size: 13px; font-weight: bold; line-height: 1.6;">
                <div>مدير التعادل والإنتاج العلمي</div>
                <div style="margin-top: 8px; font-size: 13.5px;">المهندس عمار هلال</div>
            </td>
        </tr>
    </table>

    <!-- COPIES TO -->
    <div class="pdf-copies">
        <div style="font-weight: bold; text-decoration: underline; margin-bottom: 3px;">صورة إلى:</div>
        @if(($docType ?? 'equivalence') === 'eligibility')
            <div>مكتب التعادل – الديوان</div>
            <div>أمانة سر المجلس (للتعميم على الجامعة المعنية عبر البريد الالكتروني)</div>
        @else
            <div>- مجلس التعليم العالي: مكتب التعادل – الديوان</div>
            <div>- أمانة سر المجلس (للتعميم على الجامعة المعنية عبر البريد الالكتروني)</div>
        @endif
    </div>

</body>
</html>
