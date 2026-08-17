<!DOCTYPE html>
<html lang="ar" dir="ltr">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<style>
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
* { font-family: 'Segoe UI', 'DejaVu Sans', sans-serif; }
body { direction: ltr; text-align: right; font-size: 13px; color: #111C2C; margin: 0; padding: 20px; background: #fff; }
.moz-header { width: 100%; border-bottom: 3px double #C5A059; margin-bottom: 10px; padding-bottom: 10px; }
.moz-header td { vertical-align: middle; }
.logo-td { width: 80px; text-align: left; }
.txt-td { text-align: right; }
.txt-td .ar { font-size: 15px; font-weight: bold; color: #1A2A44; }
.txt-td .en { font-size: 9px; color: #555; text-transform: uppercase; }
.moz-title { text-align: center; font-size: 18px; font-weight: bold; color: #1A2A44; margin: 10px 0 14px; }
.sec { background-color: #1A2A44; color: #fff; font-weight: bold; font-size: 13px; padding: 5px 12px; margin: 14px 0 0; border-right: 4px solid #C5A059; }
.mt { width: 100%; border-collapse: collapse; }
.mt td { padding: 6px 10px; font-size: 12.5px; border: 1px solid #dde; color: #111C2C; }
.mt td.l { background: #f5f3f5; font-weight: bold; color: #1A2A44; white-space: nowrap; width: 145px; }
.cname { background: #f0f3ff; border: 1px solid #dde; border-right: 4px solid #C5A059; padding: 7px 12px; font-size: 14px; font-weight: bold; color: #1A2A44; text-align: center; margin: 5px 0; }
.wblock { background: #faf9fb; border: 1px solid #dde; padding: 8px 12px; font-size: 12.5px; margin-top: 5px; color: #111C2C; }
.wblock .note { color: #555; font-size: 11px; display: block; margin-top: 3px; }
.dblock { border: 1px solid #dde; margin: 8px 0; }
.dblock-h { background: #1A2A44; color: #fff; font-weight: bold; font-size: 12.5px; padding: 5px 12px; border-bottom: 2px solid #C5A059; }
.ct { width: 100%; border-collapse: collapse; font-size: 12px; }
.ct th { background: #1A2A44; color: #fff; padding: 6px 8px; text-align: center; font-weight: bold; }
.ct td { border: 1px solid #dde; padding: 5px 8px; text-align: center; color: #111C2C; }
.dt { width: 100%; border-collapse: collapse; font-size: 11.5px; }
.dt th { background: #1A2A44; color: #fff; padding: 5px 7px; text-align: center; font-weight: bold; }
.dt td { border: 1px solid #dde; padding: 4px 7px; text-align: center; color: #111C2C; }
.subh { background: #f0f3ff; color: #1A2A44; font-weight: bold; font-size: 12px; padding: 4px 10px; border-bottom: 1px solid #dde; }
.ebox { background: #faf9fb; border: 1px solid #dde; border-right: 4px solid #C5A059; padding: 9px 14px; margin-top: 8px; font-size: 12.5px; color: #111C2C; }
.ebox div { margin-bottom: 4px; }
.doc-card { border: 2px solid #1A2A44; background: #faf9fb; padding: 20px; text-align: center; margin-top: 25px; border-radius: 4px; }
</style>
</head>
<body>

<!-- PAGE 1: MOZHAKKARA SUMMARY REPORT -->
<table class="moz-header">
<tr>
    <td class="txt-td">
        <div class="ar">الجمهورية العربية السورية</div>
        <div class="ar">وزارة التعليم العالي والبحث العلمي</div>
        <div class="en">MINISTRY OF HIGHER EDUCATION AND SCIENTIFIC RESEARCH</div>
    </td>
    <td class="logo-td">
        <img src="{{ str_replace('\\','/',public_path('assets/report_logo.png')) }}" width="75" height="75" alt="logo" style="object-fit: contain;"/>
    </td>
</tr>
</table>

@php
    $isFacultyPermission = str_contains($application->request_type ?? '', 'سماح') || str_contains($application->request_type ?? '', 'تدريسية');
    $govEd = $govEd ?? ($application->educations ? $application->educations->first(function($e) {
        return $e->thesis_title === 'عضو هيئة تدريسية في جامعة حكومية' || (optional($e->level)->name && str_contains(optional($e->level)->name, 'حكومية'));
    }) : null);
    $phdEd = $phdEd ?? ($application->educations ? $application->educations->first(function($e) {
        return $e->thesis_title === 'شهادة الدكتوراه' || (optional($e->level)->name == 'دكتوراه' && $e->thesis_title !== 'عضو هيئة تدريسية في جامعة حكومية');
    }) : null);
@endphp

@if($isFacultyPermission)
    <div class="moz-title">(مذكرة العرض المدمجة - سماح بالتدريس)</div>

    <div class="sec">البيانات الشخصية للمرشح :</div>
    <table class="mt">
        <tr>
            <td class="l">نوع المعاملة :</td>
            <td style="font-weight:bold;color:#1A2A44;">{{ $application->request_type ?? 'عضو هيئة تدريسية - سماح بالتدريس' }}</td>
            <td class="l">رقم المعاملة :</td>
            <td style="font-weight:bold;">{{ $application->application_no ?? $candidate->id }}</td>
        </tr>
    </table>
    <div class="cname">اسم المرشح : {{ $candidate->full_name }}</div>
    <table class="mt">
        <tr>
            <td class="l">الرقم الوطني :</td><td style="font-weight:bold;">{{ $candidate->national_id }}</td>
            <td class="l">الجنسية :</td><td>{{ $candidate->is_syrian ? 'سورية' : (optional($candidate->nationality)->name ?? 'سورية') }}</td>
        </tr>
        <tr>
            <td class="l">تاريخ الميلاد :</td><td>{{ $candidate->dob }}</td>
            <td class="l">الصفة الأكاديمية :</td><td>{{ optional($govEd)->rank ?? $candidate->job_title ?? 'عضو هيئة تدريسية' }}</td>
        </tr>
        <tr>
            <td class="l">رقم الهاتف :</td><td>{{ $candidate->phone ?? '---' }}</td>
            <td class="l">رقم الجوال :</td><td>{{ $candidate->mobile }}</td>
        </tr>
        <tr>
            <td class="l">البريد الالكتروني :</td>
            <td colspan="3" style="color:#1A2A44;font-weight:600;">{{ $candidate->email }}</td>
        </tr>
        <tr>
            <td class="l">العنوان :</td>
            <td colspan="3">{{ $candidate->address }}</td>
        </tr>
    </table>

    <div class="sec">بيانات كتاب طلب التقييم الصادر عن الجامعة :</div>
    <table class="mt">
        <tr>
            <td class="l">الجامعة الطالبة :</td>
            <td style="font-weight:bold;color:#1A2A44;">{{ optional($application->workUniversity)->name ?? '---' }}</td>
            <td class="l">رقم وتاريخ الكتاب :</td>
            <td><strong>رقم:</strong> {{ $application->new_uni_request_no ?? '---' }} | <strong>بتاريخ:</strong> {{ $application->new_uni_request_date ?? '---' }}</td>
        </tr>
    </table>

    <div class="sec">بيانات التعيين والصفة بالجامعة الحكومية السورية :</div>
    <table class="mt">
        <tr>
            <td class="l">الجامعة الحكومية :</td>
            <td style="font-weight:bold;color:#1A2A44;">{{ optional($govEd->university)->name ?? optional($govEd)->university_other ?? '---' }}</td>
            <td class="l">الرتبة الأكاديمية :</td>
            <td style="font-weight:bold;">{{ optional($govEd)->rank ?? 'مدرس' }}</td>
        </tr>
        <tr>
            <td class="l">الكلية التابع لها :</td>
            <td>{{ optional($govEd)->faculty ?: optional($govEd)->general_specialization ?: '---' }}</td>
            <td class="l">القسم التابع له :</td>
            <td>{{ optional($govEd)->department ?: optional($govEd)->exact_specialization ?: '---' }}</td>
        </tr>
    </table>

    <div class="sec">بيانات شهادة الدكتوراه (المؤهل العلمي الأساسي) :</div>
    <table class="mt">
        <tr>
            <td class="l">الجامعة المانحة :</td>
            <td style="font-weight:bold;color:#1A2A44;">{{ optional($phdEd->university)->name ?? optional($phdEd)->university_other ?? '---' }}</td>
            <td class="l">تاريخ / سنة المنح :</td>
            <td>{{ optional($phdEd)->grant_date ?? '---' }}</td>
        </tr>
        <tr>
            <td class="l">الكلية المانحة :</td>
            <td>{{ optional($phdEd)->faculty ?: optional($phdEd)->general_specialization ?: '---' }}</td>
            <td class="l">القسم / الاختصاص :</td>
            <td>{{ optional($phdEd)->department ?: optional($phdEd)->exact_specialization ?: '---' }}</td>
        </tr>
    </table>

@else
    <div class="moz-title">(مذكرة العرض المدمجة)</div>

    <div class="sec">البيانات الشخصية للمرشح :</div>
    <table class="mt">
        <tr>
            <td class="l">نوع الطلب :</td>
            <td style="font-weight:bold;color:#1A2A44;">{{ $application->request_type ?? 'تعادل' }}</td>
            <td class="l">ID :</td>
            <td style="font-weight:bold;">{{ $candidate->id }}</td>
        </tr>
    </table>
    <div class="cname">اسم المرشح : {{ $candidate->full_name }}</div>
    <table class="mt">
        <tr>
            <td class="l">الرقم الوطني :</td><td style="font-weight:bold;">{{ $candidate->national_id }}</td>
            <td class="l">الجنسية :</td><td>{{ $candidate->is_syrian ? 'سورية' : 'غير سورية' }}</td>
        </tr>
        <tr>
            <td class="l">تاريخ الميلاد :</td><td>{{ $candidate->dob }}</td>
            <td class="l">الوظيفة :</td><td>{{ $candidate->job_title }}</td>
        </tr>
        <tr>
            <td class="l">رقم الهاتف :</td><td>{{ $candidate->phone }}</td>
            <td class="l">رقم الجوال :</td><td>{{ $candidate->mobile }}</td>
        </tr>
        <tr>
            <td class="l">البريد الالكتروني :</td>
            <td colspan="3" style="color:#1A2A44;font-weight:600;">{{ $candidate->email }}</td>
        </tr>
        <tr>
            <td class="l">العنوان :</td>
            <td colspan="3">{{ $candidate->address }}</td>
        </tr>
    </table>

    <div class="wblock" style="background-color: #f8fafc; border: 1px solid #cbd5e1; padding: 8px 12px; border-radius: 4px; margin-bottom: 12px;">
        <table style="width: 100%; border-collapse: collapse; border: none; font-size: 13px; direction: rtl;">
            <tr>
                <td style="width: 34%; border: none; padding: 2px; text-align: right;">
                    <b>المرشح للعمل في قسم:</b> {{ $application->work_department ?? '---' }}
                </td>
                <td style="width: 33%; border: none; padding: 2px; text-align: right;">
                    <b>في كلية:</b> {{ $application->work_faculty ?? '---' }}
                </td>
                <td style="width: 33%; border: none; padding: 2px; text-align: right;">
                    <b>في جامعة:</b> {{ optional($application->workUniversity)->name ?? '---' }}
                </td>
            </tr>
        </table>
        <div style="color: #64748B; font-size: 11px; margin-top: 4px; text-align: right;">
            التي تطلب الجامعة تكليفه بتدريسها استناداً إلى قرار معادلة شهادته العلمية.
        </div>
    </div>


    <div class="sec">الشهادات التي يحملها المرشح :</div>

    <div class="dblock">
        <div class="dblock-h">الشهادة الثانوية :</div>
        <table class="mt">
            <tr>
                <td class="l">الدولة المانحة :</td><td>{{ optional(optional($highSchoolEd)->country)->name ?? 'سوريا' }}</td>
                <td class="l">نوع الشهادة :</td><td>{{ optional($highSchoolEd)->section_name ?: (optional($highSchoolEd)->general_specialization ?: 'علمي') }}</td>
                <td class="l">تاريخ / سنة المنح :</td><td>{{ optional($highSchoolEd)->grant_date ?? '---' }}</td>
            </tr>
        </table>
    </div>

    @if($bachelorEd)
    <div class="dblock">
        <div class="dblock-h">شهادة الاجازة الجامعية :</div>
        <table class="mt">
            <tr>
                <td class="l">الدولة المانحة :</td><td>{{ optional($bachelorEd->country)->name ?? '---' }}</td>
                <td class="l">الجهة المانحة :</td><td colspan="3">{{ optional($bachelorEd->university)->name ?? ($bachelorEd->university_other ?? '---') }}</td>
            </tr>
            <tr>
                <td class="l">الكلية / التخصص العام :</td><td>{{ $bachelorEd->general_specialization ?: ($bachelorEd->faculty ?: '---') }}</td>
                <td class="l">القسم / التخصص الدقيق :</td><td>{{ $bachelorEd->exact_specialization ?: ($bachelorEd->department ?: ($bachelorEd->section_name ?: '---')) }}</td>
                <td class="l">المرتبة / المعدل :</td><td>{{ $bachelorEd->rank ?? '---' }}</td>
            </tr>
            <tr>
                <td class="l">تاريخ التسجيل :</td><td>{{ format_sys_date($bachelorEd->registration_date) }}</td>
                <td class="l">تاريخ المنح :</td><td colspan="3">{{ format_sys_date($bachelorEd->grant_date) }}</td>
            </tr>
        </table>
    </div>
    @endif

    @if($masterEd)
    <div class="dblock">
        <div class="dblock-h">درجة الماجستير :</div>
        <table class="mt">
            <tr>
                <td class="l">الجامعة المانحة :</td><td colspan="5">{{ optional($masterEd->university)->name ?? ($masterEd->university_other ?? '---') }}</td>
            </tr>
            <tr>
                <td class="l">الكلية / التخصص العام :</td><td>{{ $masterEd->general_specialization ?: ($masterEd->faculty ?: '---') }}</td>
                <td class="l">القسم / التخصص الدقيق :</td><td>{{ $masterEd->exact_specialization ?: ($masterEd->department ?: ($masterEd->section_name ?: '---')) }}</td>
                <td class="l">المرتبة / التقدير :</td><td>{{ $masterEd->rank ?? '---' }}</td>
            </tr>
            <tr>
                <td class="l">تاريخ التسجيل :</td><td>{{ format_sys_date($masterEd->registration_date) }}</td>
                <td class="l">تاريخ المناقشة :</td><td>{{ format_sys_date($masterEd->defense_date) }}</td>
                <td class="l">تاريخ المنح :</td><td>{{ format_sys_date($masterEd->grant_date) }}</td>
            </tr>
            <tr>
                <td class="l">عنوان الرسالة :</td><td colspan="5" style="font-weight:bold;color:#1A2A44;">{{ $masterEd->thesis_title ?? '---' }}</td>
            </tr>
        </table>
    </div>
    @endif

    @if($phdEd)
    <div class="dblock">
        <div class="dblock-h">درجة الدكتوراه :</div>
        <table class="mt">
            <tr>
                <td class="l">الجامعة المانحة :</td><td colspan="5">{{ optional($phdEd->university)->name ?? ($phdEd->university_other ?? '---') }}</td>
            </tr>
            <tr>
                <td class="l">الكلية / التخصص العام :</td><td>{{ $phdEd->general_specialization ?: ($phdEd->faculty ?: '---') }}</td>
                <td class="l">القسم / التخصص الدقيق :</td><td>{{ $phdEd->exact_specialization ?: ($phdEd->department ?: ($phdEd->section_name ?: '---')) }}</td>
                <td class="l">المرتبة / التقدير :</td><td>{{ $phdEd->rank ?? '---' }}</td>
            </tr>
            <tr>
                <td class="l">تاريخ المنح :</td><td colspan="5">{{ format_sys_date($phdEd->grant_date) }}</td>
            </tr>
        </table>
    </div>
    @endif

    <div class="sec">معلومات اضافية :</div>
    <div class="ebox">
        <div><b>هل المرشح جنسيته السورية :</b> {{ $candidate->is_syrian ? 'نعم' : 'لا' }}</div>
        <div><b>هل المرشح حاصل على مؤهل علمي قبل المؤهل الاخير :</b> {{ $application->has_previous_degree ? 'نعم' : 'لا' }}</div>
        <div><b>نظام دراسة المرشح :</b> {{ $application->study_system ?? '---' }}</div>
    </div>
@endif

<!-- PAGES 2+: CONSOLIDATED ATTACHMENTS (IN EXACT ORDER: HS -> BA -> MA -> OTHERS) -->
@php
    $allAttachments = collect();
    foreach($application->educations as $ed) {
        foreach($ed->attachments as $att) {
            $allAttachments->push($att);
        }
    }
@endphp

@foreach($allAttachments as $index => $attachment)
<div style="page-break-before: always;"></div>

<table class="moz-header">
<tr>
    <td class="txt-td">
        <div class="ar">الجمهورية العربية السورية</div>
        <div class="ar">وزارة التعليم العالي والبحث العلمي - حزمة المرفقات المدمجة</div>
        <div class="en">MINISTRY OF HIGHER EDUCATION - CONSOLIDATED ATTACHMENTS</div>
    </td>
    <td class="logo-td">
        <img src="{{ str_replace('\\','/',public_path('assets/report_logo.png')) }}" width="75" height="75" alt="logo" style="object-fit: contain;"/>
    </td>
</tr>
</table>

<div class="sec">المرفق رقم ({{ $index + 2 }}): {{ $attachment->notes ?: ($attachment->attachmentType->name ?? 'وثيقة ومرفق رسمي') }}</div>
<div class="cname">المرشح: {{ $candidate->full_name }} | الرقم الوطني: {{ $candidate->national_id }} | طلب رقم: #{{ $application->application_no }}</div>

@php
    $fullPath = storage_path('app/public/' . $attachment->file_path);
    $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
    $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'bmp']);
    $base64 = '';
    if ($isImage && file_exists($fullPath)) {
        $imgData = file_get_contents($fullPath);
        $base64 = 'data:image/' . $ext . ';base64,' . base64_encode($imgData);
    }
@endphp

@if($isImage && !empty($base64))
    <div style="text-align: center; margin-top: 20px;">
        <img src="{{ $base64 }}" style="max-width: 100%; max-height: 800px; border: 2px solid #C5A059; border-radius: 4px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);" />
    </div>
@else
    <div class="doc-card">
        <h3 style="color: #1A2A44; margin-bottom: 10px;">📄 وثيقة ومرفق رسمي مدمج (ملف PDF)</h3>
        <p style="font-size: 14px; font-weight: bold; color: #775A19; margin-bottom: 15px;">
            {{ $attachment->notes ?: ($attachment->attachmentType->name ?? 'مستند مرفق') }}
        </p>
        <table class="mt" style="max-width: 600px; margin: 0 auto; text-align: right;">
            <tr>
                <td class="l">نوع المستند:</td>
                <td>{{ $attachment->notes ?: ($attachment->attachmentType->name ?? 'وثيقة رسمية') }}</td>
            </tr>
            <tr>
                <td class="l">مسار الملف المرفق:</td>
                <td>{{ basename($attachment->file_path) }}</td>
            </tr>
            <tr>
                <td class="l">حالة التوثيق:</td>
                <td style="color: #137333; font-weight: bold;">مرفق مدمج ومصادق عليه ضمن الملف النهائي</td>
            </tr>
        </table>
        <p style="color: #555; font-size: 11px; margin-top: 20px;">
            تم دمج هذه الوثيقة إلكترونياً ضمن الحزمة الكاملة لطلب التعادل رقم #{{ $application->application_no }} الموجهة لمجلس التعليم العالي.
        </p>
    </div>
@endif

@endforeach

</body>
</html>
