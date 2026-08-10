@extends('layouts.university')

@section('title', 'تحويل قرار المعادلة - البحث بالرقم الوطني')

@section('content')

<!-- Header Breadcrumb & Title -->
<div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('university.dashboard') }}" class="text-decoration-none text-muted">لوحة التحكم</a></li>
                <li class="breadcrumb-item"><a href="{{ route('university.apply.options') }}" class="text-decoration-none text-muted">خيارات التعادل</a></li>
                <li class="breadcrumb-item active text-primary fw-bold" aria-current="page">تحويل قرار المعادلة</li>
            </ol>
        </nav>
        <h3 class="fw-bold mb-0" style="color: var(--imperial-navy);">
            <i class="fa-solid fa-right-left me-2" style="color: var(--heritage-gold);"></i>
            تحويل قرار المعادلة والجهة المكلّفة
        </h3>
    </div>
</div>

<!-- Institutional Informational Hero Card -->
<div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #1A2A44 0%, #0F1A2C 100%); border-radius: 14px; border-right: 5px solid var(--heritage-gold) !important;">
    <div class="card-body p-4 text-white">
        <div class="row align-items-center">
            <div class="col-md-9">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-warning text-dark px-3 py-1 fw-bold" style="border-radius: 6px;">خدمة المؤسسات والجامعات</span>
                    <span class="text-white-50 small"><i class="fa-solid fa-circle-info me-1"></i> نقل التكليف وتعديل المقررات</span>
                </div>
                <h4 class="fw-bold mb-2 text-white">البحث عن معاملة صادر بها قرار معادلة سابق</h4>
                <p class="text-white-50 mb-0 leading-relaxed" style="font-size: 0.95rem;">
                    تُستخدم هذه الخدمة للأساتذة والمرشحين الصادر بحقهم قرار معادلة سابق، والذين انتهى تكليفهم في الجامعة السابقة وانتقلوا للتكليف في جامعة جديدة.
                    الرجاء إدخال **الرقم الوطني للمرشح** لاسترجاع معاملاته الصادرة والبدء بتقديم طلب نقل التكليف وتعديل المقررات.
                </p>
            </div>
            <div class="col-md-3 text-center d-none d-md-block">
                <div class="p-3 d-inline-block rounded-circle" style="background: rgba(197,160,89,0.15); border: 2px solid var(--heritage-gold);">
                    <i class="fa-solid fa-file-signature text-warning" style="font-size: 3rem;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search Form Section -->
<div class="card border-0 shadow-sm mb-5" style="border-radius: 14px;">
    <div class="card-body p-4">
        <form action="{{ route('university.apply.transfer') }}" method="GET">
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
                               value="{{ $searchId }}"
                               required
                               autofocus>
                    </div>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm py-2.5" style="background: var(--imperial-navy); border-color: var(--imperial-navy);">
                        <i class="fa-solid fa-search me-1"></i> استرجاع المعاملات الصادرة
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Search Results Section -->
@if($searched)
    @if($candidate && $issuedApplications->count() > 0)
        <!-- Candidate Profile Header -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; background: #F0F9FF; border: 1px solid #BAE6FD !important;">
            <div class="card-body p-3.5 d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-white p-3 shadow-sm border border-info" style="width: 55px; height: 55px; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-user-check text-info fs-3"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1" style="color: var(--imperial-navy);">المرشح: {{ $candidate->full_name }}</h5>
                        <div class="d-flex align-items-center gap-3 text-muted small">
                            <span><i class="fa-solid fa-id-card me-1"></i> الرقم الوطني: <strong>{{ $candidate->national_id }}</strong></span>
                            <span><i class="fa-solid fa-flag me-1"></i> الجنسية: {{ optional($candidate->nationality)->name ?? 'سوري' }}</span>
                            <span><i class="fa-solid fa-phone me-1"></i> الهاتف: {{ $candidate->mobile ?? $candidate->phone ?? 'غير محدد' }}</span>
                        </div>
                    </div>
                </div>
                <div>
                    <span class="badge bg-success px-3 py-2 fs-7" style="border-radius: 6px;">
                        <i class="fa-solid fa-circle-check me-1"></i> تم العثور على {{ $issuedApplications->count() }} معاملة صادرة
                    </span>
                </div>
            </div>
        </div>

        <!-- List of Issued Applications Cards -->
        <h5 class="fw-bold mb-3" style="color: var(--imperial-navy);">
            <i class="fa-solid fa-award text-warning me-2"></i> المعاملات وقرارات المعادلة الصادرة المتاحة للتحويل:
        </h5>

        <div class="row g-4 mb-5">
            @foreach($issuedApplications as $app)
                @php
                    $decision = $app->latestDecision;
                    $primaryEd = $app->educations->first();
                @endphp
                <div class="col-md-6">
                    <div class="card h-100 shadow-sm border-0 position-relative hover-elevation" style="border-radius: 14px; overflow: hidden; border-top: 4px solid var(--heritage-gold) !important;">
                        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-light text-dark border px-2.5 py-1 font-monospace fw-bold fs-7">
                                    {{ $app->application_no ?? ('طلب #' . $app->id) }}
                                </span>
                                <span class="ms-2 badge bg-primary text-white fs-8">{{ $app->request_type ?? 'معادلة شهادة' }}</span>
                            </div>
                            <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold px-3 py-1">
                                <i class="fa-solid fa-circle-check me-1"></i> قرار صادر
                            </span>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3 p-3 rounded-3" style="background: #FAF8F5; border: 1px dashed #E5D5B7;">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="text-muted fs-8 fw-bold">قرار المعادلة الصادر:</span>
                                    <span class="badge bg-warning text-dark fw-bold">
                                        <i class="fa-solid fa-certificate me-1"></i> رقم القرار: {{ $decision->decision_no ?? 'صادر رسمياً' }}
                                    </span>
                                </div>
                                @if($decision && $decision->decision_date)
                                    <small class="text-muted d-block"><i class="fa-regular fa-calendar me-1"></i> تاريخ الصدور: {{ $decision->decision_date }}</small>
                                @endif
                            </div>

                            <div class="row g-2 mb-3 small">
                                <div class="col-6">
                                    <span class="text-muted d-block fs-8">الجامعة المكلّف بها سابقاً:</span>
                                    <strong style="color: var(--imperial-navy);">{{ optional($app->workUniversity)->name ?? 'غير حددة' }}</strong>
                                </div>
                                <div class="col-6">
                                    <span class="text-muted d-block fs-8">الكلية والدائرة:</span>
                                    <strong class="text-truncate d-block">{{ $app->work_faculty ?? '-' }} / {{ $app->work_department ?? '-' }}</strong>
                                </div>
                                @if($primaryEd)
                                    <div class="col-12 mt-2 pt-2 border-top">
                                        <span class="text-muted d-block fs-8">الدرجة العلمية والجامعة المانحة:</span>
                                        <strong>{{ optional($primaryEd->level)->name }} - {{ optional($primaryEd->university)->name }}</strong>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="card-footer bg-light p-3 border-top text-center">
                            <a href="{{ route('university.apply.transfer.create', $app->id) }}" class="btn btn-primary w-100 fw-bold py-2" style="background: var(--imperial-navy); border-color: var(--imperial-navy); border-radius: 8px;">
                                <i class="fa-solid fa-right-left me-2" style="color: var(--heritage-gold);"></i>
                                بدء تقديم طلب تحويل المعادلة لهذه المعاملة
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @elseif($candidate && $issuedApplications->count() == 0)
        <!-- Candidate found but has no issued decision applications -->
        <div class="alert alert-warning border-0 shadow-sm p-4 text-center mb-5" style="border-radius: 14px; border-right: 5px solid #F59E0B !important;">
            <i class="fa-solid fa-triangle-exclamation text-warning fs-1 mb-3 d-block"></i>
            <h5 class="fw-bold mb-2 text-dark">لا تتوفر معاملات صادرة قابلة للتحويل</h5>
            <p class="text-muted mb-3">
                المرشح <strong>{{ $candidate->full_name }}</strong> مسجل في النظام بالرقم الوطني ({{ $candidate->national_id }})، ولكن ليس لديه أي معاملة معادلة حالة قرارها <strong>"تم الصدور"</strong>.
            </p>
            <small class="text-muted">ملاحظة: يشترط النظام صدم أو نيل قرار معادلة سابق رسمي لتفعيل خيار تحويل المعادلة لنقل التكليف بين الجامعات.</small>
        </div>
    @else
        <!-- Candidate not found -->
        <div class="alert alert-danger border-0 shadow-sm p-4 text-center mb-5" style="border-radius: 14px; border-right: 5px solid #EF4444 !important;">
            <i class="fa-solid fa-user-slash text-danger fs-1 mb-3 d-block"></i>
            <h5 class="fw-bold mb-2 text-dark">لم يتم العثور على أي سجل بهذا الرقم الوطني</h5>
            <p class="text-muted mb-0">
                الرقم الوطني <strong>"{{ $searchId }}"</strong> غير موجود في سجلات مجلس التعليم العالي. يرجى التأكد من الرقم الوطني وإعادة المحاولة.
            </p>
        </div>
    @endif
@endif

@endsection
