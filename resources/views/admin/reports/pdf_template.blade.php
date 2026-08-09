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

* { 
    font-family: 'Segoe UI', 'DejaVu Sans', sans-serif; 
    box-sizing: border-box;
}

body { 
    direction: ltr; 
    text-align: right; 
    font-size: 12.5px; 
    color: #111C2C; 
    margin: 0; 
    padding: 15px 20px; 
    background: #ffffff; 
    line-height: 1.4;
}

/* TOP HEADER WITH EMBLEM & OFFICIAL TITLE */
.moz-header { 
    width: 100%; 
    border-bottom: 3px double #C5A059; 
    margin-bottom: 12px; 
    padding-bottom: 10px; 
}
.moz-header td { 
    vertical-align: middle; 
}
.header-txt { 
    text-align: right; 
}
.header-txt .ar-title { 
    font-size: 15px; 
    font-weight: bold; 
    color: #1A2A44; 
    margin-bottom: 2px;
}
.header-txt .en-title { 
    font-size: 9px; 
    color: #555555; 
    letter-spacing: 0.5px; 
    text-transform: uppercase;
}
.logo-box {
    text-align: left;
    width: 80px;
}
.emblem-ring {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    border: 2px solid #C5A059;
    padding: 2px;
    background-color: #ffffff;
    display: inline-block;
}
.emblem-ring img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
}

/* MOZHAKKARA TITLE */
.moz-title { 
    text-align: center; 
    font-size: 18px; 
    font-weight: bold; 
    color: #1A2A44; 
    margin: 8px 0 14px; 
}

/* SECTION HEADERS */
.sec { 
    background-color: #1A2A44; 
    color: #ffffff; 
    font-weight: bold; 
    font-size: 13px; 
    padding: 6px 12px; 
    margin: 14px 0 6px; 
    border-right: 4px solid #C5A059; 
    border-radius: 2px;
    text-align: right;
}

/* TABLES (LTR Container, RTL Visual) */
.mt { 
    width: 100%; 
    border-collapse: collapse; 
    margin-bottom: 6px; 
}
.mt td { 
    padding: 6px 10px; 
    font-size: 12.5px; 
    border: 1px solid #C5C6CE; 
    color: #111C2C; 
    vertical-align: middle;
    text-align: right;
}
.mt td.l { 
    background: #f5f3f5; 
    font-weight: bold; 
    color: #1A2A44; 
    white-space: nowrap; 
    width: 140px; 
    text-align: right;
}

/* CANDIDATE NAME BANNER */
.cname { 
    background: #f0f3ff; 
    border: 1px solid #C5C6CE; 
    border-right: 4px solid #C5A059; 
    padding: 8px 12px; 
    font-size: 14.5px; 
    font-weight: bold; 
    color: #1A2A44; 
    text-align: center; 
    margin: 6px 0; 
}

/* WORK ASSIGNMENT BLOCK */
.wblock { 
    background: #faf9fb; 
    border: 1px solid #C5C6CE; 
    padding: 8px 12px; 
    font-size: 12px; 
    margin-top: 6px; 
    color: #111C2C; 
    line-height: 1.5;
    text-align: right;
}
.wblock .note { 
    color: #555555; 
    font-size: 11px; 
    display: block; 
    margin-top: 3px; 
}

/* COURSES & DEGREE TABLES */
.ct { 
    width: 100%; 
    border-collapse: collapse; 
    font-size: 12px; 
    margin-top: 4px;
}
.ct th { 
    background: #1A2A44; 
    color: #ffffff; 
    padding: 6px 8px; 
    text-align: center; 
    font-weight: bold; 
    border: 1px solid #1A2A44;
}
.ct td { 
    border: 1px solid #C5C6CE; 
    padding: 5px 8px; 
    text-align: center; 
    color: #111C2C; 
}

/* DEGREE BLOCKS */
.dblock { 
    border: 1px solid #C5C6CE; 
    margin: 8px 0; 
}
.dblock-h { 
    background: #1A2A44; 
    color: #ffffff; 
    font-weight: bold; 
    font-size: 12.5px; 
    padding: 5px 12px; 
    border-bottom: 2px solid #C5A059; 
    text-align: right;
}

.dt { 
    width: 100%; 
    border-collapse: collapse; 
    font-size: 11px; 
}
.dt th { 
    background: #1A2A44; 
    color: #ffffff; 
    padding: 5px 6px; 
    text-align: center; 
    font-weight: bold; 
}
.dt td { 
    border: 1px solid #C5C6CE; 
    padding: 4px 6px; 
    text-align: center; 
    color: #111C2C; 
}

.subh { 
    background: #f0f3ff; 
    color: #1A2A44; 
    font-weight: bold; 
    font-size: 11.5px; 
    padding: 4px 10px; 
    border-bottom: 1px solid #C5C6CE; 
    text-align: right;
}

.ebox { 
    background: #faf9fb; 
    border: 1px solid #C5C6CE; 
    border-right: 4px solid #C5A059; 
    padding: 9px 14px; 
    margin-top: 8px; 
    font-size: 12px; 
    color: #111C2C; 
    text-align: right;
}
.ebox div { 
    margin-bottom: 4px; 
}
</style>
</head>
<body>

@php
    $logoFile = public_path('assets/logo.jpg');
    if (!file_exists($logoFile)) {
        $logoFile = public_path('images/mohe_logo.jpg');
    }
    $logoBase64 = '';
    if (file_exists($logoFile)) {
        $logoBase64 = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoFile));
    }
@endphp

<!-- TOP HEADER -->
<table class="moz-header">
    <tr>
        <td class="logo-box">
            @if($logoBase64)
                <div class="emblem-ring">
                    <img src="{{ $logoBase64 }}" alt="شعار الوزارة" />
                </div>
            @endif
        </td>
        <td class="header-txt">
            <div class="ar-title">الجمهورية العربية السورية</div>
            <div class="ar-title">وزارة التعليم العالي والبحث العلمي</div>
            <div class="en-title">MINISTRY OF HIGHER EDUCATION AND SCIENTIFIC RESEARCH</div>
        </td>
    </tr>
</table>

<!-- TITLE -->
<div class="moz-title">(مذكرة العرض)</div>

<!-- 1. البيانات الشخصية للمرشح -->
<div class="sec">البيانات الشخصية للمرشح :</div>

<table class="mt">
    <tr>
        <td style="font-weight: bold;">{{ $candidate->id }}</td>
        <td class="l">ID :</td>
        <td style="font-weight: bold; color: #1A2A44;">{{ $application->request_type ?? 'تعادل' }}</td>
        <td class="l">نوع الطلب :</td>
    </tr>
</table>

<div class="cname">اسم المرشح : {{ $candidate->full_name }}</div>

<table class="mt">
    <tr>
        <td>{{ $candidate->is_syrian ? 'سورية' : 'غير سورية' }}</td>
        <td class="l">الجنسية :</td>
        <td style="font-weight: bold;">{{ $candidate->national_id }}</td>
        <td class="l">الرقم الوطني :</td>
    </tr>
    <tr>
        <td>{{ $candidate->job_title }}</td>
        <td class="l">الوظيفة :</td>
        <td>{{ $candidate->dob }}</td>
        <td class="l">تاريخ الميلاد :</td>
    </tr>
    <tr>
        <td>{{ $candidate->mobile }}</td>
        <td class="l">رقم الجوال :</td>
        <td>{{ $candidate->phone }}</td>
        <td class="l">رقم الهاتف :</td>
    </tr>
    <tr>
        <td colspan="3" style="color: #1A2A44; font-weight: 600;">{{ $candidate->email }}</td>
        <td class="l">البريد الإلكتروني :</td>
    </tr>
    <tr>
        <td colspan="3">{{ $candidate->address }}</td>
        <td class="l">العنوان :</td>
    </tr>
</table>

<div class="wblock">
    <b>المرشح للعمل في قسم :</b> {{ $application->work_department ?? '---' }} &nbsp; | &nbsp;
    <b>في كلية :</b> {{ $application->work_faculty ?? '---' }} &nbsp; | &nbsp;
    <b>في جامعة :</b> {{ optional($application->workUniversity)->name ?? '---' }}
    <span class="note">التي تطلب الجامعة تكليفه بتدريسها استناداً إلى قرار معادلة شهادته العلمية.</span>
</div>

<!-- 2. المقررات -->
<div class="sec">المقررات التي يدرسها بموجب قرار لجنة التأهيل ومعادلة الدرجات العلمية :</div>
<table class="ct">
    <thead>
        <tr>
            <th>حالة المقرر</th>
            <th>الكلية</th>
            <th>القسم</th>
            <th>اسم المقرر</th>
        </tr>
    </thead>
    <tbody>
        @forelse($application->courses as $c)
        <tr>
            <td>{{ $c->course_status }}</td>
            <td>{{ $c->faculty }}</td>
            <td>{{ $c->department }}</td>
            <td style="font-weight: bold; color: #1A2A44;">{{ $c->course_name }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="4" style="color: #b91c1c; font-weight: bold; text-align: center;">لا توجد مقررات تطلبها الجامعة</td>
        </tr>
        @endforelse
    </tbody>
</table>

<!-- 3. الشهادات -->
<div class="sec">الشهادات التي يحملها المرشح :</div>

{{-- ثانوية --}}
<div class="dblock">
    <div class="dblock-h">الشهادة الثانوية :</div>
    <table class="mt">
        <tr>
            <td>{{ optional($highSchoolEd)->grant_date ?? '---' }}</td>
            <td class="l">تاريخ المنح :</td>
            <td>{{ optional($highSchoolEd)->section_name ?? '---' }}</td>
            <td class="l">القسم :</td>
            <td>{{ optional(optional($highSchoolEd)->country)->name ?? '---' }}</td>
            <td class="l">الدولة المانحة :</td>
        </tr>
    </table>
</div>

{{-- إجازة جامعية --}}
@if($bachelorEd)
<div class="dblock">
    <div class="dblock-h">شهادة الإجازة الجامعية :</div>
    <table class="mt">
        <tr>
            <td colspan="3">{{ optional($bachelorEd->university)->name ?? '---' }}</td>
            <td class="l">الجهة المانحة :</td>
            <td>{{ optional($bachelorEd->country)->name ?? '---' }}</td>
            <td class="l">الدولة المانحة :</td>
        </tr>
        <tr>
            <td>{{ $bachelorEd->rank ?? '---' }}</td>
            <td class="l">المرتبة :</td>
            <td>{{ $bachelorEd->exact_specialization ?? '---' }}</td>
            <td class="l">التخصص الدقيق :</td>
            <td>{{ $bachelorEd->general_specialization ?? '---' }}</td>
            <td class="l">التخصص العام :</td>
        </tr>
        <tr>
            <td colspan="3">{{ $bachelorEd->grant_date ?? '---' }}</td>
            <td class="l">تاريخ المنح :</td>
            <td>{{ $bachelorEd->registration_date ?? '---' }}</td>
            <td class="l">تاريخ التسجيل :</td>
        </tr>
    </table>
</div>
@endif

{{-- ماجستير --}}
@if($masterEd)
<div class="dblock">
    <div class="dblock-h">شهادة ماجستير {{ optional(optional($masterEd)->country)->name == 'سوريا' ? 'سوريا' : 'غير سورية' }} :</div>
    <table class="mt">
        <tr>
            <td colspan="3">{{ optional($masterEd->university)->name ?? '---' }}</td>
            <td class="l">الجهة المانحة :</td>
            <td>{{ optional($masterEd->country)->name ?? '---' }}</td>
            <td class="l">الدولة المانحة :</td>
        </tr>
        <tr>
            <td>{{ $masterEd->rank ?? '---' }}</td>
            <td class="l">المرتبة :</td>
            <td>{{ $masterEd->exact_specialization ?? '---' }}</td>
            <td class="l">التخصص الدقيق :</td>
            <td>{{ $masterEd->general_specialization ?? '---' }}</td>
            <td class="l">التخصص العام :</td>
        </tr>
        <tr>
            <td>{{ $masterEd->supervisor_name ?? '---' }}</td>
            <td class="l">اسم المشرف :</td>
            <td>{{ $masterEd->grant_date ?? '---' }}</td>
            <td class="l">تاريخ المنح :</td>
            <td>{{ $masterEd->registration_date ?? '---' }}</td>
            <td class="l">تاريخ التسجيل :</td>
        </tr>
        <tr>
            <td colspan="5">{{ $masterEd->thesis_title ?? '---' }}</td>
            <td class="l">عنوان الأطروحة :</td>
        </tr>
    </table>
    @if($masterEd->residences && $masterEd->residences->count() > 0)
    <div class="subh">مجموع الإقامة بمرحلة الماجستير :</div>
    <table class="dt">
        <thead>
            <tr>
                <th>سنة</th><th>شهر</th><th>يوم</th><th>رقم الصفحة</th><th>مطار الخروج</th><th>تاريخ الخروج</th><th>مطار الدخول</th><th>تاريخ الدخول</th>
            </tr>
        </thead>
        <tbody>
            @foreach($masterEd->residences as $r)
            <tr>
                <td>2</td><td>0</td><td>0</td><td>{{ $r->page_number }}</td><td>{{ $r->exit_airport }}</td><td>{{ $r->exit_date }}</td><td>{{ $r->entry_airport }}</td><td>{{ $r->entry_date }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
@endif

{{-- دكتوراه --}}
@if($phdEd)
<div class="dblock">
    <div class="dblock-h">شهادة دكتوراه {{ optional(optional($phdEd)->country)->name == 'سوريا' ? 'سوريا' : 'غير سورية' }} :</div>
    <table class="mt">
        <tr>
            <td colspan="3">{{ optional($phdEd->university)->name ?? '---' }}</td>
            <td class="l">الجهة المانحة :</td>
            <td>{{ optional($phdEd->country)->name ?? '---' }}</td>
            <td class="l">الدولة المانحة :</td>
        </tr>
        <tr>
            <td>{{ $phdEd->rank ?? '---' }}</td>
            <td class="l">المرتبة :</td>
            <td>{{ $phdEd->exact_specialization ?? '---' }}</td>
            <td class="l">التخصص الدقيق :</td>
            <td>{{ $phdEd->general_specialization ?? '---' }}</td>
            <td class="l">التخصص العام :</td>
        </tr>
        <tr>
            <td>{{ $phdEd->grant_date ?? '---' }}</td>
            <td class="l">تاريخ المنح :</td>
            <td>{{ $phdEd->defense_date ?? '---' }}</td>
            <td class="l">تاريخ المناقشة :</td>
            <td>{{ $phdEd->registration_date ?? '---' }}</td>
            <td class="l">تاريخ التسجيل :</td>
        </tr>
        <tr>
            <td>{{ $phdEd->supervisor_name ?? '---' }}</td>
            <td class="l">اسم المشرف :</td>
            <td colspan="3">{{ $phdEd->thesis_title ?? '---' }}</td>
            <td class="l">عنوان الأطروحة :</td>
        </tr>
        <tr>
            <td colspan="5">{{ $phdEd->notes ?? 'لا توجد' }}</td>
            <td class="l">معلومات أخرى :</td>
        </tr>
    </table>
    @if($phdEd->residences && $phdEd->residences->count() > 0)
    <div class="subh">تاريخ دخول بلد الدراسة :</div>
    <table class="dt">
        <thead>
            <tr>
                <th>سنة</th><th>شهر</th><th>يوم</th><th>رقم الصفحة</th><th>مطار الخروج</th><th>تاريخ الخروج</th><th>مطار الدخول</th><th>تاريخ الدخول</th>
            </tr>
        </thead>
        <tbody>
            @foreach($phdEd->residences as $r)
            <tr>
                <td>4</td><td>0</td><td>0</td><td>{{ $r->page_number }}</td><td>{{ $r->exit_airport }}</td><td>{{ $r->exit_date }}</td><td>{{ $r->entry_airport }}</td><td>{{ $r->entry_date }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
@endif

<!-- 4. معلومات إضافية -->
<div class="sec">معلومات إضافية :</div>
<div class="ebox">
    <div><b>هل المرشح جنسيته السورية :</b> {{ $candidate->is_syrian ? 'نعم' : 'لا' }}</div>
    <div><b>هل المرشح حاصل على مؤهل علمي قبل المؤهل الأخير :</b> {{ $application->has_previous_degree ? 'نعم' : 'لا' }}</div>
    <div><b>نظام دراسة المرشح :</b> {{ $application->study_system ?? '---' }}</div>
</div>

</body>
</html>