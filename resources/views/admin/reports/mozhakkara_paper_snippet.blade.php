<!-- MOZHAKKARA DOCUMENT PAPER SNIPPET -->
<div class="moz-wrapper">
    <div class="moz-header d-flex justify-content-between align-items-center mb-3 pb-2" style="border-bottom: 3px double var(--heritage-gold);">
        <div class="d-flex align-items-center gap-3">
            <div class="mohe-emblem-ring">
                <img src="{{ asset('assets/logo.jpg') }}" alt="شعار الوزارة" onerror="this.onerror=null; this.src='{{ asset('images/mohe_logo.jpg') }}';">
            </div>
            <div class="moz-header-text text-start">
                <div class="ar" style="font-weight: 700; color: var(--imperial-navy); font-size: 1.05rem;">الجمهورية العربية السورية</div>
                <div class="ar" style="font-weight: 700; color: var(--imperial-navy); font-size: 1.05rem;">وزارة التعليم العالي والبحث العلمي</div>
                <div class="en" style="font-size: 0.72rem; color: #666; letter-spacing: 0.5px;">MINISTRY OF HIGHER EDUCATION AND SCIENTIFIC RESEARCH</div>
            </div>
        </div>
    </div>
    
    <div class="moz-title">(مذكرة العرض)</div>

    {{-- 1. البيانات الشخصية --}}
    <div class="moz-section"><i class="fa-solid fa-user me-1"></i> البيانات الشخصية للمرشح :</div>
    <table class="mt">
        <tr>
            <td class="l">نوع الطلب :</td>
            <td style="font-weight:bold; color: var(--imperial-navy);">
                {{ $application->request_type ?? 'تعادل شهادة' }}
            </td>
            <td class="l">ID :</td>
            <td class="fw-bold">{{ $candidate->id }}</td>
        </tr>
    </table>
    
    <div class="cname">اسم المرشح : {{ $candidate->full_name }}</div>
    
    <table class="mt">
        <tr><td class="l">الرقم الوطني :</td><td class="fw-bold">{{ $candidate->national_id }}</td><td class="l">الجنسية :</td><td>{{ $candidate->is_syrian ? 'سورية' : 'غير سورية' }}</td></tr>
        <tr><td class="l">تاريخ الميلاد :</td><td>{{ format_sys_date($candidate->dob) }}</td><td class="l">الوظيفة :</td><td>{{ $candidate->job_title }}</td></tr>
        <tr><td class="l">رقم الهاتف :</td><td>{{ $candidate->phone }}</td><td class="l">رقم الجوال :</td><td>{{ $candidate->mobile }}</td></tr>
        <tr><td class="l">البريد الإلكتروني :</td><td colspan="3" style="color: var(--imperial-navy); font-weight: 600;">{{ $candidate->email }}</td></tr>
        <tr><td class="l">العنوان :</td><td colspan="3">{{ $candidate->address }}</td></tr>
    </table>
    
    {{-- 2. الشهادة الثانوية --}}
    <div class="moz-section"><i class="fa-solid fa-graduation-cap me-1"></i> الشهادة الثانوية :</div>
    @if($highSchoolEd)
    <table class="mt">
        <tr><td class="l">بلد المنح :</td><td>{{ optional($highSchoolEd->country)->name }}</td><td class="l">نوع الشهادة :</td><td>{{ $highSchoolEd->type_or_faculty }}</td></tr>
        <tr><td class="l">تاريخ المنح :</td><td>{{ format_sys_date($highSchoolEd->grant_date) }}</td><td class="l">قرار المعادلة :</td><td>{{ $highSchoolEd->decision_no ?? 'لا يوجد' }}</td></tr>
    </table>
    @else
    <div class="wblock text-muted">لا توجد بيانات مسجلة للشهادة الثانوية.</div>
    @endif

    {{-- 3. الإجازة الجامعية --}}
    <div class="moz-section"><i class="fa-solid fa-university me-1"></i> الإجازة الجامعية :</div>
    @if($bachelorEd)
    <table class="mt">
        <tr><td class="l">بلد المنح :</td><td>{{ optional($bachelorEd->country)->name }}</td><td class="l">الجامعة :</td><td>{{ optional($bachelorEd->university)->name ?? $bachelorEd->university_other }}</td></tr>
        <tr><td class="l">الكلية والفرع :</td><td>{{ $bachelorEd->type_or_faculty }} - {{ $bachelorEd->specialization_or_dept }}</td><td class="l">تاريخ التسجيل :</td><td>{{ format_sys_date($bachelorEd->registration_date) }}</td></tr>
        <tr><td class="l">تاريخ المنح :</td><td>{{ format_sys_date($bachelorEd->grant_date) }}</td><td class="l">التقدير/المعدل :</td><td>{{ $bachelorEd->rank_or_grade }}</td></tr>
    </table>
    @else
    <div class="wblock text-muted">لا توجد بيانات مسجلة للإجازة الجامعية.</div>
    @endif

    {{-- 4. الدبلوم --}}
    @if($diplomaEd)
    <div class="moz-section"><i class="fa-solid fa-certificate me-1"></i> دبلوم الدراسات العليا :</div>
    <table class="mt">
        <tr><td class="l">الجامعة والكلية :</td><td>{{ optional($diplomaEd->university)->name }} - {{ $diplomaEd->type_or_faculty }}</td><td class="l">التخصص :</td><td>{{ $diplomaEd->specialization_or_dept }}</td></tr>
        <tr><td class="l">تاريخ المنح :</td><td>{{ format_sys_date($diplomaEd->grant_date) }}</td><td class="l">التقدير/المعدل :</td><td>{{ $diplomaEd->rank_or_grade }}</td></tr>
    </table>
    @endif

    {{-- 5. الماجستير --}}
    @if($masterEd)
    <div class="moz-section"><i class="fa-solid fa-scroll me-1"></i> درجة الماجستير المراد تعادلها :</div>
    <table class="mt">
        <tr><td class="l">الجامعة والكلية :</td><td>{{ optional($masterEd->university)->name }} - {{ $masterEd->type_or_faculty }}</td><td class="l">القسم :</td><td>{{ $masterEd->specialization_or_dept }}</td></tr>
        <tr><td class="l">تاريخ التسجيل :</td><td>{{ format_sys_date($masterEd->registration_date) }}</td><td class="l">تاريخ المناقشة :</td><td>{{ format_sys_date($masterEd->defense_date) }}</td></tr>
        <tr><td class="l">تاريخ المنح :</td><td>{{ format_sys_date($masterEd->grant_date) }}</td><td class="l">التقدير :</td><td>{{ $masterEd->rank_or_grade }}</td></tr>
        <tr><td class="l">الأستاذ المشرف :</td><td colspan="3">{{ $masterEd->supervisor_name }}</td></tr>
        <tr><td class="l">عنوان الرسالة :</td><td colspan="3" style="font-weight: 600; color: var(--imperial-navy);">{{ $masterEd->thesis_title }}</td></tr>
    </table>
    @endif

    {{-- 6. الدكتوراه --}}
    @if($phdEd)
    <div class="moz-section"><i class="fa-solid fa-award me-1"></i> درجة الدكتوراه المراد تعادلها :</div>
    <table class="mt">
        <tr><td class="l">الجامعة والكلية :</td><td>{{ optional($phdEd->university)->name }} - {{ $phdEd->type_or_faculty }}</td><td class="l">القسم :</td><td>{{ $phdEd->specialization_or_dept }}</td></tr>
        <tr><td class="l">تاريخ المنح :</td><td>{{ format_sys_date($phdEd->grant_date) }}</td><td class="l">التقدير :</td><td>{{ $phdEd->rank_or_grade }}</td></tr>
    </table>
    @endif

    {{-- 7. بيانات التكليف والجامعة المطلوب التعادل لصالحها --}}
    <div class="moz-section"><i class="fa-solid fa-building-columns me-1"></i> بيانات التكليف والجامعة المطلوبة :</div>
    <table class="mt">
        <tr><td class="l">الجامعة المعنية :</td><td style="font-weight:bold; color: var(--imperial-navy);">{{ optional($application->workUniversity)->name }}</td><td class="l">الكلية والفرع :</td><td>{{ $application->work_faculty }} - {{ $application->work_department }}</td></tr>
    </table>


    <!-- OFFICIAL FOOTER: CANDIDATE SIGNATURE (RIGHT) & SUBMISSION DATE (LEFT) -->
    <div style="margin-top: 30px; border-top: 2px solid var(--heritage-gold); padding-top: 15px;">
        <table style="width: 100%; border-collapse: collapse;" border="0" cellspacing="0" cellpadding="0">
            <tr>
                <!-- Right side: Candidate Signature -->
                <td style="width: 50%; text-align: right; vertical-align: top;" align="right">
                    <div style="font-weight: bold; color: var(--imperial-navy); font-size: 13.5px; margin-bottom: 6px;">
                        <i class="fa-solid fa-signature me-1.5" style="color: var(--heritage-gold);"></i> توقيع المرشح صاحب العلاقة :
                    </div>
                    <div style="margin-top: 20px; border-bottom: 1.5px dashed var(--imperial-navy); width: 200px; height: 1px;"></div>
                    <div style="font-size: 11px; color: #666; margin-top: 4px;">(التوقيع والاسم الثلاثي للمرشح)</div>
                </td>

                <!-- Left side: Submission Date -->
                <td style="width: 50%; text-align: left; vertical-align: top;" align="left">
                    <div style="font-weight: bold; color: var(--imperial-navy); font-size: 13.5px; margin-bottom: 6px; text-align: left;">
                        <i class="fa-regular fa-calendar-check me-1.5" style="color: var(--heritage-gold);"></i> تاريخ تقديم الطلب :
                    </div>
                    <div style="font-size: 1.05rem; font-weight: 700; color: #111C2C; margin-top: 6px; text-align: left;">
                        {{ format_sys_date($application->created_at ?? now()) }}
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>
