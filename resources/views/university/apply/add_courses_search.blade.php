@extends('layouts.university')

@section('title', 'إضافة مقررات دراسية - البحث بالرقم الوطني')

@section('content')

<!-- Header Breadcrumb & Title -->
<div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('university.dashboard') }}" class="text-decoration-none text-muted">لوحة التحكم</a></li>
                <li class="breadcrumb-item"><a href="{{ route('university.apply.options') }}" class="text-decoration-none text-muted">خيارات التعادل</a></li>
                <li class="breadcrumb-item active text-primary fw-bold" aria-current="page">طلب إضافة مقررات دراسية</li>
            </ol>
        </nav>
        <h3 class="fw-bold mb-0" style="color: var(--imperial-navy);">
            <i class="fa-solid fa-book-medical me-2" style="color: var(--heritage-gold);"></i>
            إضافة مقررات دراسية جديدة للكوادر التدريسية
        </h3>
    </div>
</div>

<!-- Institutional Informational Hero Card -->
<div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #1A2A44 0%, #0F1A2C 100%); border-radius: 14px; border-right: 5px solid var(--heritage-gold) !important;">
    <div class="card-body p-4 text-white">
        <div class="row align-items-center">
            <div class="col-md-9">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-warning text-dark px-3 py-1 fw-bold" style="border-radius: 6px;">خدمة إضافة المقررات</span>
                    <span class="text-white-50 small"><i class="fa-solid fa-circle-info me-1"></i> الكوادر التدريسية والجامعات</span>
                </div>
                <h4 class="fw-bold mb-2 text-white">البحث عن مرشح مكلّف في سجلات الجامعة بالرقم الوطني</h4>
                <p class="text-white-50 mb-0 leading-relaxed" style="font-size: 0.95rem;">
                    تتيح هذه الخدمة للجامعة إدراج مواد ومقررات دراسية جديدة للأساتذة والمرشحين المكلّفين لديكم والصادر بحقهم قرار معادلة سابق. 
                    الرجاء إدخال **الرقم الوطني للمرشح** لاسترجاع معاملاته الصادرة والبدء بإضافة المقررات الجديدة.
                </p>
            </div>
            <div class="col-md-3 text-center d-none d-md-block">
                <div class="p-3 d-inline-block rounded-circle" style="background: rgba(197,160,89,0.15); border: 2px solid var(--heritage-gold);">
                    <i class="fa-solid fa-book-medical text-warning" style="font-size: 3rem;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search Form Section -->
<div class="card border-0 shadow-sm mb-5" style="border-radius: 14px; border-top: 4px solid var(--imperial-navy) !important;">
    <div class="card-body p-4">
        <form action="{{ route('university.apply.add_courses') }}" method="GET">
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
                        <i class="fa-solid fa-search me-1"></i> استرجاع المعاملات الصادرة
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Search Results Section -->
@if(!empty($searchNationalId))
    @if($applications->count() > 0)
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px; border-top: 4px solid #10B981 !important;">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-success">
                    <i class="fa-solid fa-circle-check me-2"></i>
                    تم العثور على ({{ $applications->count() }}) معاملة تعادل صادرة رسمياً لهذا المرشح في جامعتكم:
                </h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    @foreach($applications as $app)
                    <div class="col-md-6 col-lg-4">
                        <div class="border rounded-3 p-3.5 bg-white position-relative h-100 d-flex flex-column justify-content-between shadow-2xs" style="border-right: 4px solid var(--imperial-navy) !important;">
                            <div>
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-success-subtle text-success border border-success px-2.5 py-1 fw-bold">
                                        <i class="fa-solid fa-certificate me-1"></i> قرار صادر رسمياً
                                    </span>
                                    <span class="text-muted font-monospace fs-8">#{{ $app->application_no }}</span>
                                </div>
                                <h6 class="fw-bold mb-1" style="color: var(--imperial-navy);">{{ optional($app->candidate)->full_name }}</h6>
                                <div class="fs-8 text-muted mb-2"><i class="fa-solid fa-id-card me-1"></i> الرقم الوطني: {{ optional($app->candidate)->national_id }}</div>
                                <div class="fs-8 text-dark mb-1"><strong>الجامعة المكلف بها:</strong> {{ optional($app->workUniversity)->name }}</div>
                                <div class="fs-8 text-dark mb-1"><strong>الكلية والفرع:</strong> {{ $app->work_faculty }} - {{ $app->work_department }}</div>
                                
                                @if($app->latestDecision)
                                <div class="p-2 rounded bg-light border mt-2 fs-8">
                                    <div class="text-primary fw-bold"><i class="fa-solid fa-file-pdf me-1"></i> قرار المعادلة الصادر:</div>
                                    <div>رقم: <strong>{{ $app->latestDecision->decision_no }}</strong> | تاريخ: {{ $app->latestDecision->decision_date }}</div>
                                </div>
                                @endif
                            </div>
                            <div class="mt-3 pt-2 border-top">
                                <a href="{{ route('university.apply.add_courses.create', $app->id) }}" class="btn btn-solid-navy w-100 fw-bold py-2">
                                    <i class="fa-solid fa-plus me-1" style="color: var(--heritage-gold);"></i> بدء طلب إضافة مقررات دراسية
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm p-4 text-center text-muted mb-4 bg-white" style="border-radius: 14px; border-top: 4px solid #EF4444 !important;">
            <i class="fa-solid fa-folder-open fs-1 text-warning mb-2 d-block"></i>
            <h6 class="fw-bold" style="color: var(--imperial-navy);">لم يتم العثور على قرارات تعادل صادرة رسمياً في جامعتكم بهذا الرقم الوطني ({{ $searchNationalId }}).</h6>
            <p class="mb-0 fs-7">تأكد من إدخال الرقم الوطني بشكل صحيح وأن المرشح صادرة له معاملة تعادل سابقة لدى جامعتكم.</p>
        </div>
    @endif
@endif

@endsection
