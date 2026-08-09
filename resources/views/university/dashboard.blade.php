@extends('layouts.university')

@section('title', 'الرئيسية - لوحة تحكم الجامعة')

@section('content')

<style>
    /* Metric Cards Design System (Identical to Admin Dashboard) */
    .kpi-card-academic {
        background-color: #FFFFFF !important;
        border-radius: 4px !important;
        border: none !important;
        border-top: 3px solid #C5A059 !important;
        box-shadow: 0px 4px 20px rgba(26, 42, 68, 0.05) !important;
        padding: 1.25rem 1.5rem !important;
        height: 120px !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: space-between !important;
        transition: transform 0.2s ease, box-shadow 0.2s ease !important;
    }

    .kpi-card-academic:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0px 8px 24px rgba(26, 42, 68, 0.08) !important;
    }

    .kpi-title {
        font-size: 0.88rem !important;
        font-weight: 600 !important;
        color: #1A2A44 !important;
        margin: 0 !important;
    }

    .kpi-number {
        font-size: 2.25rem !important;
        font-weight: 700 !important;
        color: #1A2A44 !important;
        text-align: left !important;
        line-height: 1 !important;
        margin: 0 !important;
    }

    .kpi-icon {
        color: #1A2A44 !important;
        font-size: 1.5rem !important;
        opacity: 0.85 !important;
    }

    /* Action Portal Cards (Fixed height, centered flex layout) */
    .action-portal-card {
        background-color: #FFFFFF !important;
        border: 1px solid #C5C6CE !important;
        border-radius: 4px !important;
        border-top: 3px solid transparent !important;
        box-shadow: 0px 4px 20px rgba(26, 42, 68, 0.05) !important;
        padding: 1.25rem 1rem !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        min-height: 115px !important;
        height: 100% !important;
        text-decoration: none !important;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-top 0.2s ease !important;
    }

    .action-portal-card:hover {
        border-top: 3px solid #C5A059 !important;
        transform: translateY(-2px) !important;
        box-shadow: 0px 8px 24px rgba(26, 42, 68, 0.08) !important;
    }

    /* Data Table Design System */
    .card-academic-table {
        background-color: #FFFFFF !important;
        border-radius: 4px !important;
        border: 1px solid #C5C6CE !important;
        border-top: 3px solid #C5A059 !important;
        box-shadow: 0px 4px 20px rgba(26, 42, 68, 0.05) !important;
        overflow: hidden !important;
    }

    .table-header-slab {
        padding: 1rem 1.5rem !important;
        background-color: #FFFFFF !important;
        border-bottom: 1px solid #C5C6CE !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
    }
</style>

<!-- 1. INSTITUTIONAL WELCOME BANNER -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm text-white p-4" style="background: linear-gradient(135deg, var(--primary), var(--primary-container)); border-radius: 4px; border-top: 3px solid var(--heritage-gold);">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-none d-md-flex align-items-center justify-content-center bg-white bg-opacity-10 rounded-circle" style="width: 56px; height: 56px; border: 1.5px solid var(--heritage-gold);">
                        <i class="fa-solid fa-graduation-cap fs-3" style="color: var(--heritage-gold-light);"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-1 text-white" style="font-size: 1.5rem; text-shadow: 0 0.5pt 1px rgba(197, 160, 89, 0.4);">
                            مرحباً بك، {{ $universityName }}!
                        </h3>
                        <p class="mb-0 text-white-50 fs-7">
                            البوابة الإلكترونية المخصصة للجامعات السورية لتقديم ومتابعة معاملات تعادل الشهادات العلمية والتدريس.
                        </p>
                    </div>
                </div>
                <div class="bg-white bg-opacity-10 p-2.5 px-3 rounded text-center border" style="border-color: rgba(197, 160, 89, 0.3) !important;">
                    <span class="fs-8 text-white-50 d-block mb-1 fw-medium">حالة الاعتماد الأكاديمي</span>
                    @if($user->card_status === 'yellow_card')
                        <span class="badge-status badge-paper fs-7 px-3 py-1.5 fw-bold">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i> بطاقة صفراء (مراجعة الإدارة)
                        </span>
                    @else
                        <span class="badge-status badge-approved fs-7 px-3 py-1.5 fw-bold">
                            <i class="fa-solid fa-circle-check me-1"></i> معتمد ونشط رسمياً
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>
<!-- Success Flash Alert with Direct PDF Download Link -->
@if(session('success'))
<div class="alert border-0 shadow-sm d-flex flex-wrap align-items-center justify-content-between mb-4 p-3.5" role="alert" style="background-color: #E6F4EA; border-right: 4px solid #137333 !important; border-radius: 4px;">
    <div class="d-flex align-items-center gap-3">
        <i class="fa-solid fa-circle-check fs-2" style="color: #137333;"></i>
        <div>
            <h6 class="fw-bold mb-1" style="color: #137333; font-size: 1.05rem;">{{ session('success') }}</h6>
            <p class="mb-0 small text-muted">يمكنك الآن تحميل وطباعة تقرير الطلب ومذكرة العرض الرسمية المعتمدة وتسليمها مع ملف الثبوتيات.</p>
        </div>
    </div>
    @if(session('submitted_app_id'))
    <div class="mt-2 mt-md-0">
        <a href="{{ route('university.applications.download_pdf', session('submitted_app_id')) }}" target="_blank" class="btn btn-gold-cta px-3 py-2 fw-bold text-decoration-none shadow-sm">
            <i class="fa-solid fa-file-pdf me-1 fs-5"></i> 📥 تحميل وطباعة تقرير الطلب (PDF)
        </a>
    </div>
    @endif
</div>
@endif

<!-- Yellow Card Alert Notice -->
@if($user->card_status === 'yellow_card')
<div class="alert border-0 shadow-sm d-flex align-items-center mb-4 p-3" role="alert" style="background-color: var(--warning-container); color: var(--on-warning-container); border-right: 4px solid var(--heritage-gold) !important; border-radius: 4px;">
    <i class="fa-solid fa-circle-exclamation fs-3 me-3" style="color: var(--warning);"></i>
    <div>
        <h6 class="alert-heading fw-bold mb-1" style="color: var(--warning);">⚠️ تنبيه رسمي: حساب الجامعة لديه بطاقة صفراء مؤقتة</h6>
        <p class="mb-0 small">يمكنك مواصلة تقديم ومتابعة الطلبات، ولكن يرجى استكمال النواقص المطلوبة والتواصل مع إدارة التعادل لرفع التجميد الجزئي.</p>
    </div>
</div>
@endif

<!-- 2. SECTION 1: KPI CARDS (MATCHING ADMIN DASHBOARD CARDS & ICON STYLES EXACTLY) -->
<div class="row g-4 mb-4">
    <!-- Card 1: Total Applications -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="kpi-card-academic">
            <div class="d-flex justify-content-between align-items-start">
                <span class="kpi-title">إجمالي معاملات التعادل</span>
                <i class="fa-solid fa-folder-open kpi-icon"></i>
            </div>
            <div class="kpi-number">{{ number_format($totalApps) }}</div>
        </div>
    </div>

    <!-- Card 2: Under Study Count -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="kpi-card-academic">
            <div class="d-flex justify-content-between align-items-start">
                <span class="kpi-title">طلبات قيد الدراسة</span>
                <i class="fa-solid fa-hourglass-half kpi-icon"></i>
            </div>
            <div class="kpi-number">{{ number_format($underStudyCount) }}</div>
        </div>
    </div>

    <!-- Card 3: Suspended / Committee Count -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="kpi-card-academic">
            <div class="d-flex justify-content-between align-items-start">
                <span class="kpi-title">مواضيع اللجنة العامة (معلق)</span>
                <i class="fa-solid fa-pause kpi-icon"></i>
            </div>
            <div class="kpi-number">{{ number_format($suspendedCount) }}</div>
        </div>
    </div>

    <!-- Card 4: Approved Decisions -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="kpi-card-academic">
            <div class="d-flex justify-content-between align-items-start">
                <span class="kpi-title">الطلبات المقبولة / الصادرة</span>
                <i class="fa-solid fa-circle-check kpi-icon"></i>
            </div>
            <div class="kpi-number">{{ number_format($approvedCount) }}</div>
        </div>
    </div>
</div>

<!-- 3. QUICK ACTION PORTALS (GRID) -->
<div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="headline-md mb-0" style="font-size: 1.15rem; color: #1A2A44;">
        <i class="fa-solid fa-cubes me-2" style="color: #C5A059;"></i> بوابات الإجراءات السريعة
    </h5>
    <span class="label-sm text-muted">خدمات تعادل الشهادات والتدريس</span>
</div>

<div class="row g-3 mb-4">
    <!-- Quick Action 1: New Equivalence -->
    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
        <a href="{{ route('university.apply.options') }}" class="action-portal-card text-center">
            <i class="fa-solid fa-file-circle-plus fs-2 mb-2" style="color: #1A2A44;"></i>
            <h6 class="fw-bold mb-1" style="color: #1A2A44; font-size: 0.92rem;">تعادل جديد</h6>
            <p class="label-sm text-muted mb-0">تقديم طلب جديد</p>
        </a>
    </div>

    <!-- Quick Action 2: Transfer Equivalence -->
    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
        <a href="#" class="action-portal-card text-center" data-bs-toggle="modal" data-bs-target="#transferModal">
            <i class="fa-solid fa-arrows-spin fs-2 mb-2" style="color: #1A2A44;"></i>
            <h6 class="fw-bold mb-1" style="color: #1A2A44; font-size: 0.92rem;">تحويل معادلة</h6>
            <p class="label-sm text-muted mb-0">نقل من جامعة لأخرى</p>
        </a>
    </div>

    <!-- Quick Action 3: Add Courses -->
    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
        <a href="#" class="action-portal-card text-center" data-bs-toggle="modal" data-bs-target="#addCourseModal">
            <i class="fa-solid fa-book-medical fs-2 mb-2" style="color: #1A2A44;"></i>
            <h6 class="fw-bold mb-1" style="color: #1A2A44; font-size: 0.92rem;">إضافة مقررات</h6>
            <p class="label-sm text-muted mb-0">ربط وتحديد المقررات</p>
        </a>
    </div>

    <!-- Quick Action 4: Messages -->
    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
        <a href="{{ route('university.messages') }}" class="action-portal-card text-center">
            <div class="position-relative d-inline-block mb-2">
                <i class="fa-solid fa-comments fs-2" style="color: #1A2A44;"></i>
                @if($notifications->count() > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                        {{ $notifications->count() }}
                    </span>
                @endif
            </div>
            <h6 class="fw-bold mb-1" style="color: #1A2A44; font-size: 0.92rem;">المراسلات والإشعارات</h6>
            <p class="label-sm text-muted mb-0">التواصل مع الوزارة</p>
        </a>
    </div>

    <!-- Quick Action 5: Quick Search -->
    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
        <a href="#" class="action-portal-card text-center" data-bs-toggle="modal" data-bs-target="#searchModal">
            <i class="fa-solid fa-magnifying-glass fs-2 mb-2" style="color: #1A2A44;"></i>
            <h6 class="fw-bold mb-1" style="color: #1A2A44; font-size: 0.92rem;">بحث سريع</h6>
            <p class="label-sm text-muted mb-0">بالاسم أو الرقم الوطني</p>
        </a>
    </div>

    <!-- Quick Action 6: Required Documents -->
    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
        <a href="{{ route('university.required_documents') }}" class="action-portal-card text-center">
            <i class="fa-solid fa-folder-closed fs-2 mb-2" style="color: #1A2A44;"></i>
            <h6 class="fw-bold mb-1" style="color: #1A2A44; font-size: 0.92rem;">الأوراق المطلوبة</h6>
            <p class="label-sm text-muted mb-0">دليل الثبوتيات والمستندات</p>
        </a>
    </div>
</div>

@if(isset($draftApplications) && $draftApplications->count() > 0)
<!-- DRAFTS SECTION (المسودات المحفوظة قيد الإعداد) -->
<div class="card-academic-table mb-4" style="border-top: 4px solid var(--heritage-gold) !important;">
    <div class="table-header-slab d-flex flex-wrap align-items-center justify-content-between gap-3 bg-white p-3 border-bottom">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-floppy-disk fs-5 text-warning"></i>
            <h6 class="fw-bold mb-0" style="color: var(--imperial-navy);">
                المسودات والطلبات المحفوظة (غير المكتملة)
            </h6>
            <span class="badge bg-warning text-dark fw-bold rounded-pill px-2.5 py-1 fs-8">{{ $draftApplications->count() }} مسودة</span>
        </div>
        <p class="text-muted small mb-0">يمكنك العودة لاستكمال إدخال البيانات المتبقية ورفع المرفقات الناقصة ثم إرسال المعاملة أصولاً للوزارة.</p>
    </div>
    <div class="table-responsive">
        <table class="table-academic">
            <thead>
                <tr>
                    <th>رقم المسودة</th>
                    <th>اسم المرشح</th>
                    <th>نوع التعادل</th>
                    <th>الكلية والفرع</th>
                    <th>حالة المسودة</th>
                    <th>آخر تحديث</th>
                    <th class="text-center" style="width: 210px;">إجراءات المسودة</th>
                </tr>
            </thead>
            <tbody>
                @foreach($draftApplications as $draft)
                <tr>
                    <td class="fw-bold text-muted">#Draft-{{ $draft->id }}</td>
                    <td class="fw-bold" style="color: var(--imperial-navy);">{{ optional($draft->candidate)->full_name ?? 'مرشح جديد' }}</td>
                    <td><span class="badge bg-light text-dark border">{{ $draft->request_type ?? 'تعادل' }}</span></td>
                    <td class="fs-7 text-muted">{{ $draft->work_faculty ?? 'غ/م' }} - {{ $draft->work_department ?? 'غ/م' }}</td>
                    <td>
                        <span class="badge-status badge-study bg-warning-subtle text-warning border border-warning">
                            <i class="fa-solid fa-pen me-1"></i> مسودة (بانتظار الاستكمال)
                        </span>
                    </td>
                    <td class="fs-8 text-muted">{{ $draft->updated_at ? $draft->updated_at->format('Y-m-d H:i') : '' }}</td>
                    <td class="text-center">
                        <div class="d-flex align-items-center justify-content-center gap-2">
                            <a href="{{ route('university.applications.edit', $draft->id) }}" class="btn btn-sm btn-solid-navy px-3 py-1 fs-8 fw-bold">
                                <i class="fa-solid fa-pen-to-square me-1"></i> استكمال الطلب
                            </a>
                            <form action="{{ route('university.applications.delete_draft', $draft->id) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('هل أنت تأكد من إغلاق وحذف هذه المسودة؟');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger px-2 py-1 fs-8" title="حذف المسودة">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- 4. RECENT APPLICATIONS DATA TABLE WITH NUDGE (حث الطلب) SERVICE -->
<div class="card-academic-table mb-4">
    <div class="table-header-slab d-flex flex-wrap align-items-center justify-content-between gap-3">
        <!-- 1. RIGHT: SECTION TITLE -->
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-list-check fs-5" style="color: #C5A059;"></i>
            <h6 class="fw-bold mb-0" style="color: #1A2A44; font-size: 1.05rem;">
                آخر الطلبات المقدمة من الجامعة لمجلس التعليم العالي
            </h6>
        </div>

        <!-- 2. CENTER: SEARCH BOX (IDENTICAL TO ADMIN PAGE SEARCH BAR) -->
        <div class="flex-grow-1 mx-md-3" style="max-width: 420px;">
            <form action="{{ route('university.dashboard') }}" method="GET" class="position-relative m-0">
                <div class="input-group input-group-sm shadow-sm" style="border-radius: 4px; overflow: hidden; border: 1px solid #C5C6CE;">
                    <span class="input-group-text bg-white border-0 ps-3 pe-2 text-muted">
                        <i class="fa-solid fa-magnifying-glass" style="color: #C5A059;"></i>
                    </span>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search', '') }}" 
                           class="form-control border-0 bg-white shadow-none ps-1" 
                           placeholder="البحث باسم المرشح أو رقم المعاملة أو الكلية..." 
                           style="font-size: 0.88rem;">
                    @if(request('search'))
                        <a href="{{ route('university.dashboard') }}" 
                           class="input-group-text bg-white border-0 text-muted px-2 text-decoration-none" title="مسح البحث">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    @endif
                    <button type="submit" class="btn btn-gold-cta px-3 fw-bold border-0">بحث</button>
                </div>
            </form>
        </div>

        <!-- 3. LEFT: NEW APPLICATION BUTTON -->
        <div>
            <a href="{{ route('university.apply.options') }}" class="btn btn-gold-cta py-1.5 px-3 btn-sm">
                <i class="fa-solid fa-plus me-1"></i> تقديم طلب جديد
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table-academic">
            <thead>
                <tr>
                    <th style="width: 90px;">رقم الطلب</th>
                    <th>اسم المرشح (الطالب)</th>
                    <th>نوع التعادل</th>
                    <th>القسم المرشح له</th>
                    <th>حالة الطلب الحالية</th>
                    <th>تاريخ التقديم</th>
                    <th style="width: 220px;" class="text-center">الإجراءات والخدمات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentApplications as $app)
                <tr>
                    <td class="fw-bold" style="color: #C5A059;">{{ $app->application_no ?? $app->id }}</td>
                    <td class="fw-bold" style="color: #1A2A44;">{{ $app->candidate->full_name ?? 'غ/م' }}</td>
                    <td>
                        <span class="badge bg-light text-dark border fw-medium px-2 py-1 label-sm">{{ $app->request_type ?? 'تعادل' }}</span>
                    </td>
                    <td class="fs-7 text-muted">{{ $app->work_faculty ?? '' }} - {{ $app->work_department ?? 'غ/م' }}</td>
                    <td>
                        @if($app->status == 'تحت التدقيق الأولي' || $app->status == 'قيد الدراسة')
                            <span class="badge-status badge-study">
                                <i class="fa-solid fa-hourglass-half me-1"></i> تحت التدقيق الأولي
                            </span>
                        @elseif($app->status == 'بانتظار الوثائق')
                            <span class="badge-status badge-paper">
                                <i class="fa-solid fa-file-circle-exclamation me-1"></i> بانتظار الوثائق
                            </span>
                        @elseif($app->status == 'تم الصدور' || $app->status == 'موافقة')
                            <span class="badge-status badge-approved">
                                <i class="fa-solid fa-circle-check me-1"></i> تم الصدور
                            </span>
                        @elseif($app->status == 'معلق')
                            <span class="badge-status badge-suspended">
                                <i class="fa-solid fa-pause me-1"></i> معلق
                            </span>
                        @elseif($app->status == 'مرفوض')
                            <span class="badge-status badge-rejected">
                                <i class="fa-solid fa-xmark me-1"></i> مرفوض
                            </span>
                        @else
                            <span class="badge-status badge-study">{{ $app->status }}</span>
                        @endif
                    </td>
                    <td class="text-muted label-sm">{{ $app->created_at ? $app->created_at->format('Y-m-d') : 'غ/م' }}</td>
                    <td class="text-center">
                        <div class="d-flex align-items-center justify-content-center gap-1.5">
                            {{-- 0. معاينة استعراض كامل تفاصيل الطلب (أيقونة عين - يفتح صفحة مستقلة مباشرة) --}}
                            <a href="{{ route('university.applications.show', $app->id) }}" class="btn btn-sm btn-outline-info px-2 py-1 shadow-sm" title="فتح صفحة مستقلة لمعاينة كافة تفاصيل المعاملة والمؤهلات">
                                <i class="fa-solid fa-eye"></i>
                            </a>

                            {{-- 1. تعديل / استكمال الوثائق (أيقونة فقط) --}}
                            @if($app->status == 'بانتظار الوثائق')
                                <a href="{{ route('university.applications.edit', $app->id) }}" class="btn btn-sm btn-warning px-2 py-1 shadow-sm" title="تعديل البيانات واستكمال الوثائق المطلوبة">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                            @endif

                            {{-- 2. تحميل قرار التعادل الوزاري الصادر (أيقونة فقط) --}}
                            @if($app->latestDecision)
                                <a href="{{ asset('storage/' . $app->latestDecision->file_path) }}" target="_blank" class="btn btn-sm btn-gold-cta px-2 py-1 text-decoration-none shadow-sm" title="تحميل وتنزيل قرار التعادل الصادر">
                                    <i class="fa-solid fa-stamp" style="color: var(--imperial-navy);"></i>
                                </a>
                            @endif

                            {{-- 3. تحميل تقرير ومذكرة عرض الطلب PDF (أيقونة فقط) --}}
                            <a href="{{ route('university.applications.download_pdf', $app->id) }}" target="_blank" class="btn btn-sm btn-outline-danger px-2 py-1 text-decoration-none shadow-sm" title="تحميل وطباعة تقرير ومذكرة عرض الطلب (PDF)">
                                <i class="fa-solid fa-file-pdf"></i>
                            </a>

                            {{-- 4. حث وتذكير المعاملة (أيقونة فقط) --}}
                            <form action="{{ route('university.applications.nudge', $app->id) }}" method="POST" class="d-inline m-0">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-gold px-2 py-1 shadow-sm" title="إرسال طلب حث واستعجال دراسة هذه المعاملة للوزارة">
                                    <i class="fa-solid fa-bell"></i>
                                </button>
                            </form>

                            {{-- 5. المراسلات والإشعارات (أيقونة فقط) --}}
                            <a href="{{ route('university.messages') }}?application_id={{ $app->id }}" class="btn btn-sm btn-outline-navy px-2 py-1 shadow-sm" title="مراسلة الوزارة ومتابعة الملاحظات حول هذا الطلب">
                                <i class="fa-solid fa-comments"></i>
                            </a>
                        </div>

                        <!-- Read-Only Application View Modal -->
                        <div class="modal fade text-start" id="viewAppModal{{ $app->id }}" tabindex="-1" aria-labelledby="viewAppModalLabel{{ $app->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-xl">
                                <div class="modal-content border-0 shadow-lg">
                                    <div class="modal-header text-white" style="background: linear-gradient(135deg, #071526 0%, #152B47 100%); border-bottom: 3px solid var(--heritage-gold);">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="d-inline-flex align-items-center justify-content-center p-1.5 rounded-circle bg-white shadow-sm" style="border: 2px solid var(--heritage-gold); width: 42px; height: 42px;">
                                                <i class="fa-solid fa-file-contract fs-5" style="color: var(--imperial-navy);"></i>
                                            </div>
                                            <div>
                                                <h6 class="modal-title fw-bold text-white mb-0" id="viewAppModalLabel{{ $app->id }}">
                                                    معاينة واستعراض كامل بيانات المعاملة (عرض فقط - غير قابل للتعديل)
                                                </h6>
                                                <small class="text-white-50 fs-8">رقم المعاملة: {{ $app->application_no }} | المرشح: {{ optional($app->candidate)->full_name }}</small>
                                            </div>
                                        </div>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-4 bg-light" style="max-height: 78vh; overflow-y: auto;">
                                        
                                        <!-- Status & Quick PDF Bar -->
                                        <div class="bg-white p-3 rounded shadow-sm border mb-4 d-flex flex-wrap justify-content-between align-items-center gap-2" style="border-right: 4px solid var(--heritage-gold) !important;">
                                            <div class="d-flex align-items-center gap-3">
                                                <span class="fs-7 fw-bold text-dark">حالة الطلب الحالية:</span>
                                                <span class="badge bg-warning text-dark fw-bold fs-7">{{ $app->status }}</span>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('university.applications.download_pdf', $app->id) }}" target="_blank" class="btn btn-sm btn-outline-danger fw-bold">
                                                    <i class="fa-solid fa-file-pdf me-1"></i> مذكرة العرض PDF
                                                </a>
                                                <a href="{{ route('university.applications.download_consolidated_pdf', $app->id) }}" target="_blank" class="btn btn-sm btn-outline-gold fw-bold">
                                                    <i class="fa-solid fa-file-zipper me-1"></i> المرفقات المدمجة PDF
                                                </a>
                                            </div>
                                        </div>

                                        <!-- 1. Personal Candidate Information -->
                                        <div class="bg-white p-3.5 rounded shadow-sm border mb-4">
                                            <h6 class="fw-bold mb-3 pb-2 border-bottom" style="color: var(--imperial-navy);">
                                                <i class="fa-solid fa-user-graduate me-1" style="color: var(--heritage-gold);"></i> 1. البيانات الشخصية للمرشح
                                            </h6>
                                            <div class="row g-3 fs-7">
                                                <div class="col-md-4">
                                                    <span class="text-muted d-block fs-8">الاسم الكامل:</span>
                                                    <span class="fw-bold text-dark">{{ optional($app->candidate)->full_name ?? 'غ/م' }}</span>
                                                </div>
                                                <div class="col-md-4">
                                                    <span class="text-muted d-block fs-8">اسم الأب:</span>
                                                    <span class="fw-bold text-dark">{{ optional($app->candidate)->father_name ?? 'غ/م' }}</span>
                                                </div>
                                                <div class="col-md-4">
                                                    <span class="text-muted d-block fs-8">اسم الأم:</span>
                                                    <span class="fw-bold text-dark">{{ optional($app->candidate)->mother_name ?? 'غ/م' }}</span>
                                                </div>
                                                <div class="col-md-4">
                                                    <span class="text-muted d-block fs-8">الرقم الوطني:</span>
                                                    <span class="fw-bold text-dark">{{ optional($app->candidate)->national_id ?? 'غ/م' }}</span>
                                                </div>
                                                <div class="col-md-4">
                                                    <span class="text-muted d-block fs-8">تاريخ الميلاد:</span>
                                                    <span class="fw-bold text-dark">{{ optional($app->candidate)->dob ?? 'غ/م' }}</span>
                                                </div>
                                                <div class="col-md-4">
                                                    <span class="text-muted d-block fs-8">الجنسية:</span>
                                                    <span class="fw-bold text-dark">{{ optional(optional($app->candidate)->nationality)->name ?? 'سوري' }}</span>
                                                </div>
                                                <div class="col-md-6">
                                                    <span class="text-muted d-block fs-8">الكلية والفرع المرشح له:</span>
                                                    <span class="fw-bold text-dark">{{ $app->work_faculty }} - {{ $app->work_department }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- 2. Academic Degrees Summary -->
                                        <div class="bg-white p-3.5 rounded shadow-sm border mb-4">
                                            <h6 class="fw-bold mb-3 pb-2 border-bottom" style="color: var(--imperial-navy);">
                                                <i class="fa-solid fa-graduation-cap me-1" style="color: var(--heritage-gold);"></i> 2. المؤهلات الأكاديمية المدخلة بالطلب
                                            </h6>
                                            <div class="d-flex flex-column gap-3">
                                                @forelse($app->educations as $ed)
                                                    <div class="p-3 rounded border bg-light">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <span class="badge bg-navy text-white fw-bold fs-8" style="background-color: var(--imperial-navy);">
                                                                {{ optional($ed->level)->name ?? 'مؤهل أكاديمي' }}
                                                            </span>
                                                            <span class="text-muted fs-8">الدولة: {{ optional($ed->country)->name ?? 'غ/م' }}</span>
                                                        </div>
                                                        <div class="row g-2 fs-8 text-dark">
                                                            <div class="col-md-4"><strong>الجامعة المانحة:</strong> {{ optional($ed->university)->name ?? ($ed->section_name ?? 'غ/م') }}</div>
                                                            <div class="col-md-4"><strong>الاختصاص العام:</strong> {{ $ed->general_specialization ?? 'غ/م' }}</div>
                                                            <div class="col-md-4"><strong>الاختصاص الدقيق:</strong> {{ $ed->exact_specialization ?? 'غ/م' }}</div>
                                                            @if($ed->thesis_title)
                                                                <div class="col-12 mt-1"><strong>عنوان الأطروحة:</strong> {{ $ed->thesis_title }}</div>
                                                            @endif
                                                        </div>

                                                        <!-- Attachments List -->
                                                        @if($ed->attachments && $ed->attachments->count() > 0)
                                                            <div class="mt-2 pt-2 border-top d-flex flex-wrap gap-2">
                                                                <span class="fs-8 fw-bold text-muted me-1"><i class="fa-solid fa-paperclip"></i> المرفقات والوثائق:</span>
                                                                @foreach($ed->attachments as $att)
                                                                    <a href="{{ asset('storage/' . $att->file_path) }}" target="_blank" class="btn btn-xs btn-outline-secondary py-0 px-2 fs-8">
                                                                        <i class="fa-solid fa-file-pdf text-danger me-1"></i> {{ optional($att->attachmentType)->name ?? 'مرفق' }}
                                                                    </a>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>
                                                @empty
                                                    <p class="text-muted fs-7 mb-0">لا توجد مؤهلات مسجلة.</p>
                                                @endforelse
                                            </div>
                                        </div>

                                    </div>
                                    <div class="modal-footer bg-white">
                                        <button type="button" class="btn btn-secondary px-4 btn-sm" data-bs-dismiss="modal">إغلاق النافذة</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted fs-7">
                        <i class="fa-regular fa-folder-open d-block fs-3 mb-2 text-muted"></i>
                        لا توجد طلبات مقدمة حالياً من قبل الكلية أو الجامعة.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- ================= MODALS ================= -->

<!-- Transfer Modal -->
<div class="modal fade" id="transferModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold"><i class="fa-solid fa-arrows-spin me-1 text-primary"></i> طلب تحويل معاملة تعادل</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="p-3 rounded-circle d-inline-flex mb-3" style="background-color: var(--secondary-container);">
                    <i class="fa-solid fa-hourglass-start fs-2" style="color: var(--heritage-gold-dark);"></i>
                </div>
                <h6 class="fw-bold mb-2" style="color: var(--primary-container);">بوابة تحويل المعاملات بين الجامعات قيد التطوير</h6>
                <p class="text-muted small mb-0">يرجى مراجعة إدارة التعادل يدوياً في الوزارة لحين إتاحة الخدمة الإلكترونية المباشرة.</p>
            </div>
            <div class="modal-footer bg-light p-2 justify-content-end">
                <button type="button" class="btn btn-sm btn-outline-navy" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Course Modal -->
<div class="modal fade" id="addCourseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold"><i class="fa-solid fa-book-medical me-1 text-primary"></i> إضافة مقررات إضافية للمرشحين</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="p-3 rounded-circle d-inline-flex mb-3" style="background-color: var(--secondary-container);">
                    <i class="fa-solid fa-hourglass-start fs-2" style="color: var(--heritage-gold-dark);"></i>
                </div>
                <h6 class="fw-bold mb-2" style="color: var(--primary-container);">بوابة تعديل المقررات بعد تقديم الطلب</h6>
                <p class="text-muted small mb-0">يمكنك تحديد وإضافة مقررات المرشح التدريسية حالياً أثناء إنشاء المعاملة في الخطوة الخامسة من المنشئ.</p>
            </div>
            <div class="modal-footer bg-light p-2 justify-content-end">
                <button type="button" class="btn btn-sm btn-outline-navy" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

<!-- Search Modal -->
<div class="modal fade" id="searchModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #071526 0%, #152B47 100%); border-bottom: 3px solid var(--heritage-gold);">
                <h6 class="modal-title fw-bold text-white"><i class="fa-solid fa-magnifying-glass me-1" style="color: var(--heritage-gold);"></i> بحث سريع واستعراض المعاملات (عرض فقط)</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-white">
                <form action="{{ route('university.dashboard') }}" method="GET">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark fs-7">اسم المرشح / الرقم الوطني / رقم المعاملة :</label>
                        <input type="text" name="search" class="form-control academic-input" placeholder="أدخل الاسم أو الرقم الوطني أو رقم الطلب..." required>
                    </div>
                    <button type="submit" class="btn btn-solid-navy w-100 py-2 fw-bold shadow-sm">
                        <i class="fa-solid fa-magnifying-glass me-1"></i> بحث واستیفاء البيانات للمعاملة
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Required Papers Modal (Full 6 Categories) -->
<div class="modal fade" id="requiredPapersModal" tabindex="-1" aria-labelledby="requiredPapersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #071526 0%, #152B47 100%); border-bottom: 3.5px solid var(--heritage-gold);">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-inline-flex align-items-center justify-content-center p-1.5 rounded-circle bg-white shadow-sm" style="border: 2px solid var(--heritage-gold); width: 44px; height: 44px;">
                        <i class="fa-solid fa-folder-open fs-5" style="color: var(--imperial-navy);"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold text-white mb-0" id="requiredPapersModalLabel">
                            دليل الثبوتيات والمستندات المطلوبة للتعادل (القرار رقم 150/ص)
                        </h6>
                        <small class="text-white-50 fs-8">وزارة التعليم العالي والبحث العلمي - مجلس التعليم العالي | الجمهورية العربية السورية</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4 bg-light" style="max-height: 76vh; overflow-y: auto;">
                
                <!-- Category Tabs Navigation -->
                <ul class="nav nav-pills nav-fill gap-1 bg-white p-2 rounded shadow-sm border mb-4" id="modalDocTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold py-2 fs-8" id="modal-sy-master-tab" data-bs-toggle="tab" data-bs-target="#modal-sy-master" type="button" role="tab">
                            🎓 ماجستير سوري
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold py-2 fs-8" id="modal-foreign-master-tab" data-bs-toggle="tab" data-bs-target="#modal-foreign-master" type="button" role="tab">
                            🌍 ماجستير خارجي
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold py-2 fs-8" id="modal-sy-phd-tab" data-bs-toggle="tab" data-bs-target="#modal-sy-phd" type="button" role="tab">
                            🏛️ دكتوراه سورية
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold py-2 fs-8" id="modal-foreign-phd-tab" data-bs-toggle="tab" data-bs-target="#modal-foreign-phd" type="button" role="tab">
                            🔬 دكتوراه خارجية وإنتاج
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold py-2 fs-8" id="modal-gov-faculty-tab" data-bs-toggle="tab" data-bs-target="#modal-gov-faculty" type="button" role="tab">
                            👨‍🏫 أعضاء الهيئة التدريسية
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold py-2 fs-8" id="modal-general-rules-tab" data-bs-toggle="tab" data-bs-target="#modal-general-rules" type="button" role="tab">
                            ⚠️ التصديقات الخارجية
                        </button>
                    </li>
                </ul>

                <!-- Tab Contents -->
                <div class="tab-content text-start" id="modalDocTabsContent">
                    
                    <!-- 1. M.Sc Syrian -->
                    <div class="tab-pane fade show active" id="modal-sy-master" role="tabpanel">
                        <div class="bg-white p-3.5 rounded border shadow-sm" style="border-top: 4px solid var(--imperial-navy) !important;">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                <h6 class="fw-bold mb-0 text-dark">الوثائق المطلوبة لتعادل درجة الماجستير (جامعة سورية):</h6>
                                <span class="badge bg-success-subtle text-success fs-8">الرسم: 100,000 ل.س</span>
                            </div>
                            <ol class="fs-7 text-dark mb-0 ps-3">
                                <li class="mb-2">كتاب صادر عن الجامعة يتضمن طلب تقويم درجاته العلمية.</li>
                                <li class="mb-2">نسخة مصدقة أصولاً عن شهادة الدراسة الثانوية السورية.</li>
                                <li class="mb-2">نسخة مصدقة أصولاً عن الإجازة الجامعية الأولى.</li>
                                <li class="mb-2">نسخة مصدقة أصولاً عن شهادة الماجستير.</li>
                                <li class="mb-2">وثيقة مصدقة أصولاً متضمنة تاريخ التسجيل بالدرجة وتاريخ المناقشة وتاريخ الحصول على الشهادة.</li>
                                <li class="mb-2">ملخص باللغة العربية عن رسالة الماجستير إلكترونياً (PDF).</li>
                                <li class="mb-2">شهادة خبرة تدريسية لا تقل عن سنتين ما بعد الحصول على الدرجة العلمية.</li>
                                <li class="mb-2">العقود وإيصالات الرواتب مصدقة أصولاً تثبت ممارسة الخبرة.</li>
                                <li class="mb-2">شهادة اللغة الإنكليزية من المعهد العالي للغات + شهادة ICDL معتمدة من الجمعية المعلوماتية.</li>
                                <li class="mb-2">السيرة الذاتية للمرشح متضمنة عنوان إقامته وعنوانه ورقم هاتفه.</li>
                                <li class="mb-0">إيصال تسديد رسم تعادل 100,000 ل.س من حملة الماجستير.</li>
                            </ol>
                        </div>
                    </div>

                    <!-- 2. M.Sc Foreign -->
                    <div class="tab-pane fade" id="modal-foreign-master" role="tabpanel">
                        <div class="bg-white p-3.5 rounded border shadow-sm" style="border-top: 4px solid #0284c7 !important;">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                <h6 class="fw-bold mb-0 text-dark">الوثائق المطلوبة لتعادل درجة الماجستير (غير سورية - خارجي):</h6>
                                <span class="badge bg-success-subtle text-success fs-8">الرسم: 100,000 ل.س</span>
                            </div>
                            <ol class="fs-7 text-dark mb-0 ps-3">
                                <li class="mb-2">كتاب صادر عن الجامعة يتضمن طلب تقويم درجاته العلمية.</li>
                                <li class="mb-2">نسخة مصدقة أصولاً عن شهادة الدراسة الثانوية السورية.</li>
                                <li class="mb-2">نسخة مصدقة أصولاً عن الإجازة الجامعية الأولى.</li>
                                <li class="mb-2">نسخة مصدقة أصولاً عن شهادة الماجستير الخارجية.</li>
                                <li class="mb-2">وثيقة مصدقة أصولاً متضمنة تاريخ التسجيل بالدرجة وتاريخ المناقشة وتاريخ الحصول على الشهادة لكل درجتي الماجستير والبكالوريوس.</li>
                                <li class="mb-2">ملخص باللغة العربية عن رسالة الماجستير إلكترونياً.</li>
                                <li class="mb-2">شهادة اللغة الإنكليزية من المعهد العالي للغات + شهادة ICDL معتمدة.</li>
                                <li class="mb-2">صور عن جوازات السفر بالإضافة لإبراز جوازات السفر الأصلية.</li>
                                <li class="mb-2">نموذج إثبات إقامة (تعطى من مكتب التعادل) أو وثيقة حركة الهجرة والجوازات.</li>
                                <li class="mb-2">السيرة الذاتية للمرشح متضمنة عنوان إقامته ورقم هاتفه.</li>
                                <li class="mb-2">إيصال تسديد رسم تعادل 100,000 ل.س.</li>
                                <li class="mb-0">الشهادات الأصلية للشهادات كافة للمطابقة.</li>
                            </ol>
                        </div>
                    </div>

                    <!-- 3. Ph.D Syrian -->
                    <div class="tab-pane fade" id="modal-sy-phd" role="tabpanel">
                        <div class="bg-white p-3.5 rounded border shadow-sm" style="border-top: 4px solid var(--heritage-gold) !important;">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                <h6 class="fw-bold mb-0 text-dark">الوثائق المطلوبة لتعادل درجة الدكتوراه (جامعة سورية):</h6>
                                <span class="badge bg-success-subtle text-success fs-8">الرسم: 125,000 ل.س</span>
                            </div>
                            <ol class="fs-7 text-dark mb-0 ps-3">
                                <li class="mb-2">كتاب صادر عن الجامعة يتضمن طلب تقويم درجاته العلمية.</li>
                                <li class="mb-2">نسخة مصدقة أصولاً عن شهادة الدراسة الثانوية السورية.</li>
                                <li class="mb-2">نسخة مصدقة أصولاً عن الإجازة الجامعية الأولى.</li>
                                <li class="mb-2">نسخة مصدقة أصولاً عن شهادة الماجستير.</li>
                                <li class="mb-2">نسخة مصدقة أصولاً عن شهادة الدكتوراه السورية.</li>
                                <li class="mb-2">وثيقة مصدقة أصولاً متضمنة تاريخ التسجيل بالدرجة وتاريخ المناقشة وتاريخ الحصول على الشهادة لكل درجتي (الماجستير، الدكتوراه).</li>
                                <li class="mb-2">ملخص باللغة العربية عن رسالة الدكتوراه إلكترونياً.</li>
                                <li class="mb-2">شهادة اللغة الإنكليزية من المعهد العالي للغات + شهادة ICDL معتمدة من الجمعية المعلوماتية.</li>
                                <li class="mb-2">السيرة الذاتية للمرشح متضمنة عنوان إقامته ورقم هاتفه.</li>
                                <li class="mb-0">إيصال تسديد رسم تعادل 125,000 ل.س من حملة درجة الدكتوراه.</li>
                            </ol>
                        </div>
                    </div>

                    <!-- 4. Ph.D Foreign & Scientific Production -->
                    <div class="tab-pane fade" id="modal-foreign-phd" role="tabpanel">
                        <div class="bg-white p-3.5 rounded border shadow-sm" style="border-top: 4px solid #16a34a !important;">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                <h6 class="fw-bold mb-0 text-dark">الوثائق المطلوبة (للتعادل وفحص الإنتاج العلمي) للدكتوراه غير السورية:</h6>
                                <span class="badge bg-success-subtle text-success fs-8">الرسم: 125,000 ل.س</span>
                            </div>
                            <div class="row g-3 fs-7 text-dark">
                                <div class="col-md-7">
                                    <strong class="d-block mb-2 text-primary">الوثائق الأكاديمية والشخصية (13 بند):</strong>
                                    <ol class="ps-3 mb-0">
                                        <li class="mb-1">كتاب الجامعة لطلب تقويم الدرجة.</li>
                                        <li class="mb-1">الثانوية السورية مصدقة أصولاً.</li>
                                        <li class="mb-1">الإجازة الجامعية الأولى مصدقة.</li>
                                        <li class="mb-1">شهادة الماجستير مصدقة.</li>
                                        <li class="mb-1">شهادة الدكتوراه مصدقة.</li>
                                        <li class="mb-1">وثيقة تواريخ التسجيل والمناقشة للدرجتين.</li>
                                        <li class="mb-1">ملخص باللغة العربية عن الرسالة إلكترونياً.</li>
                                        <li class="mb-1">شهادة اللغة الإنكليزية + ICDL.</li>
                                        <li class="mb-1">صور جوازات السفر + الأصل.</li>
                                        <li class="mb-1">نموذج إثبات الإقامة / حركة الهجرة والجوازات.</li>
                                        <li class="mb-1">السيرة الذاتية وعنوان الإقامة.</li>
                                        <li class="mb-1">إيصال رسم التعادل 125,000 ل.س.</li>
                                        <li class="mb-0">الشهادات الأصلية كافة.</li>
                                    </ol>
                                </div>
                                <div class="col-md-5">
                                    <div class="p-3 bg-success-subtle rounded border border-success">
                                        <strong class="d-block mb-2 text-success"><i class="fa-solid fa-book me-1"></i> وثائق فحص الإنتاج العلمي للتعيين بوظيفة مدرس:</strong>
                                        <ul class="ps-3 mb-0">
                                            <li class="mb-2"><strong>ثلاث نسخ</strong> عن أطروحة الدكتوراه.</li>
                                            <li><strong>ثلاث ملخصات</strong> عن الأطروحة باللغة العربية لا يقل كل ملخص عن <strong>(25) صفحة</strong>.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 5. Government Faculty -->
                    <div class="tab-pane fade" id="modal-gov-faculty" role="tabpanel">
                        <div class="bg-white p-3.5 rounded border shadow-sm" style="border-top: 4px solid #d97706 !important;">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                <h6 class="fw-bold mb-0 text-dark">الوثائق المطلوبة لأعضاء الهيئة التدريسية بالجامعات الحكومية:</h6>
                                <span class="badge bg-success-subtle text-success fs-8">الرسم: 125,000 ل.س</span>
                            </div>
                            <ol class="fs-7 text-dark mb-0 ps-3">
                                <li class="mb-2">كتاب صادر عن الجامعة يتضمن طلب تقويم درجاته العلمية.</li>
                                <li class="mb-2">نسخة مصدقة أصولاً عن شهادة الدكتوراه.</li>
                                <li class="mb-2">بيان وضع أو بطاقة ذاتية رسمية من الجامعة الحكومية.</li>
                                <li class="mb-0">إيصال تسديد رسم تعادل 125,000 ل.س من حملة درجة الدكتوراه.</li>
                            </ol>
                        </div>
                    </div>

                    <!-- 6. General Rules -->
                    <div class="tab-pane fade" id="modal-general-rules" role="tabpanel">
                        <div class="bg-white p-3.5 rounded border shadow-sm" style="border-top: 4px solid #dc2626 !important;">
                            <h6 class="fw-bold mb-2 text-danger"><i class="fa-solid fa-shield-halved me-1"></i> الشروط القانونية وتصديقات الشهادات غير السورية:</h6>
                            <ul class="fs-7 text-dark mb-0 ps-3">
                                <li class="mb-2">تصديق كافة الوثائق والشهادات غير السورية أصولاً من <strong>وزارة الخارجية والمغتربين بالجمهورية العربية السورية</strong>.</li>
                                <li class="mb-2">الترجمة والتوثيق أصولاً من <strong>وزارة العدل</strong> للشهادات الصادرة بلغة غير العربية.</li>
                                <li class="mb-2">إرفاق إثبات الإقامة الرسمية الصادر عن مكتب التعادل أو حركة الهجرة والجوازات طيلة فترة الدراسة.</li>
                                <li class="mb-0">التزام مدير الموارد البشرية بالجامعة بإبراز كافة الشهادات الأصلية للجنة المطابقة.</li>
                            </ul>
                        </div>
                    </div>

                </div>

            </div>
            <div class="modal-footer bg-white d-flex justify-content-between align-items-center">
                <a href="{{ route('university.required_documents') }}" class="btn btn-solid-navy btn-sm fw-bold">
                    <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> فتح الصفحة الكاملة للثبوتيات والطباعة
                </a>
                <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">إغلاق النافذة</button>
            </div>
        </div>
    </div>
</div>

@endsection
