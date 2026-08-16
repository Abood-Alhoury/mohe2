@extends('layouts.university')

@section('title', 'تقديم طلب تعادل جديد')

@section('content')

<div class="row mb-4">
    <div class="col-12 text-center my-3">
        <h2 class="headline-lg text-prestigious mb-2">تعادل الشهادات العلمية والتدريس</h2>
        <p class="body-md text-muted">اختر نوع معاملة التعادل المناسبة للمرشح للبدء في ملء طلب التقويم الإلكتروني</p>
    </div>
</div>

<div class="row justify-content-center g-4 mb-5">
    <!-- Option 1: Syrian Master's (ACTIVE) -->
    <div class="col-md-4">
        <a href="{{ route('university.apply.syrian_masters') }}" class="card text-center p-4 h-100 text-decoration-none mohe-card active-option-card position-relative" style="border-radius: 8px; border-top: 3.5px solid var(--heritage-gold) !important;">
            <div class="card-option-badge">متاح للتقديم</div>
            <div class="option-icon-wrapper mx-auto mb-3">
                <i class="fa-solid fa-graduation-cap fs-1" style="color: var(--heritage-gold);"></i>
            </div>
            <h5 class="fw-bold mb-1" style="color: var(--primary-container);">ماجستير سوري</h5>
            <span class="label-sm text-muted d-block mb-3">Syrian Master's Degree</span>
            <p class="body-md small text-muted mb-0">لتعادل درجة الماجستير الصادرة عن إحدى الجامعات الحكومية أو الخاصة السورية لغايات التدريس.</p>
        </a>
    </div>

    <!-- Option 2: Syrian Doctorate (ACTIVE) -->
    <div class="col-md-4">
        <a href="{{ route('university.apply.syrian_doctorate') }}" class="card text-center p-4 h-100 text-decoration-none mohe-card active-option-card position-relative" style="border-radius: 8px; border-top: 3.5px solid var(--heritage-gold) !important;">
            <div class="card-option-badge">متاح للتقديم</div>
            <div class="option-icon-wrapper mx-auto mb-3">
                <i class="fa-solid fa-user-graduate fs-1" style="color: var(--heritage-gold);"></i>
            </div>
            <h5 class="fw-bold mb-1" style="color: var(--primary-container);">دكتوراه سورية</h5>
            <span class="label-sm text-muted d-block mb-3">Syrian Doctorate</span>
            <p class="body-md small text-muted mb-0">لتعادل درجة الدكتوراه الصادرة عن إحدى الجامعات السورية الحكومية أو الخاصة.</p>
        </a>
    </div>

    <!-- Option 3: Non-Syrian Master's (Disabled Placeholder) -->
    <div class="col-md-4">
        <div class="card text-center p-4 h-100 text-decoration-none mohe-card disabled-option-card position-relative" style="border-radius: 8px;">
            <div class="card-option-badge bg-secondary text-white border-0">قريباً</div>
            <div class="option-icon-wrapper bg-light mx-auto mb-3">
                <i class="fa-solid fa-earth-americas fs-1 text-muted"></i>
            </div>
            <h5 class="fw-bold text-muted mb-1">ماجستير غير سوري</h5>
            <span class="label-sm text-muted d-block mb-3">Non-Syrian Master's</span>
            <p class="body-md small text-muted mb-0">لتعادل درجة الماجستير الصادرة عن جامعات عربية أو أجنبية (تتطلب تقويم الإنتاج العلمي).</p>
        </div>
    </div>

    <!-- Option 4: Non-Syrian Doctorate (Disabled Placeholder) -->
    <div class="col-md-4">
        <div class="card text-center p-4 h-100 text-decoration-none mohe-card disabled-option-card position-relative" style="border-radius: 8px;">
            <div class="card-option-badge bg-secondary text-white border-0">قريباً</div>
            <div class="option-icon-wrapper bg-light mx-auto mb-3">
                <i class="fa-solid fa-award fs-1 text-muted"></i>
            </div>
            <h5 class="fw-bold text-muted mb-1">دكتوراه غير سورية</h5>
            <span class="label-sm text-muted d-block mb-3">Non-Syrian Doctorate</span>
            <p class="body-md small text-muted mb-0">لتعادل درجة الدكتوراه الصادرة عن جامعات عربية أو أجنبية (تتطلب فحص وثائق وإقامة).</p>
        </div>
    </div>

    <!-- Option 5: Faculty Member (Disabled Placeholder) -->
    <div class="col-md-4">
        <div class="card text-center p-4 h-100 text-decoration-none mohe-card disabled-option-card position-relative" style="border-radius: 8px;">
            <div class="card-option-badge bg-secondary text-white border-0">قريباً</div>
            <div class="option-icon-wrapper bg-light mx-auto mb-3">
                <i class="fa-solid fa-users-rectangle fs-1 text-muted"></i>
            </div>
            <h5 class="fw-bold text-muted mb-1">عضو هيئة تدريسية</h5>
            <span class="label-sm text-muted d-block mb-3">Faculty Member</span>
            <p class="body-md small text-muted mb-0">خاص بأعضاء الهيئة التدريسية المنقولين أو المعينين في الجامعات الحكومية السورية.</p>
        </div>
    </div>

    <!-- Option 6: Research Centers Scientist (Disabled Placeholder) -->
    <div class="col-md-4">
        <div class="card text-center p-4 h-100 text-decoration-none mohe-card disabled-option-card position-relative" style="border-radius: 8px;">
            <div class="card-option-badge bg-secondary text-white border-0">قريباً</div>
            <div class="option-icon-wrapper bg-light mx-auto mb-3">
                <i class="fa-solid fa-microscope fs-1 text-muted"></i>
            </div>
            <h5 class="fw-bold text-muted mb-1">باحث في مراكز البحوث</h5>
            <span class="label-sm text-muted d-block mb-3">Research Center Scientist</span>
            <p class="body-md small text-muted mb-0">لمعادلة وتقويم الدرجات العلمية للباحثين العاملين في مراكز البحوث السورية.</p>
        </div>
    </div>
</div>

<style>
    .option-icon-wrapper {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: var(--surface-container-low);
        border: 2px solid var(--outline-variant);
        transition: all 0.25s ease;
    }

    .active-option-card {
        transition: all 0.22s ease;
    }

    .active-option-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 30px rgba(26, 42, 68, 0.12) !important;
        border-top-color: var(--heritage-gold) !important;
    }

    .active-option-card:hover .option-icon-wrapper {
        background-color: var(--primary-container);
        border-color: var(--heritage-gold);
    }
    
    .active-option-card:hover .option-icon-wrapper i {
        color: var(--heritage-gold-light) !important;
    }

    .disabled-option-card {
        opacity: 0.65;
        background-color: var(--surface-container-low) !important;
        cursor: not-allowed;
        pointer-events: none;
    }

    .card-option-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        font-size: 0.72rem;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: var(--radius-default);
        background-color: var(--secondary-container);
        color: var(--on-secondary-container);
        border: 1px solid var(--heritage-gold);
    }
</style>

@endsection
