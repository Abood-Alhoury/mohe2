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
    <!-- Option 1: Syrian Master's - Applied Track (ACTIVE) -->
    <div class="col-md-4">
        <a href="{{ route('university.apply.syrian_masters_applied') }}" class="card text-center p-4 h-100 text-decoration-none mohe-card active-option-card position-relative" style="border-radius: 8px; border-top: 3.5px solid var(--heritage-gold) !important;">
            <div class="card-option-badge">متاح للتقديم</div>
            <div class="option-icon-wrapper mx-auto mb-3">
                <i class="fa-solid fa-graduation-cap fs-1" style="color: var(--heritage-gold);"></i>
            </div>
            <h5 class="fw-bold mb-1" style="color: var(--primary-container);">ماجستير سوري (مسار تطبيقي)</h5>
            <span class="label-sm text-muted d-block mb-3">Syrian Master's (Applied Track)</span>
            <p class="body-md small text-muted mb-0">لتعادل درجة الماجستير الصادرة عن الجامعات السورية لتدريس الجوانب التطبيقية (بدون شرط خبرة السنتين).</p>
        </a>
    </div>

    <!-- Option 2: Syrian Master's - Theoretical Track (ACTIVE) -->
    <div class="col-md-4">
        <a href="{{ route('university.apply.syrian_masters_theoretical') }}" class="card text-center p-4 h-100 text-decoration-none mohe-card active-option-card position-relative" style="border-radius: 8px; border-top: 3.5px solid var(--heritage-gold) !important;">
            <div class="card-option-badge">متاح للتقديم</div>
            <div class="option-icon-wrapper mx-auto mb-3">
                <i class="fa-solid fa-book-open-reader fs-1" style="color: var(--heritage-gold);"></i>
            </div>
            <h5 class="fw-bold mb-1" style="color: var(--primary-container);">ماجستير سوري (مسار نظري)</h5>
            <span class="label-sm text-muted d-block mb-3">Syrian Master's (Theoretical Track)</span>
            <p class="body-md small text-muted mb-0">لتعادل درجة الماجستير الصادرة عن الجامعات السورية لتدريس المقررات النظرية (يتطلب خبرة تدريسية لا تقل عن سنتين).</p>
        </a>
    </div>

    <!-- Option 3: Syrian Doctorate (ACTIVE) -->
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

    <!-- Option 4A: Foreign Master's - Applied Track (ACTIVE) -->
    <div class="col-md-4">
        <a href="{{ route('university.apply.foreign_masters_applied') }}" class="card text-center p-4 h-100 text-decoration-none mohe-card active-option-card position-relative" style="border-radius: 8px; border-top: 3.5px solid var(--heritage-gold) !important;">
            <div class="card-option-badge">متاح للتقديم</div>
            <div class="option-icon-wrapper mx-auto mb-3">
                <i class="fa-solid fa-earth-americas fs-1" style="color: var(--heritage-gold);"></i>
            </div>
            <h5 class="fw-bold mb-1" style="color: var(--primary-container);">ماجستير خارجي (مسار تطبيقي)</h5>
            <span class="label-sm text-muted d-block mb-3">Foreign Master's (Applied Track)</span>
            <p class="body-md small text-muted mb-0">لتعادل درجة الماجستير غير السورية لتدريس الجوانب التطبيقية (بدون شرط خبرة السنتين، مع حركات الإقامة أو الإعفاء للتخصصات النظرية).</p>
        </a>
    </div>

    <!-- Option 4B: Foreign Master's - Theoretical Track (ACTIVE) -->
    <div class="col-md-4">
        <a href="{{ route('university.apply.foreign_masters_theoretical') }}" class="card text-center p-4 h-100 text-decoration-none mohe-card active-option-card position-relative" style="border-radius: 8px; border-top: 3.5px solid var(--heritage-gold) !important;">
            <div class="card-option-badge">متاح للتقديم</div>
            <div class="option-icon-wrapper mx-auto mb-3">
                <i class="fa-solid fa-globe fs-1" style="color: var(--heritage-gold);"></i>
            </div>
            <h5 class="fw-bold mb-1" style="color: var(--primary-container);">ماجستير خارجي (مسار نظري)</h5>
            <span class="label-sm text-muted d-block mb-3">Foreign Master's (Theoretical Track)</span>
            <p class="body-md small text-muted mb-0">لتعادل درجة الماجستير غير السورية لتدريس المقررات النظرية (يتطلب خبرة سنتين فأكثر مع عقود وإيصالات وشهادة خبرة وإثبات الإقامة أو الإعفاء).</p>
        </a>
    </div>

    <!-- Option 5: Non-Syrian Doctorate (ACTIVE) -->
    <div class="col-md-4">
        <a href="{{ route('university.apply.foreign_doctorate') }}" class="card text-center p-4 h-100 text-decoration-none mohe-card active-option-card position-relative" style="border-radius: 8px; border-top: 3.5px solid var(--heritage-gold) !important;">
            <div class="card-option-badge">متاح للتقديم</div>
            <div class="option-icon-wrapper mx-auto mb-3">
                <i class="fa-solid fa-award fs-1" style="color: var(--heritage-gold);"></i>
            </div>
            <h5 class="fw-bold mb-1" style="color: var(--primary-container);">دكتوراه غير سورية (خارجية)</h5>
            <span class="label-sm text-muted d-block mb-3">Non-Syrian Doctorate</span>
            <p class="body-md small text-muted mb-0">لتعادل وتقييم درجة الدكتوراه الصادرة عن جامعات عربية أو أجنبية (فحص وثائق، إقامة، وتقييم الإنتاج العلمي ومقابلة الأهلية).</p>
        </a>
    </div>

    <!-- Option 6: Faculty Member Teaching Permission (ACTIVE) -->
    <div class="col-md-4">
        <a href="{{ route('university.apply.faculty_permission') }}" class="card text-center p-4 h-100 text-decoration-none mohe-card active-option-card position-relative" style="border-radius: 8px; border-top: 3.5px solid var(--heritage-gold) !important;">
            <div class="card-option-badge">متاح للتقديم</div>
            <div class="option-icon-wrapper mx-auto mb-3">
                <i class="fa-solid fa-chalkboard-user fs-1" style="color: var(--heritage-gold);"></i>
            </div>
            <h5 class="fw-bold mb-1" style="color: var(--primary-container);">عضو هيئة تدريسية (معاملة السماح)</h5>
            <span class="label-sm text-muted d-block mb-3">Faculty Member Permission</span>
            <p class="body-md small text-muted mb-0">خاص بأعضاء الهيئة التدريسية في الجامعات الحكومية السورية لطلب السماح بالتدريس في الجامعات الخاصة.</p>
        </a>
    </div>
</div>

@endsection
