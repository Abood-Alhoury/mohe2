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
</style>
</head>
<body>

<table class="moz-header">
<tr>
    <td class="txt-td">
        <div class="ar">الجمهورية العربية السورية</div>
        <div class="ar">وزارة التعليم العالي والبحث العلمي</div>
        <div class="en">MINISTRY OF HIGHER EDUCATION AND SCIENTIFIC RESEARCH</div>
    </td>
    <td class="logo-td">
        <img src="{{ str_replace('\\','/',public_path('logo.jpg')) }}" width="70" height="70" alt="logo"/>
    </td>
</tr>
</table>

<div class="moz-title">(مذكرة العرض)</div>

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

<div class="wblock">
    <b>المرشح للعمل في قسم :</b> {{ $application->work_department ?? '---' }}
    &nbsp; | &nbsp;
    <b>في كلية :</b> {{ $application->work_faculty ?? '---' }}
    &nbsp; | &nbsp;
    <b>في جامعة :</b> {{ optional($application->workUniversity)->name ?? '---' }}
    <span class="note">التي تطلب الجامعة تكليفه بتدريسها استنادا الى قرار معادلة شهادته العلمية.</span>
</div>

<div class="sec">المقررات التي يدرسها بموجب قرار لجنة التأهيل ومعادلة الدرجات العلمية :</div>
<table class="ct">
    <thead>
        <tr>
            <th>اسم المقرر</th><th>القسم</th><th>الكلية</th><th>حالة المقرر</th>
        </tr>
    </thead>
    <tbody>
        @forelse($application->courses as $c)
        <tr>
            <td style="font-weight:bold;color:#1A2A44;">{{ $c->course_name }}</td>
            <td>{{ $c->department }}</td>
            <td>{{ $c->faculty }}</td>
            <td>{{ $c->course_status }}</td>
        </tr>
        @empty
        <tr><td colspan="4" style="color:#b91c1c;font-weight:bold;text-align:center;">لا توجد مقررات</td></tr>
        @endforelse
    </tbody>
</table>

<div class="sec">الشهادات التي يحملها المرشح :</div>

<div class="dblock">
    <div class="dblock-h">الشهادة الثانوية :</div>
    <table class="mt">
        <tr>
            <td class="l">الدولة المانحة :</td><td>{{ optional(optional($highSchoolEd)->country)->name ?? '---' }}</td>
            <td class="l">القسم :</td><td>{{ optional($highSchoolEd)->section_name ?? '---' }}</td>
            <td class="l">تاريخ المنح :</td><td>{{ optional($highSchoolEd)->grant_date ?? '---' }}</td>
        </tr>
    </table>
</div>

@if($bachelorEd)
<div class="dblock">
    <div class="dblock-h">شهادة الاجازة الجامعية :</div>
    <table class="mt">
        <tr>
            <td class="l">الدولة المانحة :</td><td>{{ optional($bachelorEd->country)->name ?? '---' }}</td>
            <td class="l">الجهة المانحة :</td><td colspan="3">{{ optional($bachelorEd->university)->name ?? '---' }}</td>
        </tr>
        <tr>
            <td class="l">التخصص العام :</td><td>{{ $bachelorEd->general_specialization ?? '---' }}</td>
            <td class="l">التخصص الدقيق :</td><td>{{ $bachelorEd->exact_specialization ?? '---' }}</td>
            <td class="l">المرتبة :</td><td>{{ $bachelorEd->rank ?? '---' }}</td>
        </tr>
        <tr>
            <td class="l">تاريخ التسجيل :</td><td>{{ $bachelorEd->registration_date ?? '---' }}</td>
            <td class="l">تاريخ المنح :</td><td colspan="3">{{ $bachelorEd->grant_date ?? '---' }}</td>
        </tr>
    </table>
</div>
@endif

@if($masterEd)
<div class="dblock">
    <div class="dblock-h">شهادة ماجستير {{ optional(optional($masterEd)->country)->name == 'سوريا' ? 'سوريا' : 'غير سورية' }} :</div>
    <table class="mt">
        <tr>
            <td class="l">الدولة المانحة :</td><td>{{ optional($masterEd->country)->name ?? '---' }}</td>
            <td class="l">الجهة المانحة :</td><td colspan="3">{{ optional($masterEd->university)->name ?? '---' }}</td>
        </tr>
        <tr>
            <td class="l">التخصص العام :</td><td>{{ $masterEd->general_specialization ?? '---' }}</td>
            <td class="l">التخصص الدقيق :</td><td>{{ $masterEd->exact_specialization ?? '---' }}</td>
            <td class="l">المرتبة :</td><td>{{ $masterEd->rank ?? '---' }}</td>
        </tr>
        <tr>
            <td class="l">تاريخ التسجيل :</td><td>{{ $masterEd->registration_date ?? '---' }}</td>
            <td class="l">تاريخ المنح :</td><td>{{ $masterEd->grant_date ?? '---' }}</td>
            <td class="l">اسم المشرف :</td><td>{{ $masterEd->supervisor_name ?? '---' }}</td>
        </tr>
        <tr>
            <td class="l">عنوان الاطروحة :</td><td colspan="5">{{ $masterEd->thesis_title ?? '---' }}</td>
        </tr>
    </table>
    @if($masterEd->residences && $masterEd->residences->count() > 0)
    <div class="subh">مجموع الاقامة بمرحلة الماجستير :</div>
    <table class="dt">
        <thead><tr><th>تاريخ الدخول</th><th>مطار الدخول</th><th>تاريخ الخروج</th><th>مطار الخروج</th><th>رقم الصفحة</th><th>يوم</th><th>شهر</th><th>سنة</th></tr></thead>
        <tbody>
            @foreach($masterEd->residences as $r)
            <tr><td>{{ $r->entry_date }}</td><td>{{ $r->entry_airport }}</td><td>{{ $r->exit_date }}</td><td>{{ $r->exit_airport }}</td><td>{{ $r->page_number }}</td><td>0</td><td>0</td><td>2</td></tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
@endif

@if($phdEd)
<div class="dblock">
    <div class="dblock-h">شهادة دكتوراه {{ optional(optional($phdEd)->country)->name == 'سوريا' ? 'سوريا' : 'غير سورية' }} :</div>
    <table class="mt">
        <tr>
            <td class="l">الدولة المانحة :</td><td>{{ optional($phdEd->country)->name ?? '---' }}</td>
            <td class="l">الجهة المانحة :</td><td colspan="3">{{ optional($phdEd->university)->name ?? '---' }}</td>
        </tr>
        <tr>
            <td class="l">التخصص العام :</td><td>{{ $phdEd->general_specialization ?? '---' }}</td>
            <td class="l">التخصص الدقيق :</td><td>{{ $phdEd->exact_specialization ?? '---' }}</td>
            <td class="l">المرتبة :</td><td>{{ $phdEd->rank ?? '---' }}</td>
        </tr>
        <tr>
            <td class="l">تاريخ التسجيل :</td><td>{{ $phdEd->registration_date ?? '---' }}</td>
            <td class="l">تاريخ المناقشة :</td><td>{{ $phdEd->defense_date ?? '---' }}</td>
            <td class="l">تاريخ المنح :</td><td>{{ $phdEd->grant_date ?? '---' }}</td>
        </tr>
        <tr>
            <td class="l">اسم المشرف :</td><td>{{ $phdEd->supervisor_name ?? '---' }}</td>
            <td class="l">عنوان الاطروحة :</td><td colspan="3">{{ $phdEd->thesis_title ?? '---' }}</td>
        </tr>
        <tr>
            <td class="l">معلومات اخرى :</td><td colspan="5">{{ $phdEd->notes ?? 'لا توجد' }}</td>
        </tr>
    </table>
    @if($phdEd->residences && $phdEd->residences->count() > 0)
    <div class="subh">تاريخ دخول بلد الدراسة :</div>
    <table class="dt">
        <thead><tr><th>تاريخ الدخول</th><th>مطار الدخول</th><th>تاريخ الخروج</th><th>مطار الخروج</th><th>رقم الصفحة</th><th>يوم</th><th>شهر</th><th>سنة</th></tr></thead>
        <tbody>
            @foreach($phdEd->residences as $r)
            <tr><td>{{ $r->entry_date }}</td><td>{{ $r->entry_airport }}</td><td>{{ $r->exit_date }}</td><td>{{ $r->exit_airport }}</td><td>{{ $r->page_number }}</td><td>0</td><td>0</td><td>4</td></tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
@endif

<div class="sec">معلومات اضافية :</div>
<div class="ebox">
    <div><b>هل المرشح جنسيته السورية :</b> {{ $candidate->is_syrian ? 'نعم' : 'لا' }}</div>
    <div><b>هل المرشح حاصل على مؤهل علمي قبل المؤهل الاخير :</b> {{ $application->has_previous_degree ? 'نعم' : 'لا' }}</div>
    <div><b>نظام دراسة المرشح :</b> {{ $application->study_system ?? '---' }}</div>
</div>

</body>
</html>