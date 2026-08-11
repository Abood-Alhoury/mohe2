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
    font-size: 10px; 
    color: #555555; 
    letter-spacing: 0.5px; 
}
.emblem-td { 
    text-align: left; 
    width: 80px; 
}
.emblem-img { 
    width: 65px; 
    height: 65px; 
    border-radius: 50%; 
    border: 2px solid #C5A059; 
}

/* DOCUMENT MAIN TITLE */
.moz-title { 
    text-align: center; 
    font-size: 18px; 
    font-weight: bold; 
    color: #1A2A44; 
    margin: 10px 0 14px; 
}

/* SECTION HEADER BANNER */
.moz-section { 
    background-color: #1A2A44; 
    color: #ffffff; 
    font-weight: bold; 
    font-size: 13px; 
    padding: 5px 12px; 
    margin: 14px 0 6px; 
    border-right: 4px solid #C5A059; 
    border-radius: 2px;
}

/* DATA TABLES */
.mt { 
    width: 100%; 
    border-collapse: collapse; 
    margin: 0; 
}
.mt td { 
    padding: 5px 8px; 
    font-size: 12px; 
    border: 1px solid #C5C6CE; 
    color: #111C2C; 
}
.mt td.l { 
    background-color: #f5f3f5; 
    font-weight: bold; 
    color: #1A2A44; 
    white-space: nowrap; 
    width: 150px; 
}

.cname { 
    background-color: #f0f3ff; 
    border: 1px solid #C5C6CE; 
    border-right: 4px solid #C5A059;
    padding: 7px 12px; 
    font-size: 14px; 
    font-weight: bold; 
    color: #1A2A44; 
    text-align: center; 
    margin: 6px 0; 
}

.wblock { 
    background-color: #faf9fb; 
    border: 1px solid #C5C6CE; 
    padding: 8px 12px; 
    font-size: 12px; 
    margin-top: 6px; 
    color: #111C2C; 
}

/* COURSES TABLE */
.ct { 
    width: 100%; 
    border-collapse: collapse; 
    font-size: 12px; 
}
.ct th { 
    background-color: #1A2A44; 
    color: #ffffff; 
    padding: 6px 8px; 
    text-align: center; 
    font-weight: bold; 
}
.ct td { 
    border: 1px solid #C5C6CE; 
    padding: 5px 8px; 
    text-align: center; 
    color: #111C2C; 
}
</style>
</head>
<body>

    <!-- TOP HEADER WITH EMBLEM & OFFICIAL TITLE -->
    <table class="moz-header">
        <tr>
            <td style="width: 70px; vertical-align: middle;">
                <img src="{{ public_path('assets/logo.jpg') }}" class="emblem-img" alt="شعار الوزارة">
            </td>
            <td class="header-txt" style="vertical-align: middle; padding-right: 8px;">
                <div class="ar-title">الجمهورية العربية السورية</div>
                <div class="ar-title">وزارة التعليم العالي والبحث العلمي</div>
                <div class="en-title">MINISTRY OF HIGHER EDUCATION AND SCIENTIFIC RESEARCH</div>
            </td>
        </tr>
    </table>

    <div class="moz-title">(مذكرة العرض)</div>

    {{-- 1. البيانات الشخصية --}}
    <div class="moz-section">البيانات الشخصية للمرشح :</div>
    <table class="mt">
        <tr>
            <td class="l">نوع الطلب :</td>
            <td style="font-weight:bold; color: #1A2A44;">{{ $application->request_type ?? 'تعادل شهادة' }}</td>
            <td class="l">ID :</td>
            <td style="font-weight:bold;">{{ $candidate->id }}</td>
        </tr>
    </table>
    
    <div class="cname">اسم المرشح : {{ $candidate->full_name }}</div>
    
    <table class="mt">
        <tr><td class="l">الرقم الوطني :</td><td style="font-weight:bold;">{{ $candidate->national_id }}</td><td class="l">الجنسية :</td><td>{{ $candidate->is_syrian ? 'سورية' : 'غير سورية' }}</td></tr>
        <tr><td class="l">تاريخ الميلاد :</td><td>{{ format_sys_date($candidate->dob) }}</td><td class="l">الوظيفة :</td><td>{{ $candidate->job_title }}</td></tr>
        <tr><td class="l">رقم الهاتف :</td><td>{{ $candidate->phone }}</td><td class="l">رقم الجوال :</td><td>{{ $candidate->mobile }}</td></tr>
        <tr><td class="l">البريد الإلكتروني :</td><td colspan="3" style="color: #1A2A44; font-weight: bold;">{{ $candidate->email }}</td></tr>
        <tr><td class="l">العنوان :</td><td colspan="3">{{ $candidate->address }}</td></tr>
    </table>
    
    {{-- 2. الشهادة الثانوية --}}
    <div class="moz-section">الشهادة الثانوية :</div>
    @if($highSchoolEd)
    <table class="mt">
        <tr><td class="l">بلد المنح :</td><td>{{ optional($highSchoolEd->country)->name }}</td><td class="l">نوع الشهادة :</td><td>{{ $highSchoolEd->type_or_faculty }}</td></tr>
        <tr><td class="l">تاريخ المنح :</td><td>{{ format_sys_date($highSchoolEd->grant_date) }}</td><td class="l">قرار المعادلة :</td><td>{{ $highSchoolEd->decision_no ?? 'لا يوجد' }}</td></tr>
    </table>
    @else
    <div class="wblock">لا توجد بيانات مسجلة للشهادة الثانوية.</div>
    @endif

    {{-- 3. الإجازة الجامعية --}}
    <div class="moz-section">الإجازة الجامعية :</div>
    @if($bachelorEd)
    <table class="mt">
        <tr><td class="l">بلد المنح :</td><td>{{ optional($bachelorEd->country)->name }}</td><td class="l">الجامعة :</td><td>{{ optional($bachelorEd->university)->name ?? $bachelorEd->university_other }}</td></tr>
        <tr><td class="l">الكلية والفرع :</td><td>{{ $bachelorEd->type_or_faculty }} - {{ $bachelorEd->specialization_or_dept }}</td><td class="l">تاريخ التسجيل :</td><td>{{ format_sys_date($bachelorEd->registration_date) }}</td></tr>
        <tr><td class="l">تاريخ المنح :</td><td>{{ format_sys_date($bachelorEd->grant_date) }}</td><td class="l">التقدير/المعدل :</td><td>{{ $bachelorEd->rank_or_grade }}</td></tr>
    </table>
    @else
    <div class="wblock">لا توجد بيانات مسجلة للإجازة الجامعية.</div>
    @endif

    {{-- 4. الماجستير --}}
    @if($masterEd)
    <div class="moz-section">درجة الماجستير المراد تعادلها :</div>
    <table class="mt">
        <tr><td class="l">الجامعة والكلية :</td><td>{{ optional($masterEd->university)->name }} - {{ $masterEd->type_or_faculty }}</td><td class="l">القسم :</td><td>{{ $masterEd->specialization_or_dept }}</td></tr>
        <tr><td class="l">تاريخ التسجيل :</td><td>{{ format_sys_date($masterEd->registration_date) }}</td><td class="l">تاريخ المناقشة :</td><td>{{ format_sys_date($masterEd->defense_date) }}</td></tr>
        <tr><td class="l">تاريخ المنح :</td><td>{{ format_sys_date($masterEd->grant_date) }}</td><td class="l">التقدير :</td><td>{{ $masterEd->rank_or_grade }}</td></tr>
        <tr><td class="l">الأستاذ المشرف :</td><td colspan="3">{{ $masterEd->supervisor_name }}</td></tr>
        <tr><td class="l">عنوان الرسالة :</td><td colspan="3" style="font-weight: bold; color: #1A2A44;">{{ $masterEd->thesis_title }}</td></tr>
    </table>
    @endif

    {{-- 5. الدكتوراه --}}
    @if($phdEd)
    <div class="moz-section">درجة الدكتوراه المراد تعادلها :</div>
    <table class="mt">
        <tr><td class="l">الجامعة والكلية :</td><td>{{ optional($phdEd->university)->name }} - {{ $phdEd->type_or_faculty }}</td><td class="l">القسم :</td><td>{{ $phdEd->specialization_or_dept }}</td></tr>
        <tr><td class="l">تاريخ المنح :</td><td>{{ format_sys_date($phdEd->grant_date) }}</td><td class="l">التقدير :</td><td>{{ $phdEd->rank_or_grade }}</td></tr>
    </table>
    @endif

    {{-- 6. بيانات التكليف والجامعة المطلوب التعادل لصالحها --}}
    <div class="moz-section">بيانات التكليف والجامعة المطلوبة :</div>
    <table class="mt">
        <tr><td class="l">الجامعة المعنية :</td><td style="font-weight:bold; color: #1A2A44;">{{ optional($application->workUniversity)->name }}</td><td class="l">الكلية والفرع :</td><td>{{ $application->work_faculty }} - {{ $application->work_department }}</td></tr>
    </table>

    {{-- 7. المقررات الدراسية المطلوبة --}}
    <div class="moz-section">المقررات الدراسية المكلّف بتدريسها :</div>
    @if($application->courses && $application->courses->count() > 0)
    <table class="ct">
        <thead>
            <tr>
                <th style="width: 30px;">#</th>
                <th>اسم المقرر الدراسي</th>
                <th>الكلية والفرع</th>
                <th style="width: 100px;">حالة المقرر</th>
            </tr>
        </thead>
        <tbody>
            @foreach($application->courses as $idx => $c)
            <tr>
                <td>{{ $idx + 1 }}</td>
                <td style="font-weight: bold;">{{ $c->course_name }}</td>
                <td>{{ $c->faculty_name }} - {{ $c->department_name }}</td>
                <td>{{ $c->course_status ?? 'مستوفى' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="wblock">لا توجد مقررات دراسية مسجلة في هذا الطلب.</div>
    @endif

    <!-- OFFICIAL FOOTER: CANDIDATE SIGNATURE (RIGHT) & SUBMISSION DATE (LEFT) -->
    <div style="margin-top: 30px; border-top: 2px solid #C5A059; padding-top: 15px; page-break-inside: avoid;">
        <table style="width: 100%; border-collapse: collapse;" border="0" cellspacing="0" cellpadding="0">
            <tr>
                <!-- Right side: Candidate Signature -->
                <td style="width: 50%; text-align: right; vertical-align: top;" align="right">
                    <div style="font-weight: bold; color: #1A2A44; font-size: 13px; margin-bottom: 6px;">توقيع المرشح صاحب العلاقة :</div>
                    <div style="margin-top: 20px; border-bottom: 1.5px dashed #1A2A44; width: 200px; height: 1px;"></div>
                    <div style="font-size: 11px; color: #666666; margin-top: 4px;">(التوقيع والاسم الثلاثي للمرشح)</div>
                </td>

                <!-- Left side: Submission Date -->
                <td style="width: 50%; text-align: left; vertical-align: top;" align="left">
                    <div style="font-weight: bold; color: #1A2A44; font-size: 13px; margin-bottom: 6px; text-align: left;">تاريخ تقديم الطلب :</div>
                    <div style="font-size: 14px; font-weight: bold; color: #111C2C; margin-top: 6px; text-align: left;">
                        {{ format_sys_date($application->created_at ?? now()) }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>