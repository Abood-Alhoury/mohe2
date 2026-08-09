@extends(Auth::check() && optional(Auth::user()->role)->name === 'admin' ? 'layouts.admin' : 'layouts.university')
@section('title', 'تواصل معنا - قنوات الدعم والتوجيه الرسمي')

@section('content')
<!-- Hero Title Header -->
<div class="mb-4 text-center py-4 rounded shadow-sm position-relative overflow-hidden" 
     style="background: linear-gradient(135deg, #091728 0%, #152B47 50%, #0B1C33 100%); border-bottom: 3px solid var(--heritage-gold); color: white;">
    <div class="position-relative z-1 py-2">
        <div class="d-inline-flex align-items-center justify-content-center p-2 rounded-circle bg-white shadow-sm mb-2" style="border: 2px solid var(--heritage-gold); width: 64px; height: 64px;">
            <i class="fa-solid fa-headset fs-2" style="color: var(--imperial-navy);"></i>
        </div>
        <h1 class="h3 fw-bold mb-1">مركز الدعم والتواصل المؤسسي للجامعات</h1>
        <p class="text-white-50 small mb-0">منظومة إدارية مغلقة مخصصة حصراً للجامعات السورية المعتمدة ومجلس التعليم العالي بوزارة التعليم العالي والبحث العلمي</p>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success border-0 shadow-sm mb-4 d-flex align-items-center gap-2">
    <i class="fa-solid fa-circle-check fs-4"></i>
    <div>{{ session('success') }}</div>
</div>
@endif

<!-- Contact Cards Row -->
<div class="row g-4 mb-5">
    <div class="col-md-3 col-sm-6">
        <div class="bg-white p-4 rounded shadow-sm border border-secondary-subtle text-center h-100 transition-all hover-shadow" style="border-top: 4px solid var(--imperial-navy) !important;">
            <div class="d-inline-flex p-3 rounded-circle mb-3" style="background: #f0f3ff; color: var(--imperial-navy);">
                <i class="fa-solid fa-location-dot fs-3"></i>
            </div>
            <h6 class="fw-bold mb-2" style="color: var(--imperial-navy);">المقر الرئيسي للوزارة</h6>
            <p class="text-muted small mb-0">دمشق - المزة - اتستراد المزة<br>مبنى وزارة التعليم العالي والبحث العلمي</p>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="bg-white p-4 rounded shadow-sm border border-secondary-subtle text-center h-100 transition-all hover-shadow" style="border-top: 4px solid var(--heritage-gold) !important;">
            <div class="d-inline-flex p-3 rounded-circle mb-3" style="background: #fffdf5; color: var(--heritage-gold);">
                <i class="fa-solid fa-phone fs-3"></i>
            </div>
            <h6 class="fw-bold mb-2" style="color: var(--imperial-navy);">هواتف الاستعلام والبدالة</h6>
            <p class="text-muted small mb-0" dir="ltr">
                +963 11 211 4500<br>
                +963 11 211 4501
            </p>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="bg-white p-4 rounded shadow-sm border border-secondary-subtle text-center h-100 transition-all hover-shadow" style="border-top: 4px solid #0284c7 !important;">
            <div class="d-inline-flex p-3 rounded-circle mb-3 bg-info-subtle text-info">
                <i class="fa-solid fa-envelope fs-3"></i>
            </div>
            <h6 class="fw-bold mb-2" style="color: var(--imperial-navy);">البريد الإلكتروني المعتمد</h6>
            <p class="text-muted small mb-0">
                equivalence@mohe.gov.sy<br>
                info@mohe.gov.sy
            </p>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="bg-white p-4 rounded shadow-sm border border-secondary-subtle text-center h-100 transition-all hover-shadow" style="border-top: 4px solid #16a34a !important;">
            <div class="d-inline-flex p-3 rounded-circle mb-3 bg-success-subtle text-success">
                <i class="fa-solid fa-building-columns fs-3"></i>
            </div>
            <h6 class="fw-bold mb-2" style="color: var(--imperial-navy);">نطاق الخدمة والتخويل</h6>
            <p class="text-muted small mb-0">
                مخصص لمندوبي الجامعات<br>
                ومديرية المعادلة بمجلس التعليم
            </p>
        </div>
    </div>
</div>

<!-- Main Section: Interactive Form + FAQ -->
<div class="row g-4 mb-5">
    <!-- Contact Form -->
    <div class="col-lg-7">
        <div class="bg-white p-4 rounded shadow-sm border border-secondary-subtle h-100">
            <h5 class="fw-bold mb-3" style="color: var(--imperial-navy);">
                <i class="fa-solid fa-paper-plane me-2" style="color: var(--heritage-gold);"></i>
                إرسال استفسار مؤسسي للوزارة
            </h5>
            <p class="text-muted small mb-4">نموذج التواصل الفني والإداري المخصص لمسؤولي ومندوبي الجامعات المسجلة لدى المنظومة.</p>

            <form action="{{ route('contact.submit') }}" method="POST">
                @csrf
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold fs-7">اسم الجامعة / اسم المندوب :</label>
                        <input type="text" name="name" class="form-control" value="{{ Auth::user()->name ?? '' }}" placeholder="اكتب اسم الجامعة أو المندوب أصولاً" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold fs-7">البريد الإلكتروني المؤسسي :</label>
                        <input type="email" name="email" class="form-control" value="{{ Auth::user()->email ?? '' }}" placeholder="uni@mohe.gov.sy" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold fs-7">موضوع الاستفسار المؤسسي :</label>
                    <select name="subject" class="form-select" required>
                        <option value="">اختر موضوع الرسالة...</option>
                        <option value="استفسار عن طلب معادلة">استفسار عن حالة طلب معادلة شهادة</option>
                        <option value="طلب استكمال أوراق أو وثائق">طلب استكمال أوراق أو وثائق ناقصة</option>
                        <option value="استفسار تقني بالحساب الجامعي">استفسار تقني أو مشكلة في حساب الجامعة</option>
                        <option value="آخر">تنسيق إداري / موضوع آخر</option>
                    </select>
                </div>

                @if(Auth::check() && isset($userApplications) && $userApplications->count() > 0)
                <div class="mb-3">
                    <label class="form-label fw-bold fs-7 text-dark">ربط المعاملة المرتبطة بالاستفسار (اختياري) :</label>
                    <select name="application_id" class="form-select">
                        <option value="">استفسار عام / غير محدد بمعاملة معينة</option>
                        @foreach($userApplications as $userApp)
                            <option value="{{ $userApp->id }}">
                                رقم الطلب: {{ $userApp->application_no }} (المرشح: {{ optional($userApp->candidate)->full_name ?? 'المرشح' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="mb-4">
                    <label class="form-label fw-bold fs-7">تفاصيل الرسالة أو الاستفسار :</label>
                    <textarea name="message" class="form-control" rows="5" placeholder="اكتب تفاصيل الاستفسار أو الملاحظة هنا بشكل واضح..." required></textarea>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-solid-navy px-4 py-2 fw-bold shadow-sm">
                        <i class="fa-solid fa-paper-plane me-1"></i> إرسال الاستفسار أصولاً
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- FAQ Accordion -->
    <div class="col-lg-5">
        <div class="bg-white p-4 rounded shadow-sm border border-secondary-subtle h-100">
            <h5 class="fw-bold mb-3" style="color: var(--imperial-navy);">
                <i class="fa-solid fa-circle-question me-2" style="color: var(--heritage-gold);"></i>
                أسئلة شائعة حول معادلة الشهادات
            </h5>
            <p class="text-muted small mb-4">إجابات سريعة لأهم التساؤلات المتعلقة بتقديم وتدقيق معاملة التعادل.</p>

            <div class="accordion" id="faqAccordion">
                <div class="accordion-item border-0 mb-2 shadow-sm rounded overflow-hidden">
                    <h2 class="accordion-header" id="faqOne">
                        <button class="accordion-button fw-bold fs-7" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                            ما هي المدة التقديرية لدراسة طلب المعادلة؟
                        </button>
                    </h2>
                    <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-muted fs-7">
                            تخضع جميع الطلبات للتدقيق الأولي والعرض على لجنة المعادلة الفنية بمجلس التعليم العالي، وتستغرق المعاملات المستكملة للوثائق من 5 إلى 15 يوم عمل.
                        </div>
                    </div>
                </div>

                <div class="accordion-item border-0 mb-2 shadow-sm rounded overflow-hidden">
                    <h2 class="accordion-header" id="faqTwo">
                        <button class="accordion-button collapsed fw-bold fs-7" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                            كيف يتم إشعار الجامعة بحالة الطلب وصدور القرار؟
                        </button>
                    </h2>
                    <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-muted fs-7">
                            يصل إشعار فوري ورسالة تنبيهية في مركز المراسلات وجرس الإشعارات بحساب الجامعة المسجلة فور تحديث الحالة أو صدور قرار التعادل الرسمي.
                        </div>
                    </div>
                </div>

                <div class="accordion-item border-0 mb-2 shadow-sm rounded overflow-hidden">
                    <h2 class="accordion-header" id="faqThree">
                        <button class="accordion-button collapsed fw-bold fs-7" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                            ما العمل عند تغيير حالة الطلب إلى "بانتظار الوثائق"؟
                        </button>
                    </h2>
                    <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-muted fs-7">
                            يمكن لحساب الجامعة التعديل المباشر على الطلب وإعادة إرفاق الوثائق المطلوبة أو التعديل على بيانات المؤهل ثم إعادة رفع الطلب للمراجعة.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
