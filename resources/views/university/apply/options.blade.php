@extends('layouts.university')

@section('title', 'تقديم طلب تعادل جديد')

@section('content')

<div class="row mb-4">
    <div class="col-12 text-center my-4">
        <h2 class="fw-extrabold text-primary mb-2">تعادل الشهادات العلمية</h2>
        <p class="text-muted">اختر نوع معاملة التعادل المناسبة للمرشح للبدء في ملء طلب التقويم</p>
    </div>
</div>

<div class="row justify-content-center g-4">
    <!-- Option A: Syrian Master's (ACTIVE) -->
    <div class="col-md-4">
        <a href="{{ route('university.apply.syrian_masters') }}" class="card text-center p-4 h-100 text-decoration-none mohe-card active-option-card" style="border: 2px solid rgba(30, 58, 95, 0.1);">
            <div class="card-option-badge">متاح الآن</div>
            <div class="option-icon-wrapper mx-auto mb-3">
                <i class="fa-solid fa-graduation-cap fs-1 text-primary"></i>
            </div>
            <h5 class="fw-bold text-dark mb-2">ماجستير سوري</h5>
            <span class="text-muted d-block small mb-3">Syrian Master's</span>
            <p class="small text-muted mb-0">لتعادل درجة الماجستير الصادرة عن إحدى الجامعات الحكومية أو الخاصة السورية.</p>
        </a>
    </div>

    <!-- Option B: Syrian Doctorate (Disabled Placeholder) -->
    <div class="col-md-4">
        <div class="card text-center p-4 h-100 text-decoration-none mohe-card disabled-option-card">
            <div class="card-option-badge bg-secondary text-white border-0">قريباً</div>
            <div class="option-icon-wrapper bg-light mx-auto mb-3">
                <i class="fa-solid fa-user-graduate fs-1 text-secondary"></i>
            </div>
            <h5 class="fw-bold text-secondary mb-2">دكتوراه سورية</h5>
            <span class="text-muted d-block small mb-3">Syrian Doctorate</span>
            <p class="small text-muted mb-0">لتعادل درجة الدكتوراه الصادرة عن إحدى الجامعات السورية الحكومية أو الخاصة.</p>
        </div>
    </div>

    <!-- Option C: Non-Syrian Master's (Disabled Placeholder) -->
    <div class="col-md-4">
        <div class="card text-center p-4 h-100 text-decoration-none mohe-card disabled-option-card">
            <div class="card-option-badge bg-secondary text-white border-0">قريباً</div>
            <div class="option-icon-wrapper bg-light mx-auto mb-3">
                <i class="fa-solid fa-earth-americas fs-1 text-secondary"></i>
            </div>
            <h5 class="fw-bold text-secondary mb-2">ماجستير غير سوري</h5>
            <span class="text-muted d-block small mb-3">Non-Syrian Master's</span>
            <p class="small text-muted mb-0">لتعادل درجة الماجستير الصادرة عن جامعات عربية أو أجنبية (تحتاج فحص الإنتاج العلمي).</p>
        </div>
    </div>

    <!-- Option D: Non-Syrian Doctorate (Disabled Placeholder) -->
    <div class="col-md-4">
        <div class="card text-center p-4 h-100 text-decoration-none mohe-card disabled-option-card">
            <div class="card-option-badge bg-secondary text-white border-0">قريباً</div>
            <div class="option-icon-wrapper bg-light mx-auto mb-3">
                <i class="fa-solid fa-award fs-1 text-secondary"></i>
            </div>
            <h5 class="fw-bold text-secondary mb-2">دكتوراه غير سورية</h5>
            <span class="text-muted d-block small mb-3">Non-Syrian Doctorate</span>
            <p class="small text-muted mb-0">لتعادل درجة الدكتوراه الصادرة عن جامعات عربية أو أجنبية (تحتاج فحص الإنتاج العلمي).</p>
        </div>
    </div>

    <!-- Option E: Faculty Member (Disabled Placeholder) -->
    <div class="col-md-4">
        <div class="card text-center p-4 h-100 text-decoration-none mohe-card disabled-option-card">
            <div class="card-option-badge bg-secondary text-white border-0">قريباً</div>
            <div class="option-icon-wrapper bg-light mx-auto mb-3">
                <i class="fa-solid fa-users-rectangle fs-1 text-secondary"></i>
            </div>
            <h5 class="fw-bold text-secondary mb-2">عضو هيئة تدريسية</h5>
            <span class="text-muted d-block small mb-3">Faculty Member</span>
            <p class="small text-muted mb-0">خاص بأعضاء الهيئة التدريسية المنقولين أو المعينين في الجامعات الحكومية السورية.</p>
        </div>
    </div>

    <!-- Option F: Researcher (Disabled Placeholder) -->
    <div class="col-md-4">
        <div class="card text-center p-4 h-100 text-decoration-none mohe-card disabled-option-card">
            <div class="card-option-badge bg-secondary text-white border-0">قريباً</div>
            <div class="option-icon-wrapper bg-light mx-auto mb-3">
                <i class="fa-solid fa-microscope fs-1 text-secondary"></i>
            </div>
            <h5 class="fw-bold text-secondary mb-2">باحث في مراكز البحوث</h5>
            <span class="text-muted d-block small mb-3">Researcher in Research Centers</span>
            <p class="small text-muted mb-0">لمعادلة وتقويم الدرجات العلمية للباحثين العاملين في مراكز البحوث العلمية السورية.</p>
        </div>
    </div>
</div>

<style>
    .option-icon-wrapper {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: rgba(30, 58, 95, 0.05);
        transition: all 0.3s;
    }

    .active-option-card:hover {
        border-color: var(--mohe-navy) !important;
    }

    .active-option-card:hover .option-icon-wrapper {
        background-color: var(--mohe-navy);
    }
    
    .active-option-card:hover .option-icon-wrapper i {
        color: #ffffff !important;
    }

    .disabled-option-card {
        opacity: 0.7;
        background-color: #fafafa !important;
        cursor: not-allowed;
        pointer-events: none;
    }

    .card-option-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        font-size: 0.7rem;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 12px;
        background-color: rgba(201, 176, 55, 0.15);
        color: var(--mohe-gold-dark);
        border: 1px solid var(--mohe-gold);
    }
</style>

@endsection
