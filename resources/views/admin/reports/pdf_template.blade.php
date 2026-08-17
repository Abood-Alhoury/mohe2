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
    width: 70px; 
    height: 70px; 
    object-fit: contain;
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
    text-align: right;
}

/* DATA TABLES - RTL REVERSED COLUMN ORDER */
.mt { 
    width: 100%; 
    border-collapse: collapse; 
    margin: 0;
    direction: rtl;
}
.mt td { 
    padding: 5px 8px; 
    font-size: 12px; 
    border: 1px solid #C5C6CE; 
    color: #111C2C;
    text-align: right;
}
.mt td.l { 
    background-color: #f5f3f5; 
    font-weight: bold; 
    color: #1A2A44; 
    white-space: nowrap; 
    width: 150px;
    text-align: right;
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
    text-align: right;
}

/* COURSES TABLE */
.ct { 
    width: 100%; 
    border-collapse: collapse; 
    font-size: 12px;
    direction: rtl;
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

    <!-- TOP HEADER WITH EMBLEM & OFFICIAL TITLE (Reversed: text first, logo last for RTL) -->
    <table class="moz-header">
        <tr>
            <td class="header-txt" style="vertical-align: middle; padding-right: 8px;">
                <div class="ar-title">الجمهورية العربية السورية</div>
                <div class="ar-title">وزارة التعليم العالي والبحث العلمي</div>
                <div class="en-title">MINISTRY OF HIGHER EDUCATION AND SCIENTIFIC RESEARCH</div>
            </td>
            <td style="width: 70px; vertical-align: middle; text-align: left;">
                <img src="{{ public_path('assets/report_logo.png') }}" class="emblem-img" alt="شعار الجمهورية العربية السورية">
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
        {{-- =========================================================================
             CUSTOM PDF MOZHAKKARA FOR FACULTY TEACHING PERMISSION
        ========================================================================= --}}
        <div class="moz-title">(مذكرة العرض - سماح بالتدريس)</div>

        {{-- 1. البيانات الشخصية للمرشح --}}
        <div class="moz-section">البيانات الشخصية للمرشح :</div>
        <table class="mt">
            <tr>
                <td style="font-weight:bold;">{{ $application->application_no ?? $candidate->id }}</td>
                <td class="l">رقم المعاملة :</td>
                <td style="font-weight:bold; color: #1A2A44;">{{ $application->request_type ?? 'عضو هيئة تدريسية - سماح بالتدريس' }}</td>
                <td class="l">نوع المعاملة :</td>
            </tr>
        </table>
        
        <div class="cname">اسم المرشح : {{ $candidate->full_name }}</div>
        
        <table class="mt">
            <tr><td>{{ $candidate->is_syrian ? 'سورية' : (optional($candidate->nationality)->name ?? 'سورية') }}</td><td class="l">الجنسية :</td><td style="font-weight:bold;">{{ $candidate->national_id }}</td><td class="l">الرقم الوطني :</td></tr>
            <tr><td>{{ optional($govEd)->rank ?? $candidate->job_title ?? 'عضو هيئة تدريسية' }}</td><td class="l">الصفة الأكاديمية :</td><td>{{ format_sys_date($candidate->dob) }}</td><td class="l">تاريخ الميلاد :</td></tr>
            <tr><td>{{ $candidate->mobile }}</td><td class="l">رقم الجوال :</td><td>{{ $candidate->phone ?? '---' }}</td><td class="l">رقم الهاتف :</td></tr>
            <tr><td colspan="3" style="color: #1A2A44; font-weight: bold;">{{ $candidate->email }}</td><td class="l">البريد الإلكتروني :</td></tr>
            <tr><td colspan="3">{{ $candidate->address }}</td><td class="l">العنوان :</td></tr>
        </table>

        {{-- 2. كتاب طلب التقييم الصادر عن الجامعة الخاصة --}}
        <div class="moz-section">بيانات كتاب طلب التقييم الصادر عن الجامعة :</div>
        <table class="mt">
            <tr>
                <td><strong>رقم:</strong> {{ $application->new_uni_request_no ?? '---' }} | <strong>بتاريخ:</strong> {{ format_sys_date($application->new_uni_request_date) }}</td>
                <td class="l">رقم وتاريخ الكتاب :</td>
                <td style="font-weight:bold; color: #1A2A44;">{{ optional($application->workUniversity)->name ?? '---' }}</td>
                <td class="l">الجامعة الطالبة :</td>
            </tr>
        </table>

        {{-- 3. بيانات التعيين بالجامعة الحكومية --}}
        <div class="moz-section">بيانات التعيين والصفة بالجامعة الحكومية السورية :</div>
        <table class="mt">
            <tr>
                <td style="font-weight:bold;">{{ optional($govEd)->rank ?? 'مدرس' }}</td>
                <td class="l">الرتبة الأكاديمية :</td>
                <td style="font-weight:bold; color: #1A2A44;">{{ optional($govEd->university)->name ?? optional($govEd)->university_other ?? '---' }}</td>
                <td class="l">الجامعة الحكومية :</td>
            </tr>
            <tr>
                <td>{{ optional($govEd)->department ?: optional($govEd)->exact_specialization ?: '---' }}</td>
                <td class="l">القسم التابع له :</td>
                <td>{{ optional($govEd)->faculty ?: optional($govEd)->general_specialization ?: '---' }}</td>
                <td class="l">الكلية التابع لها :</td>
            </tr>
        </table>

        {{-- 4. بيانات شهادة الدكتوراه --}}
        <div class="moz-section">بيانات شهادة الدكتوراه (المؤهل العلمي الأساسي) :</div>
        <table class="mt">
            <tr>
                <td>{{ format_sys_date(optional($phdEd)->grant_date) }}</td>
                <td class="l">تاريخ / سنة المنح :</td>
                <td style="font-weight:bold; color: #1A2A44;">{{ optional($phdEd->university)->name ?? optional($phdEd)->university_other ?? '---' }}</td>
                <td class="l">الجامعة المانحة :</td>
            </tr>
            <tr>
                <td>{{ optional($phdEd)->department ?: optional($phdEd)->exact_specialization ?: '---' }}</td>
                <td class="l">القسم / الاختصاص :</td>
                <td>{{ optional($phdEd)->faculty ?: optional($phdEd)->general_specialization ?: '---' }}</td>
                <td class="l">الكلية المانحة :</td>
            </tr>
        </table>

    @else
        {{-- =========================================================================
             STANDARD PDF MOZHAKKARA FOR MASTERS / PHD / EQUIVALENCE
        ========================================================================= --}}
        <div class="moz-title">(مذكرة العرض)</div>

        {{-- 1. البيانات الشخصية (Reversed column order for RTL in DomPDF) --}}
        <div class="moz-section">البيانات الشخصية للمرشح :</div>
        <table class="mt">
            <tr>
                <td style="font-weight:bold;">{{ $candidate->id }}</td>
                <td class="l">ID :</td>
                <td style="font-weight:bold; color: #1A2A44;">{{ $application->request_type ?? 'تعادل شهادة' }}</td>
                <td class="l">نوع الطلب :</td>
            </tr>
        </table>
        
        <div class="cname">اسم المرشح : {{ $candidate->full_name }}</div>
        
        <table class="mt">
            <tr><td>{{ $candidate->is_syrian ? 'سورية' : 'غير سورية' }}</td><td class="l">الجنسية :</td><td style="font-weight:bold;">{{ $candidate->national_id }}</td><td class="l">الرقم الوطني :</td></tr>
            <tr><td>{{ $candidate->job_title }}</td><td class="l">الوظيفة :</td><td>{{ format_sys_date($candidate->dob) }}</td><td class="l">تاريخ الميلاد :</td></tr>
            <tr><td>{{ $candidate->mobile }}</td><td class="l">رقم الجوال :</td><td>{{ $candidate->phone }}</td><td class="l">رقم الهاتف :</td></tr>
            <tr><td colspan="3" style="color: #1A2A44; font-weight: bold;">{{ $candidate->email }}</td><td class="l">البريد الإلكتروني :</td></tr>
            <tr><td colspan="3">{{ $candidate->address }}</td><td class="l">العنوان :</td></tr>
        </table>
        
        {{-- 2. الشهادة الثانوية --}}
        <div class="moz-section">الشهادة الثانوية :</div>
        @if($highSchoolEd)
        <table class="mt">
            <tr><td>{{ $highSchoolEd->section_name ?: ($highSchoolEd->general_specialization ?: ($highSchoolEd->type_or_faculty ?: 'علمي')) }}</td><td class="l">نوع الشهادة :</td><td>{{ optional($highSchoolEd->country)->name ?? 'سوريا' }}</td><td class="l">بلد المنح :</td></tr>
            <tr><td>{{ $highSchoolEd->decision_no ?? 'لا يوجد' }}</td><td class="l">قرار المعادلة :</td><td>{{ format_sys_date($highSchoolEd->grant_date) }}</td><td class="l">تاريخ المنح :</td></tr>
        </table>
        @else
        <div class="wblock">لا توجد بيانات مسجلة للشهادة الثانوية.</div>
        @endif

        {{-- 3. الإجازة الجامعية --}}
        <div class="moz-section">الإجازة الجامعية :</div>
        @if($bachelorEd)
        <table class="mt">
            <tr><td>{{ optional($bachelorEd->university)->name ?? $bachelorEd->university_other }}</td><td class="l">الجامعة :</td><td>{{ optional($bachelorEd->country)->name }}</td><td class="l">بلد المنح :</td></tr>
            <tr><td>{{ format_sys_date($bachelorEd->registration_date) }}</td><td class="l">تاريخ التسجيل :</td><td>{{ $bachelorEd->general_specialization ?: ($bachelorEd->faculty ?: '---') }} - {{ $bachelorEd->exact_specialization ?: $bachelorEd->department }} {{ $bachelorEd->section_name ? '(' . $bachelorEd->section_name . ')' : '' }}</td><td class="l">الكلية والفرع :</td></tr>
            <tr><td>{{ $bachelorEd->rank_or_grade }}</td><td class="l">التقدير/المعدل :</td><td>{{ format_sys_date($bachelorEd->grant_date) }}</td><td class="l">تاريخ المنح :</td></tr>
        </table>
        @else
        <div class="wblock">لا توجد بيانات مسجلة للإجازة الجامعية.</div>
        @endif

        {{-- 4. الماجستير --}}
        @if($masterEd)
        <div class="moz-section">درجة الماجستير المراد تعادلها :</div>
        <table class="mt">
            <tr><td>{{ $masterEd->exact_specialization ?: ($masterEd->department ?: ($masterEd->section_name ?: '---')) }} {{ $masterEd->section_name && $masterEd->section_name !== ($masterEd->exact_specialization ?: $masterEd->department) ? '(' . $masterEd->section_name . ')' : '' }}</td><td class="l">القسم والفرع :</td><td>{{ optional($masterEd->university)->name ?? ($masterEd->university_other ?? '---') }} - {{ $masterEd->general_specialization ?: $masterEd->faculty }}</td><td class="l">الجامعة والكلية :</td></tr>
            <tr><td>{{ format_sys_date($masterEd->defense_date) }}</td><td class="l">تاريخ المناقشة :</td><td>{{ format_sys_date($masterEd->registration_date) }}</td><td class="l">تاريخ التسجيل :</td></tr>
            <tr><td>{{ $masterEd->rank_or_grade }}</td><td class="l">التقدير :</td><td>{{ format_sys_date($masterEd->grant_date) }}</td><td class="l">تاريخ المنح :</td></tr>
            <tr><td colspan="3">{{ $masterEd->supervisor_name ?? '---' }}</td><td class="l">الأستاذ المشرف :</td></tr>
            <tr><td colspan="3" style="font-weight: bold; color: #1A2A44;">{{ $masterEd->thesis_title ?? '---' }}</td><td class="l">عنوان الرسالة :</td></tr>
        </table>
        @endif

        {{-- 5. الدكتوراه --}}
        @if($phdEd)
        <div class="moz-section">درجة الدكتوراه المراد تعادلها :</div>
        <table class="mt">
            <tr><td>{{ $phdEd->exact_specialization ?: ($phdEd->department ?: ($phdEd->section_name ?: '---')) }} {{ $phdEd->section_name && $phdEd->section_name !== ($phdEd->exact_specialization ?: $phdEd->department) ? '(' . $phdEd->section_name . ')' : '' }}</td><td class="l">القسم والفرع :</td><td>{{ optional($phdEd->university)->name ?? ($phdEd->university_other ?? '---') }} - {{ $phdEd->general_specialization ?: $phdEd->faculty }}</td><td class="l">الجامعة والكلية :</td></tr>
            <tr><td>{{ $phdEd->rank_or_grade }}</td><td class="l">التقدير :</td><td>{{ format_sys_date($phdEd->grant_date) }}</td><td class="l">تاريخ المنح :</td></tr>
        </table>
        @endif

        {{-- 6. بيانات التكليف والجامعة المطلوب التعادل لصالحها --}}
        <div class="moz-section">بيانات التكليف والجامعة المطلوبة :</div>
        <table class="mt">
            <tr><td>{{ $application->work_faculty }} - {{ $application->work_department }}</td><td class="l">الكلية والفرع :</td><td style="font-weight:bold; color: #1A2A44;">{{ optional($application->workUniversity)->name }}</td><td class="l">الجامعة المعنية :</td></tr>
        </table>
    @endif


    <!-- OFFICIAL FOOTER: CANDIDATE SIGNATURE (RIGHT) & SUBMISSION DATE (LEFT) - Reversed for DomPDF RTL -->
    <div style="margin-top: 30px; border-top: 2px solid #C5A059; padding-top: 15px; page-break-inside: avoid;">
        <table style="width: 100%; border-collapse: collapse;" border="0" cellspacing="0" cellpadding="0">
            <tr>
                <!-- Left side in DomPDF = Right side in RTL: Submission Date -->
                <td style="width: 50%; text-align: left; vertical-align: top;" align="left">
                    <div style="font-weight: bold; color: #1A2A44; font-size: 13px; margin-bottom: 6px; text-align: left;">تاريخ تقديم الطلب :</div>
                    <div style="font-size: 14px; font-weight: bold; color: #111C2C; margin-top: 6px; text-align: left;">
                        {{ format_sys_date($application->created_at ?? now()) }}
                    </div>
                </td>

                <!-- Right side in DomPDF = Left side in RTL: Candidate Signature -->
                <td style="width: 50%; text-align: right; vertical-align: top;" align="right">
                    <div style="font-weight: bold; color: #1A2A44; font-size: 13px; margin-bottom: 6px;">توقيع المرشح صاحب العلاقة :</div>
                    <div style="margin-top: 20px; border-bottom: 1.5px dashed #1A2A44; width: 200px; height: 1px; margin-right: auto; margin-left: 0;"></div>
                    <div style="font-size: 11px; color: #666666; margin-top: 4px;">(التوقيع والاسم الثلاثي للمرشح)</div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>