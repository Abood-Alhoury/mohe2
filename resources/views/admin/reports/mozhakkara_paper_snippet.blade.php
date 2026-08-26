<!-- MOZHAKKARA DOCUMENT PAPER SNIPPET -->
@php
    $reqType = $application->request_type ?? '';
    $isForeignMaster = str_contains($reqType, 'ماجستير خارجي') || str_contains($reqType, 'خارجي') || str_contains($reqType, 'غير سوري');
    $isFacultyPermission = !$isForeignMaster && (str_contains($reqType, 'سماح') || str_contains($reqType, 'هيئة تدريسية'));
    $govEd = $govEd ?? ($application->educations ? $application->educations->first(function($e) {
        return $e->thesis_title === 'عضو هيئة تدريسية في جامعة حكومية' || (optional($e->level)->name && str_contains(optional($e->level)->name, 'حكومية'));
    }) : null);
    $phdEd = $phdEd ?? ($application->educations ? $application->educations->first(function($e) {
        return $e->thesis_title === 'شهادة الدكتوراه' || (optional($e->level)->name == 'دكتوراه' && $e->thesis_title !== 'عضو هيئة تدريسية في جامعة حكومية');
    }) : null);
@endphp

<div class="moz-wrapper">
    <div class="moz-header d-flex justify-content-between align-items-center mb-3 pb-2" style="border-bottom: 3px double var(--heritage-gold);">
        <div class="d-flex align-items-center gap-3">
            <div class="mohe-emblem-ring" style="border: none; background: transparent; box-shadow: none;">
                <img src="{{ asset('assets/report_logo.png') }}" alt="شعار الجمهورية العربية السورية" style="width: 75px; height: 75px; object-fit: contain;">
            </div>
            <div class="moz-header-text text-start">
                <div class="ar" style="font-weight: 700; color: var(--imperial-navy); font-size: 1.05rem;">الجمهورية العربية السورية</div>
                <div class="ar" style="font-weight: 700; color: var(--imperial-navy); font-size: 1.05rem;">وزارة التعليم العالي والبحث العلمي</div>
                <div class="en" style="font-size: 0.72rem; color: #666; letter-spacing: 0.5px;">MINISTRY OF HIGHER EDUCATION AND SCIENTIFIC RESEARCH</div>
            </div>
        </div>
    </div>
    
    @if($isFacultyPermission)
        {{-- =========================================================================
             CUSTOM MOZHAKKARA FOR FACULTY TEACHING PERMISSION (عضو هيئة تدريسية - سماح)
        ========================================================================= --}}
        <div class="moz-title">(مذكرة العرض - سماح بالتدريس)</div>

        {{-- 1. البيانات الشخصية --}}
        <div class="moz-section"><i class="fa-solid fa-user me-1"></i> البيانات الشخصية للمرشح :</div>
        <table class="mt">
            <tr>
                <td class="l">نوع المعاملة :</td>
                <td style="font-weight:bold; color: var(--imperial-navy);">
                    {{ $application->request_type ?? 'عضو هيئة تدريسية - سماح بالتدريس' }}
                </td>
                <td class="l">رقم المعاملة :</td>
                <td class="fw-bold">{{ $application->application_no ?? $candidate->id }}</td>
            </tr>
        </table>
        
        <div class="cname">اسم المرشح : {{ $candidate->full_name }}</div>
        
        <table class="mt">
            <tr><td class="l">الرقم الوطني :</td><td class="fw-bold">{{ $candidate->national_id }}</td><td class="l">الجنسية :</td><td>{{ $candidate->is_syrian ? 'سورية' : (optional($candidate->nationality)->name ?? 'سورية') }}</td></tr>
            <tr><td class="l">تاريخ الميلاد :</td><td>{{ format_sys_date($candidate->dob) }}</td><td class="l">الصفة الأكاديمية :</td><td>{{ optional($govEd)->rank ?? $candidate->job_title ?? 'عضو هيئة تدريسية' }}</td></tr>
            <tr><td class="l">رقم الهاتف :</td><td>{{ $candidate->phone ?? '---' }}</td><td class="l">رقم الجوال :</td><td>{{ $candidate->mobile }}</td></tr>
            <tr><td class="l">البريد الإلكتروني :</td><td colspan="3" style="color: var(--imperial-navy); font-weight: 600;">{{ $candidate->email }}</td></tr>
            <tr><td class="l">العنوان التفصيلي :</td><td colspan="3">{{ $candidate->address }}</td></tr>
        </table>

        {{-- 2. بيانات كتاب طلب التقييم الصادر عن الجامعة الخاصة --}}
        <div class="moz-section"><i class="fa-solid fa-file-signature me-1"></i> بيانات كتاب طلب التقييم الصادر عن الجامعة :</div>
        <table class="mt">
            <tr>
                <td class="l">الجامعة الخاصة الطالبة :</td>
                <td style="font-weight:bold; color: var(--imperial-navy);">{{ optional($application->workUniversity)->name ?? '---' }}</td>
                <td class="l">رقم وتاريخ الكتاب :</td>
                <td><strong>رقم:</strong> {{ $application->new_uni_request_no ?? '---' }} | <strong>بتاريخ:</strong> {{ format_sys_date($application->new_uni_request_date) }}</td>
            </tr>
        </table>

        {{-- 3. بيانات التعيين في الجامعة الحكومية السورية --}}
        <div class="moz-section"><i class="fa-solid fa-building-columns me-1"></i> بيانات التعيين والصفة بالجامعة الحكومية السورية :</div>
        <table class="mt">
            <tr>
                <td class="l">الجامعة الحكومية :</td>
                <td style="font-weight:bold; color: var(--imperial-navy);">{{ $govEd?->university?->name ?? $govEd?->university_other ?? '---' }}</td>
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

        {{-- 4. بيانات شهادة الدكتوراه (المؤهل الأساسي) --}}
        <div class="moz-section"><i class="fa-solid fa-graduation-cap me-1"></i> بيانات شهادة الدكتوراه (المؤهل العلمي الأساسي) :</div>
        <table class="mt">
            <tr>
                <td class="l">الجامعة المانحة للدكتوراه :</td>
                <td style="font-weight:bold; color: var(--imperial-navy);">{{ $phdEd?->university?->name ?? $phdEd?->university_other ?? '---' }}</td>
                <td class="l">تاريخ / سنة المنح :</td>
                <td>{{ format_sys_date(optional($phdEd)->grant_date) }}</td>
            </tr>
            <tr>
                <td class="l">الكلية المانحة :</td>
                <td>{{ optional($phdEd)->faculty ?: optional($phdEd)->general_specialization ?: '---' }}</td>
                <td class="l">القسم / الاختصاص الدقيق :</td>
                <td>{{ optional($phdEd)->department ?: optional($phdEd)->exact_specialization ?: '---' }}</td>
            </tr>
        </table>

    @elseif($isForeignMaster)
        {{-- =========================================================================
             CUSTOM MOZHAKKARA FOR FOREIGN MASTER'S (معادلة ماجستير خارجي)
        ========================================================================= --}}
        @php
            $residences = optional($masterEd)->residences ?? collect();
            $totalStayDays = 0;
            foreach ($residences as $r) {
                if ($r->entry_date && $r->exit_date) {
                    $in = \Carbon\Carbon::parse($r->entry_date);
                    $out = \Carbon\Carbon::parse($r->exit_date);
                    if ($out->gte($in)) {
                        $totalStayDays += $in->diffInDays($out);
                    }
                }
            }
            $stayYears = floor($totalStayDays / 365);
            $stayMonths = floor(($totalStayDays % 365) / 30);
            $stayDays = ($totalStayDays % 365) % 30;

            $hasExp = $application->has_previous_degree || str_contains($application->request_type, 'نظري') || (optional($masterEd)->experience_from_year);
        @endphp

        <div class="moz-title">(مذكرة العرض - تعادل ماجستير خارجي)</div>

        {{-- 1. البيانات الشخصية --}}
        <div class="moz-section"><i class="fa-solid fa-user me-1"></i> البيانات الشخصية للمرشح :</div>
        <table class="mt">
            <tr>
                <td class="l">نوع المعاملة :</td>
                <td style="font-weight:bold; color: var(--imperial-navy);">
                    {{ $application->request_type ?? 'تعادل ماجستير خارجي' }}
                </td>
                <td class="l">رقم المعاملة :</td>
                <td class="fw-bold">{{ $application->application_no ?? $candidate->id }}</td>
            </tr>
        </table>
        
        <div class="cname">اسم المرشح : {{ $candidate->full_name }}</div>
        
        <table class="mt">
            <tr><td class="l">الرقم الوطني :</td><td class="fw-bold">{{ $candidate->national_id }}</td><td class="l">الجنسية :</td><td>{{ $candidate->is_syrian ? 'سورية' : (optional($candidate->nationality)->name ?? 'سورية') }}</td></tr>
            <tr><td class="l">تاريخ الميلاد :</td><td>{{ format_sys_date($candidate->dob) }}</td><td class="l">الوظيفة / الصفة :</td><td>{{ $candidate->job_title ?? 'مرشح تعادل ماجستير خارجي' }}</td></tr>
            <tr><td class="l">رقم الهاتف :</td><td>{{ $candidate->phone ?? '---' }}</td><td class="l">رقم الجوال :</td><td>{{ $candidate->mobile }}</td></tr>
            <tr><td class="l">البريد الإلكتروني :</td><td colspan="3" style="color: var(--imperial-navy); font-weight: 600;">{{ $candidate->email }}</td></tr>
            <tr><td class="l">العنوان :</td><td colspan="3">{{ $candidate->address }}</td></tr>
        </table>

        {{-- 2. بيانات كتاب طلب التقييم الصادر عن الجامعة --}}
        <div class="moz-section"><i class="fa-solid fa-file-signature me-1"></i> بيانات كتاب طلب التقييم الصادر عن الجامعة :</div>
        <table class="mt">
            <tr>
                <td class="l">الجامعة الطالبة :</td>
                <td style="font-weight:bold; color: var(--imperial-navy);">{{ optional($application->workUniversity)->name ?? '---' }}</td>
                <td class="l">رقم وتاريخ الكتاب :</td>
                <td><strong>رقم:</strong> {{ $application->new_uni_request_no ?? '---' }} | <strong>بتاريخ:</strong> {{ format_sys_date($application->new_uni_request_date) }}</td>
            </tr>
            <tr>
                <td class="l">الكلية والفرع المطلوب :</td>
                <td colspan="3">{{ $application->work_faculty }} - {{ $application->work_department }}</td>
            </tr>
        </table>

        {{-- 3. الإجازة الجامعية الأولى (إن وجدت) - الثانوية العامة مستبعدة كما طُلب --}}
        @if($bachelorEd)
        <div class="moz-section"><i class="fa-solid fa-graduation-cap me-1"></i> الإجازة الجامعية الأولى :</div>
        <table class="mt">
            <tr>
                <td class="l">بلد المنح والجامعة :</td>
                <td>{{ optional($bachelorEd->country)->name }} - {{ optional($bachelorEd->university)->name ?? $bachelorEd->university_other }}</td>
                <td class="l">الكلية والفرع :</td>
                <td>{{ $bachelorEd->general_specialization ?: ($bachelorEd->faculty ?: '---') }} - {{ $bachelorEd->exact_specialization ?: $bachelorEd->department }}</td>
            </tr>
            <tr>
                <td class="l">تاريخ المنح والتقدير :</td>
                <td>{{ format_sys_date($bachelorEd->grant_date) }} | <strong>التقدير:</strong> {{ $bachelorEd->rank_or_grade }}</td>
                <td class="l">قرار المعادلة :</td>
                <td>{{ $bachelorEd->notes ?: ($bachelorEd->decision_no ? 'رقم: ' . $bachelorEd->decision_no : 'لا يوجد') }}</td>
            </tr>
        </table>
        @endif

        {{-- 4. درجة الماجستير الخارجي المراد تعادلها --}}
        <div class="moz-section"><i class="fa-solid fa-award me-1"></i> درجة الماجستير الخارجي المراد تعادلها :</div>
        @if($masterEd)
        <table class="mt">
            <tr>
                <td class="l">بلد الدراسة والجامعة :</td>
                <td style="font-weight:bold; color: var(--imperial-navy);">{{ optional($masterEd->country)->name ?? '---' }} - {{ $masterEd->university_other ?: (optional($masterEd->university)->name ?? '---') }}</td>
                <td class="l">الكلية والقسم :</td>
                <td>{{ $masterEd->faculty }} - {{ $masterEd->department ?: ($masterEd->general_specialization ?: '---') }}</td>
            </tr>
            <tr>
                <td class="l">الاختصاص الدقيق :</td>
                <td>{{ $masterEd->section_name ?: ($masterEd->exact_specialization ?: '---') }}</td>
                <td class="l">نظام ولغة الدراسة :</td>
                <td>{{ $masterEd->study_system ?: 'فصلي / سنوي' }} (لغة: {{ $masterEd->study_language ?: 'العربية' }})</td>
            </tr>
            <tr>
                <td class="l">تاريخ التسجيل والمناقشة :</td>
                <td><strong>تسجيل:</strong> {{ format_sys_date($masterEd->registration_date) }} | <strong>مناقشة:</strong> {{ format_sys_date($masterEd->defense_date) }}</td>
                <td class="l">تاريخ المنح والتقدير :</td>
                <td><strong>منح:</strong> {{ format_sys_date($masterEd->grant_date) }} | <strong>التقدير:</strong> {{ $masterEd->rank_or_grade }}</td>
            </tr>
            @if($masterEd->supervisor_name)
            <tr>
                <td class="l">الأستاذ المشرف :</td>
                <td colspan="3">{{ $masterEd->supervisor_name }}</td>
            </tr>
            @endif
            @if($masterEd->thesis_title)
            <tr>
                <td class="l">عنوان رسالة الماجستير :</td>
                <td colspan="3" style="font-weight: 600; color: var(--imperial-navy);">{{ $masterEd->thesis_title }}</td>
            </tr>
            @endif
            <tr>
                <td class="l">صفة الإيفاد الرسمي :</td>
                <td colspan="3">
                    @if($masterEd->envoy_decision)
                        <span class="badge bg-info-subtle text-info border px-2 py-1"><i class="fa-solid fa-plane me-1"></i> موفد بموجب قرار إيفاد رقم ({{ $masterEd->envoy_decision }}) تاريخ ({{ format_sys_date($masterEd->envoy_date) }})</span>
                    @else
                        <span>غير موفد (دراسة خاصة)</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="l">مسار تعادل الشهادة :</td>
                <td colspan="3">
                    @if($hasExp)
                        <span class="badge bg-primary-subtle text-primary border border-primary px-2 py-1">
                            <i class="fa-solid fa-book-open-reader me-1"></i> مسار نظري (خبرة سنتين تدريسية داخل سوريا: {{ $masterEd->notes ?: 'مرفق شهادة الخبرة' }} - يتطلب مقابلة علمية وفحص أهلية)
                        </span>
                    @else
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning px-2 py-1">
                            <i class="fa-solid fa-tools me-1"></i> مسار تطبيقي (عضو هيئة فنية - تدريس تطبيقي ومخبري بدون مقابلة)
                        </span>
                    @endif
                </td>
            </tr>
        </table>
        @endif

        {{-- 5. تفاصيل حركات الإقامة ببلد الدراسة ومدة الإقامة المحسوبة --}}
        <div class="moz-section d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span><i class="fa-solid fa-passport me-1"></i> تفصيل حركات الإقامة ببلد الدراسة (من واقع جواز السفر) :</span>
            <span class="badge bg-primary fs-8">
                إجمالي مدة الإقامة المحسوبة: {{ $stayYears }} سنة و {{ $stayMonths }} شهر و {{ $stayDays }} يوم
            </span>
        </div>

        @if($residences->count() > 0)
        <table class="table table-bordered text-center align-middle mb-3" style="font-size: 0.82rem; border-color: #cbd5e1;">
            <thead style="background-color: #f1f5f9; color: var(--imperial-navy); font-weight: bold;">
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 14%;">تاريخ الدخول</th>
                    <th style="width: 16%;">منفذ / مطار الدخول</th>
                    <th style="width: 14%;">تاريخ الخروج</th>
                    <th style="width: 16%;">منفذ / مطار الخروج</th>
                    <th style="width: 12%;">رقم الصفحة</th>
                    <th style="width: 23%;">مدة الإقامة المحسوبة</th>
                </tr>
            </thead>
            <tbody>
                @foreach($residences as $idx => $res)
                    @php
                        $inD = \Carbon\Carbon::parse($res->entry_date);
                        $outD = \Carbon\Carbon::parse($res->exit_date);
                        if ($inD && $outD && $outD->gte($inD)) {
                            $diff = $inD->diff($outD);
                            $y = $diff->y;
                            $m = $diff->m;
                            $d = $diff->d;
                        } else {
                            $y = 0; $m = 0; $d = 0;
                        }
                    @endphp
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td><strong>{{ format_sys_date($res->entry_date) }}</strong></td>
                        <td>{{ $res->entry_airport ?: '---' }}</td>
                        <td><strong>{{ format_sys_date($res->exit_date) }}</strong></td>
                        <td>{{ $res->exit_airport ?: '---' }}</td>
                        <td><span class="badge bg-secondary">{{ $res->page_number ?: '---' }}</span></td>
                        <td><span class="badge bg-success-subtle text-success border border-success">{{ $y }} سنة و {{ $m }} شهر و {{ $d }} يوم</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="wblock text-muted">لا توجد حركات إقامة مسجلة لهذه المعاملة.</div>
        @endif

    @else
        {{-- =========================================================================
             STANDARD MOZHAKKARA FOR MASTERS / PHD / EQUIVALENCE REQUESTS
        ========================================================================= --}}
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
            <tr>
                <td class="l">بلد المنح :</td>
                <td>{{ optional($highSchoolEd->country)->name ?? 'سوريا' }}</td>
                <td class="l">نوع الشهادة :</td>
                <td>{{ $highSchoolEd->section_name ?: ($highSchoolEd->general_specialization ?: ($highSchoolEd->type_or_faculty ?: 'علمي')) }}</td>
            </tr>
            <tr>
                <td class="l">تاريخ المنح :</td>
                <td>{{ format_sys_date($highSchoolEd->grant_date) }}</td>
                <td class="l">قرار المعادلة :</td>
                <td>{{ $highSchoolEd->decision_no ?? 'لا يوجد' }}</td>
            </tr>
        </table>
        @else
        <div class="wblock text-muted">لا توجد بيانات مسجلة للشهادة الثانوية.</div>
        @endif

        {{-- 3. الإجازة الجامعية --}}
        <div class="moz-section"><i class="fa-solid fa-university me-1"></i> الإجازة الجامعية :</div>
        @if($bachelorEd)
        <table class="mt">
            <tr>
                <td class="l">بلد المنح :</td>
                <td>{{ optional($bachelorEd->country)->name }}</td>
                <td class="l">الجامعة :</td>
                <td>{{ optional($bachelorEd->university)->name ?? $bachelorEd->university_other }}</td>
            </tr>
            <tr>
                <td class="l">الكلية والفرع :</td>
                <td>
                    {{ $bachelorEd->general_specialization ?: ($bachelorEd->faculty ?: '---') }}
                    @if($bachelorEd->exact_specialization || $bachelorEd->department)
                        - {{ $bachelorEd->exact_specialization ?: $bachelorEd->department }}
                    @endif
                    @if($bachelorEd->section_name && $bachelorEd->section_name !== ($bachelorEd->exact_specialization ?: $bachelorEd->department))
                        ({{ $bachelorEd->section_name }})
                    @endif
                </td>
                <td class="l">تاريخ التسجيل :</td>
                <td>{{ format_sys_date($bachelorEd->registration_date) }}</td>
            </tr>
            <tr>
                <td class="l">تاريخ المنح :</td>
                <td>{{ format_sys_date($bachelorEd->grant_date) }}</td>
                <td class="l">التقدير/المعدل :</td>
                <td>{{ $bachelorEd->rank_or_grade }}</td>
            </tr>
        </table>
        @else
        <div class="wblock text-muted">لا توجد بيانات مسجلة للإجازة الجامعية.</div>
        @endif

        {{-- 4. الدبلوم --}}
        @if($diplomaEd)
        <div class="moz-section"><i class="fa-solid fa-certificate me-1"></i> دبلوم الدراسات العليا :</div>
        <table class="mt">
            <tr>
                <td class="l">الجامعة والكلية :</td>
                <td>{{ optional($diplomaEd->university)->name }} - {{ $diplomaEd->general_specialization ?: $diplomaEd->faculty }}</td>
                <td class="l">التخصص :</td>
                <td>{{ $diplomaEd->exact_specialization ?: ($diplomaEd->department ?: ($diplomaEd->section_name ?: '---')) }}</td>
            </tr>
            <tr>
                <td class="l">تاريخ المنح :</td>
                <td>{{ format_sys_date($diplomaEd->grant_date) }}</td>
                <td class="l">التقدير/المعدل :</td>
                <td>{{ $diplomaEd->rank_or_grade }}</td>
            </tr>
        </table>
        @endif

        {{-- 5. الماجستير --}}
        @if($masterEd)
        <div class="moz-section"><i class="fa-solid fa-scroll me-1"></i> درجة الماجستير المراد تعادلها :</div>
        <table class="mt">
            <tr>
                <td class="l">الجامعة والكلية :</td>
                <td>
                    {{ optional($masterEd->university)->name ?? ($masterEd->university_other ?? '---') }}
                    @if($masterEd->general_specialization || $masterEd->faculty)
                        - {{ $masterEd->general_specialization ?: $masterEd->faculty }}
                    @endif
                </td>
                <td class="l">القسم والفرع :</td>
                <td>
                    {{ $masterEd->exact_specialization ?: ($masterEd->department ?: ($masterEd->section_name ?: '---')) }}
                    @if($masterEd->section_name && $masterEd->section_name !== ($masterEd->exact_specialization ?: $masterEd->department))
                        ({{ $masterEd->section_name }})
                    @endif
                </td>
            </tr>
            <tr>
                <td class="l">تاريخ التسجيل :</td>
                <td>{{ format_sys_date($masterEd->registration_date) }}</td>
                <td class="l">تاريخ المناقشة :</td>
                <td>{{ format_sys_date($masterEd->defense_date) }}</td>
            </tr>
            <tr>
                <td class="l">تاريخ المنح :</td>
                <td>{{ format_sys_date($masterEd->grant_date) }}</td>
                <td class="l">التقدير :</td>
                <td>{{ $masterEd->rank_or_grade }}</td>
            </tr>
            <tr>
                <td class="l">الأستاذ المشرف :</td>
                <td colspan="3">{{ $masterEd->supervisor_name ?? '---' }}</td>
            </tr>
            <tr>
                <td class="l">عنوان الرسالة :</td>
                <td colspan="3" style="font-weight: 600; color: var(--imperial-navy);">{{ $masterEd->thesis_title ?? '---' }}</td>
            </tr>
        </table>
        @endif

        {{-- 6. الدكتوراه --}}
        @if($phdEd)
        <div class="moz-section"><i class="fa-solid fa-award me-1"></i> درجة الدكتوراه المراد تعادلها :</div>
        <table class="mt">
            <tr>
                <td class="l">الجامعة والكلية :</td>
                <td>
                    {{ optional($phdEd->university)->name ?? ($phdEd->university_other ?? '---') }}
                    @if($phdEd->general_specialization || $phdEd->faculty)
                        - {{ $phdEd->general_specialization ?: $phdEd->faculty }}
                    @endif
                </td>
                <td class="l">القسم والفرع :</td>
                <td>
                    {{ $phdEd->exact_specialization ?: ($phdEd->department ?: ($phdEd->section_name ?: '---')) }}
                    @if($phdEd->section_name && $phdEd->section_name !== ($phdEd->exact_specialization ?: $phdEd->department))
                        ({{ $phdEd->section_name }})
                    @endif
                </td>
            </tr>
            @if($phdEd->registration_date || $phdEd->defense_date)
            <tr>
                <td class="l">تاريخ التسجيل :</td>
                <td>{{ format_sys_date($phdEd->registration_date) }}</td>
                <td class="l">تاريخ المناقشة :</td>
                <td>{{ format_sys_date($phdEd->defense_date) }}</td>
            </tr>
            @endif
            <tr>
                <td class="l">تاريخ المنح :</td>
                <td>{{ format_sys_date($phdEd->grant_date) }}</td>
                <td class="l">التقدير :</td>
                <td>{{ $phdEd->rank_or_grade }}</td>
            </tr>
            @if($phdEd->supervisor_name)
            <tr>
                <td class="l">الأستاذ المشرف :</td>
                <td colspan="3">{{ $phdEd->supervisor_name }}</td>
            </tr>
            @endif
            @if($phdEd->thesis_title)
            <tr>
                <td class="l">عنوان الأطروحة :</td>
                <td colspan="3" style="font-weight: 600; color: var(--imperial-navy);">{{ $phdEd->thesis_title }}</td>
            </tr>
            @endif
        </table>
        @endif

        {{-- 7. بيانات التكليف والجامعة المطلوب التعادل لصالحها --}}
        <div class="moz-section"><i class="fa-solid fa-building-columns me-1"></i> بيانات التكليف والجامعة المطلوبة :</div>
        <table class="mt">
            <tr><td class="l">الجامعة المعنية :</td><td style="font-weight:bold; color: var(--imperial-navy);">{{ optional($application->workUniversity)->name }}</td><td class="l">الكلية والفرع :</td><td>{{ $application->work_faculty }} - {{ $application->work_department }}</td></tr>
        </table>
    @endif


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
