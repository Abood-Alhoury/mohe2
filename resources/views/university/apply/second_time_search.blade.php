@extends('layouts.university')

@section('title', 'تعادل للمرة الثانية أو أكثر - البحث بالرقم الوطني')

@section('content')

<!-- Header Breadcrumb & Title -->
<div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('university.dashboard') }}" class="text-decoration-none text-muted">لوحة التحكم</a></li>
                <li class="breadcrumb-item"><a href="{{ route('university.apply.options') }}" class="text-decoration-none text-muted">خيارات التعادل</a></li>
                <li class="breadcrumb-item active text-primary fw-bold" aria-current="page">تعادل للمرة الثانية أو أكثر</li>
            </ol>
        </nav>
        <h3 class="fw-bold mb-0" style="color: var(--imperial-navy);">
            <i class="fa-solid fa-repeat me-2" style="color: var(--heritage-gold);"></i>
            تقديم طلب تعادل للمرة الثانية أو أكثر للمرشح
        </h3>
    </div>
</div>

<!-- Institutional Informational Hero Card -->
<div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #1A2A44 0%, #0F1A2C 100%); border-radius: 14px; border-right: 5px solid var(--heritage-gold) !important;">
    <div class="card-body p-4 text-white">
        <div class="row align-items-center">
            <div class="col-md-9">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-warning text-dark px-3 py-1 fw-bold" style="border-radius: 6px;">خدمة التعادل المكرر</span>
                    <span class="text-white-50 small"><i class="fa-solid fa-circle-info me-1"></i> الكوادر التدريسية والجامعية</span>
                </div>
                <h4 class="fw-bold mb-2 text-white">البحث عن سجلات المرشح بالرقم الوطني</h4>
                <p class="text-white-50 mb-0 leading-relaxed" style="font-size: 0.95rem;">
                    تُستخدم هذه الخدمة للمرشحين والأساتذة الذين لديهم طلب أو قرار تعادل سابق في النظام، والذين يرغبون بتقديم **طلب تعادل للمرة الثانية أو أكثر** (مثل تعادل درجة علمية جديدة أو تخصص جديد).
                    الرجاء إدخال **الرقم الوطني للمرشح** لاسترجاع بياناته ومؤهلاته السابقة تلقائياً دون الحاجة لإعادة إدخالها.
                </p>
            </div>
            <div class="col-md-3 text-center d-none d-md-block">
                <div class="p-3 d-inline-block rounded-circle" style="background: rgba(197,160,89,0.15); border: 2px solid var(--heritage-gold);">
                    <i class="fa-solid fa-repeat text-warning" style="font-size: 3rem;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search Form Section -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 14px; border-top: 4px solid var(--imperial-navy) !important;">
    <div class="card-body p-4">
        <form action="{{ route('university.apply.second_time') }}" method="GET">
            <div class="row align-items-center g-3">
                <div class="col-md-8">
                    <label for="national_id" class="form-label fw-bold" style="color: var(--imperial-navy);">
                        <i class="fa-solid fa-id-card text-primary me-1"></i> الرقم الوطني للمرشح (National ID):
                    </label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" 
                               name="national_id" 
                               id="national_id" 
                               class="form-control border-start-0 fs-5 fw-bold" 
                               placeholder="أدخل الرقم الوطني المكون من 11 رقم (مثال: 01010012345)" 
                               value="{{ $searchNationalId }}"
                               required
                               autofocus>
                    </div>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm py-2.5" style="background: var(--imperial-navy); border-color: var(--imperial-navy);">
                        <i class="fa-solid fa-search me-1"></i> البحث واسترجاع البيانات
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Search Results Section -->
@if(!empty($searchNationalId))
    @if($candidate)
        <!-- Candidate Profile Found Card -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px; overflow: hidden; border-top: 4px solid #10B981 !important;">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-success">
                    <i class="fa-solid fa-user-check me-2"></i>
                    تم العثور على سجل المرشح في النظام:
                </h5>
                <span class="badge bg-success-subtle text-success border border-success px-3 py-1 fw-bold">
                    <i class="fa-solid fa-folder-tree me-1"></i> إجمالي الطلبات السابقة: {{ $previousApps->count() }} طلبات
                </span>
            </div>
            <div class="card-body p-4" style="background: #F0FDF4;">
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="p-3 bg-white rounded border shadow-2xs">
                            <span class="text-muted d-block fs-8 mb-1">الاسم الكامل للمرشح:</span>
                            <strong class="fs-6" style="color: var(--imperial-navy);">{{ $candidate->full_name }}</strong>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-white rounded border shadow-2xs">
                            <span class="text-muted d-block fs-8 mb-1">الرقم الوطني:</span>
                            <strong class="fs-6 font-monospace" style="color: var(--imperial-navy);">{{ $candidate->national_id }}</strong>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-white rounded border shadow-2xs">
                            <span class="text-muted d-block fs-8 mb-1">الجنسية والنوع الاجتماعي:</span>
                            <strong class="fs-6">{{ optional($candidate->nationality)->name ?? 'سوري' }} ({{ $candidate->gender ?? 'ذكر' }})</strong>
                        </div>
                    </div>
                </div>

                <!-- Previous Applications List -->
                @if($previousApps->count() > 0)
                    <h6 class="fw-bold mb-2" style="color: var(--imperial-navy);">
                        <i class="fa-solid fa-clock-rotate-left me-1"></i> المعاملات السابقة المسجلة للمرشح:
                    </h6>
                    <div class="row g-2 mb-4">
                        @foreach($previousApps as $pApp)
                            <div class="col-md-6">
                                <div class="p-2.5 bg-white rounded border d-flex justify-content-between align-items-center fs-8">
                                    <div>
                                        <strong class="text-dark d-block">#{{ $pApp->application_no }} - {{ $pApp->request_type }}</strong>
                                        <span class="text-muted"><i class="fa-solid fa-building-columns me-1"></i> {{ optional($pApp->workUniversity)->name }}</span>
                                    </div>
                                    <span class="badge bg-light text-dark border">{{ $pApp->status }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Continue Action Button -->
                <div class="p-3 bg-white rounded border d-flex justify-content-between align-items-center flex-wrap gap-3" style="border-right: 4px solid var(--heritage-gold) !important;">
                    <div>
                        <h6 class="fw-bold mb-1" style="color: var(--imperial-navy);">جاهز لتقديم طلب تعادل للمرة الثانية أو أكثر لهذا المرشح؟</h6>
                        <small class="text-muted">سيتم جلب وتثبيت بيانات المرشح الشخصية ومؤهلاته السابقة تلقائياً بالطلب الجديد.</small>
                    </div>
                    <a href="{{ route('university.apply.options') }}?candidate_id={{ $candidate->id }}&is_second_time=1" class="btn btn-solid-navy px-4 py-2.5 fw-bold shadow-sm">
                        <i class="fa-solid fa-circle-arrow-left me-1" style="color: var(--heritage-gold);"></i> المتابعة لاختيار درجة التعادل المكرر
                    </a>
                </div>
            </div>
        </div>
    @else
        <!-- Candidate Not Found Alert -->
        <div class="card border-0 shadow-sm p-4 text-center text-muted mb-4 bg-white" style="border-radius: 14px; border-top: 4px solid #EF4444 !important;">
            <i class="fa-solid fa-user-xmark fs-1 text-danger mb-2 d-block"></i>
            <h6 class="fw-bold" style="color: var(--imperial-navy);">لم يتم العثور على أي مرشح مسجل سابقاً بهذا الرقم الوطني ({{ $searchNationalId }}).</h6>
            <p class="mb-3 fs-7">إذا كانت هذه هي المرة الأولى التي يقدم فيها هذا المرشح، يرجى التوجه لخدمة **(تعادل جديد للمرة الأولى)**.</p>
            <div>
                <a href="{{ route('university.apply.options') }}" class="btn btn-solid-navy px-4 py-2 fw-bold">
                    <i class="fa-solid fa-file-circle-plus me-1" style="color: var(--heritage-gold);"></i> الانتقال إلى تقديم (تعادل جديد)
                </a>
            </div>
        </div>
    @endif
@endif

@endsection
