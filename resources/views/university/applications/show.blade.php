@extends('layouts.university')

@section('title', 'تفاصيل معاملة التعادل رقم (' . ($application->application_no ?? $application->id) . ')')

@section('content')

<!-- Header Breadcrumb & Actions -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h1 class="h4 fw-bold mb-1" style="color: var(--imperial-navy);">
            <i class="fa-solid fa-file-invoice me-2" style="color: var(--heritage-gold);"></i>
            تفاصيل معاملة التعادل رقم ({{ $application->application_no ?? '#' . $application->id }})
        </h1>
        <p class="text-muted small mb-0">
            المرشح: <strong>{{ optional($candidate)->full_name ?? 'غ/م' }}</strong> | الكلية: {{ $application->work_faculty }} ({{ $application->work_department }})
        </p>
    </div>
    <div class="d-flex align-items-center gap-2">
        {{-- 1. تحميل وتنزيل مذكرة العرض الرسمية مباشرة --}}
        <a href="{{ route('university.applications.download_pdf', $application->id) }}" class="btn btn-sm btn-outline-danger shadow-sm p-2" title="تنزيل وتحميل مذكرة التعادل الرسمية مباشرة (PDF)">
            <i class="fa-solid fa-file-pdf fs-5"></i>
        </a>

        {{-- 2. طباعة المرفقات والوثائق المدمجة الكاملة --}}
        <a href="{{ route('university.applications.download_consolidated_pdf', $application->id) }}" target="_blank" class="btn btn-sm btn-outline-gold shadow-sm p-2" title="طباعة وتحميل المرفقات والوثائق المدمجة (PDF)">
            <i class="fa-solid fa-file-lines fs-5"></i>
        </a>

        {{-- 3. العودة للوحة التحكم --}}
        <a href="{{ route('university.dashboard') }}" class="btn btn-sm btn-outline-navy shadow-sm p-2" title="العودة للوحة التحكم الرئيسية">
            <i class="fa-solid fa-arrow-right fs-5"></i>
        </a>
    </div>
</div>

@php
    $isForeignMaster = str_contains($application->request_type ?? '', 'ماجستير خارجي') || str_contains($application->request_type ?? '', 'خارجي');
@endphp

@if($isForeignMaster)
    {{-- =========================================================================
         FOREIGN MASTER'S EQUIVALENCE DETAILS VIEW (مطابق لتنسيق وتوزيع صفحة المراجعة)
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

    <div class="row g-4 mb-4">
        <!-- 1. البيانات الشخصية وبيانات الترشيح (Right Column) -->
        <div class="col-lg-6">
            <div class="card p-4 shadow-sm border-0 h-100" style="border-top: 3.5px solid var(--heritage-gold) !important; background: #ffffff;">
                <h5 class="fw-bold mb-3 pb-2 border-bottom d-flex align-items-center justify-content-between" style="color: var(--imperial-navy);">
                    <span><i class="fa-solid fa-user me-2 text-warning"></i> 1. البيانات الشخصية وبيانات الترشيح</span>
                    <span class="badge bg-light text-dark border fs-8">{{ $application->application_no ?? '#' . $application->id }}</span>
                </h5>
                <div class="row g-3 fs-7">
                    <div class="col-sm-6">
                        <span class="text-muted d-block fs-8">الاسم والكنية:</span>
                        <strong style="color: var(--imperial-navy);">{{ optional($candidate)->full_name ?? '---' }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block fs-8">اسم الأب:</span>
                        <strong>{{ optional($candidate)->father_name ?? '---' }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block fs-8">اسم الأم:</span>
                        <strong>{{ optional($candidate)->mother_name ?? '---' }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block fs-8">الرقم الوطني:</span>
                        <strong>{{ optional($candidate)->national_id ?? '---' }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block fs-8">الجنسية:</span>
                        <strong>{{ optional(optional($candidate)->nationality)->name ?? 'سورية' }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block fs-8">رقم الموبايل:</span>
                        <strong dir="ltr">{{ optional($candidate)->mobile ?? '---' }}</strong>
                    </div>
                    <div class="col-12">
                        <span class="text-muted d-block fs-8">البريد الإلكتروني:</span>
                        <strong style="color: var(--imperial-navy);">{{ optional($candidate)->email ?? '---' }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block fs-8">رقم كتاب الجامعة:</span>
                        <strong>{{ $application->new_uni_request_no ?? '---' }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block fs-8">تاريخ كتاب الجامعة:</span>
                        <strong>{{ format_sys_date($application->new_uni_request_date) }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. درجة الماجستير الخارجي والمسار (Left Column) -->
        <div class="col-lg-6">
            <div class="card p-4 shadow-sm border-0 h-100" style="border-top: 3.5px solid var(--imperial-navy) !important; background: #ffffff;">
                <h5 class="fw-bold mb-3 pb-2 border-bottom d-flex align-items-center justify-content-between" style="color: var(--imperial-navy);">
                    <span><i class="fa-solid fa-award me-2 text-warning"></i> 2. درجة الماجستير الخارجي والمسار</span>
                    @if($application->status == 'تم الصدور' || $application->status == 'موافقة')
                        <span class="badge bg-success-subtle text-success border border-success px-2.5 py-1 fw-bold fs-8"><i class="fa-solid fa-circle-check me-1"></i> {{ $application->status }}</span>
                    @elseif($application->status == 'مسودة')
                        <span class="badge bg-warning-subtle text-warning border border-warning px-2.5 py-1 fw-bold fs-8"><i class="fa-solid fa-pen me-1"></i> مسودة</span>
                    @else
                        <span class="badge bg-primary-subtle text-primary border border-primary px-2.5 py-1 fw-bold fs-8"><i class="fa-solid fa-clock me-1"></i> {{ $application->status }}</span>
                    @endif
                </h5>
                <div class="row g-3 fs-7">
                    <div class="col-sm-6">
                        <span class="text-muted d-block fs-8">بلد الدراسة:</span>
                        <strong>{{ optional(optional($masterEd)->country)->name ?? (optional($masterEd)->university_other ?? '---') }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block fs-8">الجامعة المانحة:</span>
                        <strong>{{ optional($masterEd)->university_other ?: (optional(optional($masterEd)->university)->name ?? '---') }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block fs-8">الكلية:</span>
                        <strong>{{ optional($masterEd)->faculty ?? '---' }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block fs-8">القسم / الاختصاص العام:</span>
                        <strong>{{ optional($masterEd)->department ?: (optional($masterEd)->general_specialization ?: '---') }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block fs-8">الاختصاص الدقيق:</span>
                        <strong>{{ optional($masterEd)->section_name ?: (optional($masterEd)->exact_specialization ?: '---') }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block fs-8">تاريخ المنح:</span>
                        <strong>{{ format_sys_date(optional($masterEd)->grant_date) }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block fs-8">المعدل / التقدير:</span>
                        <strong>{{ optional($masterEd)->rank ?? '---' }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block fs-8">إيفاد رسمي:</span>
                        @if(optional($masterEd)->envoy_decision)
                            <span class="badge bg-info-subtle text-info border"><i class="fa-solid fa-plane me-1"></i> موفد: {{ $masterEd->envoy_decision }} ({{ format_sys_date($masterEd->envoy_date) }})</span>
                        @else
                            <span class="text-muted">غير موفد</span>
                        @endif
                    </div>
                    @if(optional($masterEd)->thesis_title)
                    <div class="col-12">
                        <span class="text-muted d-block fs-8">عنوان الرسالة / الأطروحة:</span>
                        <strong style="color: var(--imperial-navy);">{{ optional($masterEd)->thesis_title }}</strong>
                    </div>
                    @endif
                    <div class="col-12">
                        <span class="text-muted d-block fs-8 mb-1">مسار الطلب:</span>
                        @if($hasExp)
                            <span class="badge bg-primary-subtle text-primary border border-primary fs-7 py-1 px-3">
                                <i class="fa-solid fa-book-open-reader me-1"></i> ماجستير خارجي نظري (خبرة سنتين داخل سوريا - يتطلب مقابلة وأهلية)
                            </span>
                        @else
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning fs-7 py-1 px-3">
                                <i class="fa-solid fa-tools me-1"></i> ماجستير خارجي تطبيقي (عضو هيئة فنية - تدريس تطبيقي بدون مقابلة)
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. ملخص مدة الإقامة ببلد الدراسة (Full Width) -->
        <div class="col-12">
            <div class="card p-4 shadow-sm border-0" style="background: #ffffff; border-top: 3.5px solid #0d6efd !important;">
                <h5 class="fw-bold mb-3 pb-2 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2" style="color: var(--imperial-navy);">
                    <span><i class="fa-solid fa-passport me-2 text-warning"></i> 3. ملخص مدة الإقامة ببلد الدراسة</span>
                    <span class="badge bg-primary fs-7 py-1.5 px-3">
                        إجمالي مدة الإقامة المحسوبة: {{ $stayYears }} سنة و {{ $stayMonths }} شهر و {{ $stayDays }} يوم
                    </span>
                </h5>
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 fs-7">
                    <div><strong>عدد حركات الإقامة المسجلة:</strong> <span class="badge bg-secondary fs-7">{{ $residences->count() }} حركات</span></div>
                </div>

                @if($residences->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle text-center mb-0" style="font-size: 0.85rem;">
                        <thead class="table-light fw-bold" style="color: var(--imperial-navy);">
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
                </div>
                @else
                <div class="text-center py-3 text-muted">لم يتم تسجيل أي حركات إقامة بعد.</div>
                @endif
            </div>
        </div>

        @if($bachelorEd)
        <!-- درجة الإجازة الجامعية الأولى -->
        <div class="col-12">
            <div class="card p-4 shadow-sm border-0" style="background: #ffffff;">
                <h5 class="fw-bold mb-3 pb-2 border-bottom" style="color: var(--imperial-navy);">
                    <i class="fa-solid fa-graduation-cap me-2 text-success"></i> بيانات الإجازة الجامعية الأولى (البكالوريوس)
                </h5>
                <div class="row g-3 fs-7">
                    <div class="col-sm-4"><span class="text-muted d-block fs-8">بلد الإجازة:</span><strong>{{ optional($bachelorEd->country)->name ?? '---' }}</strong></div>
                    <div class="col-sm-4"><span class="text-muted d-block fs-8">الجامعة والكلية:</span><strong>{{ $bachelorEd->faculty }} ({{ optional($bachelorEd->university)->name ?? ($bachelorEd->university_other ?? '---') }})</strong></div>
                    <div class="col-sm-4"><span class="text-muted d-block fs-8">التقدير / المعدل:</span><strong>{{ $bachelorEd->rank ?? '---' }}</strong></div>
                    <div class="col-sm-4"><span class="text-muted d-block fs-8">تاريخ المنح:</span><strong>{{ format_sys_date($bachelorEd->grant_date) }}</strong></div>
                    <div class="col-sm-8"><span class="text-muted d-block fs-8">ملاحظات وقرار المعادلة:</span><strong>{{ $bachelorEd->notes ?: 'لا يوجد' }}</strong></div>
                </div>
            </div>
        </div>
        @endif
    </div>

@else
    {{-- =========================================================================
         STANDARD APPLICATION OVERVIEW (OTHER REQUEST TYPES)
    ========================================================================= --}}
    <!-- Main Application Overview Cards -->
    <div class="row g-4 mb-4">
        <!-- Candidate Info Card -->
        <div class="col-lg-6">
            <div class="card p-4 shadow-sm border-0 h-100" style="border-top: 3.5px solid var(--heritage-gold) !important; background: #ffffff;">
                <h5 class="fw-bold mb-3 pb-2 border-bottom d-flex align-items-center justify-content-between" style="color: var(--imperial-navy);">
                    <span><i class="fa-solid fa-user-check me-2" style="color: var(--heritage-gold);"></i>1. البيانات الشخصية للمرشح</span>
                    <span class="badge bg-light text-dark border fs-8">{{ $application->request_type }}</span>
                </h5>
                <div class="row g-3 fs-7">
                    <div class="col-sm-6">
                        <span class="text-muted d-block fs-8">الاسم والكنية الكاملة:</span>
                        <strong style="color: var(--imperial-navy);">{{ optional($candidate)->full_name ?? 'غ/م' }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block fs-8">اسم الأب والأم:</span>
                        <strong>{{ optional($candidate)->father_name ?? 'غ/م' }} / {{ optional($candidate)->mother_name ?? 'غ/م' }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block fs-8">الرقم الوطني / الجنسية:</span>
                        <strong>{{ optional($candidate)->national_id ?? 'غ/م' }} ({{ optional(optional($candidate)->nationality)->name ?? 'سوري' }})</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block fs-8">تاريخ الميلاد والوظيفة:</span>
                        <strong>{{ format_sys_date(optional($candidate)->dob) }} | {{ optional($candidate)->job_title ?? 'غ/م' }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block fs-8">رقم الهاتف المحمول:</span>
                        <strong dir="ltr">{{ optional($candidate)->mobile ?? 'غ/م' }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block fs-8">البريد الإلكتروني والعنوان:</span>
                        <strong>{{ optional($candidate)->email ?? 'غ/م' }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Application Status & Details Card -->
        <div class="col-lg-6">
            <div class="card p-4 shadow-sm border-0 h-100" style="border-top: 3.5px solid var(--imperial-navy) !important; background: #ffffff;">
                <h5 class="fw-bold mb-3 pb-2 border-bottom d-flex align-items-center justify-content-between" style="color: var(--imperial-navy);">
                    <span><i class="fa-solid fa-graduation-cap me-2" style="color: var(--heritage-gold);"></i>2. حالة وتفاصيل الطلب</span>
                    @if($application->status == 'تم الصدور' || $application->status == 'موافقة')
                        <span class="badge bg-success-subtle text-success border border-success px-3 py-1 fw-bold fs-7"><i class="fa-solid fa-circle-check me-1"></i> {{ $application->status }}</span>
                    @elseif($application->status == 'مسودة')
                        <span class="badge bg-warning-subtle text-warning border border-warning px-3 py-1 fw-bold fs-7"><i class="fa-solid fa-pen me-1"></i> مسودة (غير مكتمل)</span>
                    @else
                        <span class="badge bg-primary-subtle text-primary border border-primary px-3 py-1 fw-bold fs-7"><i class="fa-solid fa-clock me-1"></i> {{ $application->status }}</span>
                    @endif
                </h5>
                <div class="row g-3 fs-7">
                    <div class="col-sm-6">
                        <span class="text-muted d-block fs-8">الكلية والفرع المستهدف:</span>
                        <strong>{{ $application->work_faculty }} - {{ $application->work_department }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block fs-8">الجامعة المرفوعة منها:</span>
                        <strong>{{ optional($application->workUniversity)->name ?? 'الجامعة الخاصة' }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block fs-8">نظام الدراسة والخبرة:</span>
                        <strong>نظام {{ $application->study_system ?? 'فصلي' }} {{ $application->has_previous_degree ? '(توجد خبرة أكثر من سنتين)' : '' }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block fs-8">تاريخ تقديم الطلب:</span>
                        <strong>{{ format_sys_date($application->created_at, true) }}</strong>
                    </div>
                </div>
                
                @if($application->status == 'بانتظار الوثائق' || $application->status == 'مسودة')
                <div class="mt-4 pt-3 border-top d-flex justify-content-end">
                    <a href="{{ route('university.applications.edit', $application->id) }}" class="btn btn-warning btn-sm fw-bold">
                        <i class="fa-solid fa-pen-to-square me-1"></i> استكمال البيانات والوثائق الناقصة
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Academic Qualifications Details Section -->
    <div class="card p-4 shadow-sm border-0 mb-4" style="background-color: #ffffff;">
        <h5 class="fw-bold mb-3 pb-2 border-bottom" style="color: var(--imperial-navy);">
            <i class="fa-solid fa-scroll me-2" style="color: var(--heritage-gold);"></i>3. الشهادات والمؤهلات العلمية المسجلة
        </h5>
        
        <div class="row g-4">
            @if($highSchoolEd)
            <div class="col-md-6 col-lg-3">
                <div class="p-3 rounded border bg-light h-100">
                    <h6 class="fw-bold text-primary mb-2"><i class="fa-solid fa-school me-1"></i> الثانوية العامة</h6>
                    <div class="fs-8 text-muted">الدولة: <strong>{{ optional($highSchoolEd->country)->name ?? 'غ/م' }}</strong></div>
                    <div class="fs-8 text-muted">الفرع: <strong>{{ $highSchoolEd->hs_type ?? 'علمي' }}</strong></div>
                    <div class="fs-8 text-muted">سنة الحصول: <strong>{{ format_sys_date($highSchoolEd->grant_date) }}</strong></div>
                </div>
            </div>
            @endif

            @if($bachelorEd)
            <div class="col-md-6 col-lg-3">
                <div class="p-3 rounded border bg-light h-100">
                    <h6 class="fw-bold text-success mb-2"><i class="fa-solid fa-graduation-cap me-1"></i> الإجازة الجامعية (البكالوريوس)</h6>
                    <div class="fs-8 text-muted">الكلية والجامعة: <strong>{{ $bachelorEd->faculty }} ({{ optional($bachelorEd->university)->name ?? $bachelorEd->university_other ?? 'غ/م' }})</strong></div>
                    <div class="fs-8 text-muted">التقدير/المرتبة: <strong>{{ $bachelorEd->rank ?? 'غ/م' }}</strong></div>
                    <div class="fs-8 text-muted">سنة التخرج: <strong>{{ format_sys_date($bachelorEd->grant_date) }}</strong></div>
                </div>
            </div>
            @endif

            @if($masterEd)
            <div class="col-md-6 col-lg-3">
                <div class="p-3 rounded border bg-light h-100">
                    <h6 class="fw-bold text-warning mb-2"><i class="fa-solid fa-award me-1"></i> درجة الماجستير</h6>
                    <div class="fs-8 text-muted">الجامعة والكلية: <strong>{{ optional($masterEd->university)->name ?? 'غ/م' }} - {{ $masterEd->faculty }}</strong></div>
                    <div class="fs-8 text-muted">التقدير والمشرف: <strong>{{ $masterEd->rank }} | أشراف: {{ $masterEd->supervisor_name }}</strong></div>
                    <div class="fs-8 text-muted">تاريخ منح الدرجة: <strong>{{ format_sys_date($masterEd->grant_date) }}</strong></div>
                    <div class="fs-8 text-muted mt-1 text-truncate" title="{{ $masterEd->thesis_title }}">العنوان: <strong>{{ $masterEd->thesis_title }}</strong></div>
                </div>
            </div>
            @endif

            @if($phdEd)
            <div class="col-md-6 col-lg-3">
                <div class="p-3 rounded border bg-light h-100">
                    <h6 class="fw-bold text-danger mb-2"><i class="fa-solid fa-user-graduate me-1"></i> درجة الدكتوراه</h6>
                    <div class="fs-8 text-muted">الجامعة والكلية: <strong>{{ optional($phdEd->university)->name ?? 'غ/م' }} - {{ $phdEd->faculty }}</strong></div>
                    <div class="fs-8 text-muted">التقدير: <strong>{{ $phdEd->rank }}</strong></div>
                    <div class="fs-8 text-muted">تاريخ منح الدرجة: <strong>{{ format_sys_date($phdEd->grant_date) }}</strong></div>
                </div>
            </div>
            @endif
        </div>
    </div>
@endif

<!-- Uploaded PDF Attachments Section -->
<div class="card p-4 shadow-sm border-0 mb-4" style="background-color: #ffffff;">
    <h5 class="fw-bold mb-3 pb-2 border-bottom" style="color: var(--imperial-navy);">
        <i class="fa-solid fa-paperclip me-2" style="color: var(--heritage-gold);"></i>4. الوثائق والمرفقات المرفوعة مع المعاملة
    </h5>
    
    <div class="row g-3">
        @php
            $hasAttachments = false;
        @endphp
        @foreach($application->educations as $edu)
            @foreach($edu->attachments as $att)
                @php $hasAttachments = true; @endphp
                <div class="col-md-6 col-lg-4">
                    <div class="d-flex align-items-center justify-content-between p-3 border rounded bg-light">
                        <div class="d-flex align-items-center gap-2 overflow-hidden me-2">
                            <i class="fa-solid fa-file-pdf text-danger fs-3"></i>
                            <div class="overflow-hidden">
                                <h6 class="mb-0 fs-8 fw-bold text-dark text-truncate" title="{{ $att->notes ?: (optional($att->attachmentType)->name ?? 'وثيقة مصدقة') }}">
                                    {{ $att->notes ?: (optional($att->attachmentType)->name ?? 'وثيقة مصدقة') }}
                                </h6>
                                <span class="fs-9 text-muted d-block">{{ format_sys_date($att->created_at) }}</span>
                            </div>
                        </div>
                        <a href="{{ Storage::url($att->file_path) }}" target="_blank" class="btn btn-sm btn-outline-info p-2 shadow-sm" title="استعراض الوثيقة المصدقة (PDF)">
                            <i class="fa-solid fa-eye fs-6"></i>
                        </a>
                    </div>
                </div>
            @endforeach
        @endforeach

        @if(!$hasAttachments)
        <div class="col-12 text-center py-4 text-muted">
            <i class="fa-solid fa-folder-open fs-2 mb-2 d-block text-secondary"></i>
            لا توجد وثائق مرفوعة لهذه المعاملة حالياً.
        </div>
        @endif
    </div>
</div>



@endsection
