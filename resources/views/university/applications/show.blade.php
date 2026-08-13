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
                                <h6 class="mb-0 fs-8 fw-bold text-dark text-truncate" title="{{ optional($att->attachmentType)->name }}">
                                    {{ optional($att->attachmentType)->name ?? 'وثيقة مصدقة' }}
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
