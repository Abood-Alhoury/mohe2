<!DOCTYPE html>
<html lang="ar" dir="rtl">
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
    vertical-align: middle;
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
    text-align: justify;
    line-height: 1.75;
}

.pdf-decree-header {
    text-align: center;
    font-size: 15px;
    font-weight: bold;
    margin: 12px 0 14px;
    text-decoration: underline;
}

.pdf-article {
    font-size: 13.5px;
    margin-bottom: 15px;
    text-align: justify;
    line-height: 1.95;
}

.pdf-signatures {
    width: 100%;
    margin-top: 25px;
    margin-bottom: 20px;
    page-break-inside: avoid;
}

.pdf-signatures td {
    text-align: center;
    vertical-align: top;
    font-size: 13px;
    font-weight: bold;
    line-height: 1.6;
}

.pdf-copies {
    font-size: 11.5px;
    line-height: 1.5;
    border-top: 1px solid #cccccc;
    padding-top: 10px;
    page-break-inside: avoid;
}
</style>
</head>
<body>

    <!-- TOP HEADER TABLE -->
    <table class="pdf-header" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <!-- Right: Arabic Header -->
            <td style="width: 40%; text-align: right; font-weight: bold; font-size: 13.5px; line-height: 1.45;">
                الجمهورية العربية السورية<br>
                مجلس التعليم العالي<br>
                لجنة التأهيل ومعادلة الدرجات العلمية
            </td>

            <!-- Center: Official Logo -->
            <td style="width: 20%; text-align: center; vertical-align: middle;">
                <img src="{{ public_path('assets/logo.jpg') }}" alt="شعار المجلس" style="width: 75px; height: 75px; object-fit: contain;">
            </td>

            <!-- Left: English Header -->
            <td style="width: 40%; text-align: left; font-weight: bold; font-size: 11.5px; line-height: 1.35; direction: ltr;">
                Syrian Arab Republic<br>
                council of Higher Education
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
            المادة 1- تعد درجة الماجستير في {{ $masterSpec }} الممنوحة عام {{ $masterYear }} للسيد {{ $candidateName }} من جامعة {{ $masterUni }}، والمسبوقة بدرجة الإجازة في {{ $baSpec }} الممنوحة عام {{ $baYear }} من جامعة {{ $baUni }}، محققةً لشروط الشهادة والاختصاص من أجل تدريس الجوانب التطبيقية في اختصاص {{ $teachingDept }} بالجامعات الخاصة السورية.
        @else
            المادة 1- الموافقة على تكليف السيد {{ $candidateName }}، الحائز درجة الماجستير في {{ $masterSpec }} الممنوحة عام {{ $masterYear }} من جامعة {{ $masterUni }}، والمسبوقة بدرجة الإجازة في {{ $baSpec }} الممنوحة عام {{ $baYear }} من جامعة {{ $baUni }}، بتدريس المقررات النظرية في اختصاص {{ $teachingDept }} في الجامعات الخاصة السورية على أن يكون تفرغه فيها كلياً، وألا يقل نصابه التدريسي عن /12/ ساعة أسبوعياً.
        @endif
    </div>

    <!-- ARTICLE 2 -->
    <div class="pdf-article">
        المادة 2- يبلغ هذا القرار من يلزم لتنفيذه.
    </div>

    <!-- SIGNATURES TABLE -->
    <table class="pdf-signatures" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <!-- Right: Director of Equivalence -->
            <td style="width: 50%; text-align: center; vertical-align: top;">
                مدير التعادل والإنتاج العلمي<br><br>
                <span style="font-size: 13.5px;">المهندس عمار هلال</span>
            </td>

            <!-- Left: Secretary General -->
            <td style="width: 50%; text-align: center; vertical-align: top;">
                أمين مجلس التعليم العالي<br><br>
                <span style="font-size: 13.5px;">الدكتور علي الجاسم</span>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: left; vertical-align: top; padding-top: 18px; font-size: 13px; font-weight: bold; line-height: 1.35;">
                <div>رئيس لجنة التأهيل ومعادلة الدرجات العلمية</div>
                <div style="margin-top: 2px;">معاون وزير التعليم العالي والبحث العلمي</div>
                <div style="margin-top: 6px; font-size: 14px;">الدكتور عبد الحميد الخالد</div>
            </td>
        </tr>
    </table>

    <!-- COPIES TO -->
    <div class="pdf-copies">
        <div style="font-weight: bold; text-decoration: underline; margin-bottom: 3px;">صورة إلى:</div>
        <div>- مجلس التعليم العالي: مكتب التعادل – الديوان</div>
        <div>- أمانة سر المجلس (للتعميم على الجامعة المعنية عبر البريد الالكتروني)</div>
    </div>

</body>
</html>
