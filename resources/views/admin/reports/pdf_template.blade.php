<!DOCTYPE html>
<html lang="ar" dir="rtl">
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
    direction: rtl; 
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
@if($application->request_type == 'تحويل قرار المعادلة')
    <div class="moz-title" style="color: #1E40AF;">(مذكرة عرض - تحويل قرار المعادلة ونقل التكليف)</div>
@elseif($application->request_type == 'إضافة مقررات دراسية')
    <div class="moz-title" style="color: #059669;">(مذكرة عرض - إضافة مقررات دراسية جديدة)</div>
@else
    <div class="moz-title">(مذكرة العرض)</div>
@endif

<!-- 1. البيانات الشخصية للمرشح -->
<div class="sec">البيانات الشخصية للمرشح :</div>

<table class="mt">
    <tr>
        <td style="font-weight: bold;">{{ $candidate->id }}</td>
        <td class="l">ID :</td>
        <td style="font-weight: bold; color: #1A2A44;">
            @if($application->request_type == 'تحويل قرار المعادلة')
                <span style="color: #1E40AF; font-weight: bold;">تحويل قرار المعادلة</span>
            @elseif($application->request_type == 'إضافة مقررات دراسية')
                <span style="color: #059669; font-weight: bold;">إضافة مقررات دراسية</span>
            @else
                {{ $application->request_type ?? 'تعادل' }}
            @endif
        </td>
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

@if($application->request_type == 'تحويل قرار المعادلة')
@php
    $parentApp = $application->parentApplication;
    $parentDecision = $parentApp ? $parentApp->latestDecision : null;
@endphp
<!-- TRANSFER EQUIVALENCE COMPARISON BLOCK FOR PDF -->
<div style="margin: 10px 0; border: 1.5px dashed #3B82F6; padding: 8px; background: #F0F9FF;">
    <div style="font-weight: bold; color: #1E40AF; font-size: 12px; margin-bottom: 6px; border-bottom: 1px solid #BAE6FD; padding-bottom: 3px; text-align: right;">
        تفاصيل تحويل قرار المعادلة ونقل التكليف بين الجامعات:
    </div>
    <table style="width: 100%; border-collapse: collapse; border: none; font-size: 11.5px;">
        <tr>
            <!-- Previous University -->
            <td style="width: 50%; vertical-align: top; padding: 4px; border: 1px solid #CBD5E1; background: #FFFFFF;">
                <div style="font-weight: bold; color: #991B1B; font-size: 11.5px; margin-bottom: 4px;">
                    1. جهة التكليف والقرار السابق (المعادلة الأولى):
                </div>
                <div><b>الجامعة السابقة:</b> {{ optional(optional($parentApp)->workUniversity)->name ?? '---' }}</div>
                <div><b>الكلية والقسم:</b> {{ optional($parentApp)->work_faculty ?? '---' }} / {{ optional($parentApp)->work_department ?? '---' }}</div>
                <div style="color: #B91C1C; font-weight: bold;">
                    <b>قرار المعادلة السابق:</b> {{ $parentDecision ? 'رقم (' . $parentDecision->decision_no . ') بتاريخ ' . $parentDecision->decision_date : 'تم الصدور أصولاً' }}
                </div>
            </td>

            <!-- New University -->
            <td style="width: 50%; vertical-align: top; padding: 4px; border: 1px solid #CBD5E1; background: #FFFFFF;">
                <div style="font-weight: bold; color: #065F46; font-size: 11.5px; margin-bottom: 4px;">
                    2. جهة التكليف الجديدة والمراد النقل إليها:
                </div>
                <div><b>الجامعة الجديدة:</b> {{ optional($application->workUniversity)->name ?? '---' }}</div>
                <div><b>الكلية والقسم الجديد:</b> {{ $application->work_faculty ?? '---' }} / {{ $application->work_department ?? '---' }}</div>
                <div style="color: #047857; font-weight: bold;">
                    <b>كتاب الجامعة الجديدة:</b> {{ $application->new_uni_request_no ? 'رقم (' . $application->new_uni_request_no . ') بتاريخ ' . $application->new_uni_request_date : 'مرفق بالطلب' }}
                </div>
            </td>
        </tr>
    </table>
</div>
@elseif($application->request_type == 'إضافة مقررات دراسية')
@php
    $parentApp = $application->parentApplication;
    $parentDecision = $parentApp ? $parentApp->latestDecision : null;
@endphp
<!-- ADD COURSES BLOCK FOR PDF -->
<div style="margin: 10px 0; border: 1.5px dashed #10B981; padding: 8px; background: #ECFDF5;">
    <div style="font-weight: bold; color: #065F46; font-size: 12px; margin-bottom: 6px; border-bottom: 1px solid #A7F3D0; padding-bottom: 3px; text-align: right;">
        تفاصيل طلب إضافة مقررات دراسية جديدة:
    </div>
    <table style="width: 100%; border-collapse: collapse; border: none; font-size: 11.5px;">
        <tr>
            <td style="padding: 3px 0; color: #475569; width: 140px;"><b>الجامعة والكلية والقسم:</b></td>
            <td style="padding: 3px 0; font-weight: bold; color: #065F46;">
                {{ optional($application->workUniversity)->name }} - {{ $application->work_faculty }} / {{ $application->work_department }}
            </td>
        </tr>
        <tr>
            <td style="padding: 3px 0; color: #475569;"><b>قرار المعادلة السابق:</b></td>
            <td style="padding: 3px 0; font-weight: bold; color: #0F172A;">
                {{ $parentDecision ? 'رقم (' . $parentDecision->decision_no . ') بتاريخ ' . $parentDecision->decision_date : 'تم الصدور أصولاً' }}
            </td>
        </tr>
    </table>
</div>
@else
<div class="dblock">
    <table class="mt">
        <tr>
            <td style="color: #1A2A44; font-weight: bold;">{{ optional($application->workUniversity)->name ?? '---' }}</td>
            <td class="l">في جامعة :</td>
            <td style="color: #1A2A44; font-weight: bold;">{{ $application->work_faculty ?? '---' }}</td>
            <td class="l">في كلية :</td>
            <td style="color: #1A2A44; font-weight: bold;">{{ $application->work_department ?? '---' }}</td>
            <td class="l">المرشح للعمل في قسم :</td>
        </tr>
    </table>
    <div style="color: #64748B; font-size: 11px; margin-top: 4px; padding: 4px 8px; text-align: right;">
        التي تطلب الجامعة تكليفه بتدريسها استناداً إلى قرار معادلة شهادته العلمية.
    </div>
</div>
@endif

<!-- 2. المقررات -->
@if($application->request_type == 'تحويل قرار المعادلة')
    @php
        $parentApp = $application->parentApplication;
        $oldUniName = optional(optional($parentApp)->workUniversity)->name ?? 'الجامعة السابقة';
        $newUniName = optional($application->workUniversity)->name ?? 'الجامعة الجديدة';
    @endphp
    <div class="sec">المقررات الدراسية (مقارنة المقررات بين الجامعة السابقة والجديدة) :</div>
    
    <!-- Previous University Courses -->
    <div style="margin-top: 6px; margin-bottom: 3px; font-weight: bold; color: #991B1B; font-size: 11.5px; text-align: right;">
        أولاً: المقررات التي تم تكليفه بها سابقاً في ({{ $oldUniName }}) :
    </div>
    <table class="ct" style="margin-bottom: 8px;">
        <thead>
            <tr style="background: #991B1B;">
                <th style="background: #991B1B; width: 30px;">#</th>
                <th style="background: #991B1B;">اسم المقرر الدراسي (في الجامعة السابقة)</th>
                <th style="background: #991B1B;">الكلية والفرع</th>
                <th style="background: #991B1B;">حالة المقرر</th>
            </tr>
        </thead>
        <tbody>
            @if($parentApp && $parentApp->courses && $parentApp->courses->count() > 0)
                @foreach($parentApp->courses as $idx => $c)
                <tr>
                    <td style="font-weight: bold;">{{ $idx + 1 }}</td>
                    <td style="font-weight:bold; color: #991B1B;">{{ $c->course_name }}</td>
                    <td>{{ $c->faculty ?? $parentApp->work_faculty }} - {{ $c->department ?? $parentApp->work_department }}</td>
                    <td>مستوفى</td>
                </tr>
                @endforeach
            @else
                <tr><td colspan="4" style="color: #64748B; text-align: center;">لم تكن هناك مقررات دراسية مطالب بها في القرار السابق.</td></tr>
            @endif
        </tbody>
    </table>

    <!-- New University Courses -->
    <div style="margin-top: 6px; margin-bottom: 3px; font-weight: bold; color: #065F46; font-size: 11.5px; text-align: right;">
        ثانياً: المقررات الدراسية المحدّثة المطلوب تكليفه بها في ({{ $newUniName }}) :
    </div>
    <table class="ct" style="margin-bottom: 8px;">
        <thead>
            <tr style="background: #065F46;">
                <th style="background: #065F46; width: 30px;">#</th>
                <th style="background: #065F46;">اسم المقرر الدراسي (في الجامعة الجديدة)</th>
                <th style="background: #065F46;">الكلية والفرع</th>
                <th style="background: #065F46;">الحالة</th>
            </tr>
        </thead>
        <tbody>
            @forelse($application->courses as $idx => $c)
            <tr>
                <td style="font-weight: bold;">{{ $idx + 1 }}</td>
                <td style="font-weight:bold; color: #065F46;">{{ $c->course_name }}</td>
                <td>{{ $application->work_faculty }} - {{ $application->work_department }}</td>
                <td style="font-weight: bold; color: #065F46;">مطالب به</td>
            </tr>
            @empty
            <tr><td colspan="4" style="color:#b91c1c; font-weight:bold; text-align:center;">لا توجد مقررات جديدة مضافة في التكليف الجديد.</td></tr>
            @endforelse
        </tbody>
    </table>
@elseif($application->request_type == 'إضافة مقررات دراسية')
    @php
        $parentApp = $application->parentApplication;
    @endphp
    <div class="sec">المقررات الدراسية (المقررات السابقة والمقررات المضافة حديثاً) :</div>
    
    <!-- Previous Courses -->
    <div style="margin-top: 6px; margin-bottom: 3px; font-weight: bold; color: #475569; font-size: 11.5px; text-align: right;">
        أولاً: المقررات التي تم تكليفه بها سابقاً بموجب القرار الصادر :
    </div>
    <table class="ct" style="margin-bottom: 8px;">
        <thead>
            <tr style="background: #475569;">
                <th style="background: #475569; width: 30px;">#</th>
                <th style="background: #475569;">اسم المقرر الدراسي (في القرار السابق)</th>
                <th style="background: #475569;">الكلية والفرع</th>
                <th style="background: #475569;">حالة المقرر</th>
            </tr>
        </thead>
        <tbody>
            @if($parentApp && $parentApp->courses && $parentApp->courses->count() > 0)
                @foreach($parentApp->courses as $idx => $c)
                <tr>
                    <td style="font-weight: bold;">{{ $idx + 1 }}</td>
                    <td style="font-weight:bold; color: #334155;">{{ $c->course_name }}</td>
                    <td>{{ $c->faculty ?? $parentApp->work_faculty }} - {{ $c->department ?? $parentApp->work_department }}</td>
                    <td>مستوفى</td>
                </tr>
                @endforeach
            @else
                <tr><td colspan="4" style="color: #64748B; text-align: center;">لم تكن هناك مقررات دراسية مطالب بها في القرار السابق.</td></tr>
            @endif
        </tbody>
    </table>

    <!-- New Added Courses -->
    <div style="margin-top: 6px; margin-bottom: 3px; font-weight: bold; color: #065F46; font-size: 11.5px; text-align: right;">
        ثانياً: المقررات الدراسية الجديدة المضافة بطلب الإضافة الحالي :
    </div>
    <table class="ct" style="margin-bottom: 8px;">
        <thead>
            <tr style="background: #065F46;">
                <th style="background: #065F46; width: 30px;">#</th>
                <th style="background: #065F46;">اسم المقرر الدراسي الجديد المضاف</th>
                <th style="background: #065F46;">الكلية والفرع</th>
                <th style="background: #065F46;">الحالة</th>
            </tr>
        </thead>
        <tbody>
            @forelse($application->courses as $idx => $c)
            <tr>
                <td style="font-weight: bold;">{{ $idx + 1 }}</td>
                <td style="font-weight:bold; color: #065F46;">{{ $c->course_name }}</td>
                <td>{{ $application->work_faculty }} - {{ $application->work_department }}</td>
                <td style="font-weight: bold; color: #065F46;">مُضاف حديثاً</td>
            </tr>
            @empty
            <tr><td colspan="4" style="color:#b91c1c; font-weight:bold; text-align:center;">لا توجد مقررات مضافة في هذا الطلب.</td></tr>
            @endforelse
        </tbody>
    </table>
@else
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
@endif

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
<div class="dblock">
    <table class="mt">
        <tr>
            <td style="color: #1A2A44; font-weight: bold;">{{ $candidate->is_syrian ? 'نعم' : 'لا' }}</td>
            <td class="l" style="width: 70%;">هل المرشح جنسيته السورية :</td>
        </tr>
        <tr>
            <td style="color: #1A2A44; font-weight: bold;">{{ $application->has_previous_degree ? 'نعم' : 'لا' }}</td>
            <td class="l" style="width: 70%;">هل المرشح حاصل على مؤهل علمي قبل المؤهل الأخير :</td>
        </tr>
        <tr>
            <td style="color: #1A2A44; font-weight: bold;">{{ $application->study_system ?? 'فصلي' }}</td>
            <td class="l" style="width: 70%;">نظام دراسة المرشح :</td>
        </tr>
    </table>
</div>

</body>
</html>