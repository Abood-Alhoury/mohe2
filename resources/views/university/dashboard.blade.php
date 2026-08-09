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
        <a href="#" class="action-portal-card text-center" data-bs-toggle="modal" data-bs-target="#requiredPapersModal">
            <i class="fa-solid fa-folder-closed fs-2 mb-2" style="color: #1A2A44;"></i>
            <h6 class="fw-bold mb-1" style="color: #1A2A44; font-size: 0.92rem;">الأوراق المطلوبة</h6>
            <p class="label-sm text-muted mb-0">دليل الثبوتيات والمستندات</p>
        </a>
    </div>
</div>

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
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold"><i class="fa-solid fa-magnifying-glass me-1 text-primary"></i> بحث سريع في معاملات الجامعة</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('university.messages') }}" method="GET">
                    <div class="mb-3">
                        <label class="form-label fw-bold">اسم المرشح أو الرقم الوطني</label>
                        <input type="text" name="search" class="form-control academic-input" placeholder="أدخل اسم المرشح للبحث عن المراسلات والطلب..." required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2">
                        <i class="fa-solid fa-search me-1"></i> بحث وعرض المحادثات
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Required Papers Modal -->
<div class="modal fade" id="requiredPapersModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold"><i class="fa-solid fa-folder-closed me-1 text-primary"></i> قائمة الثبوتيات والمستندات المطلوبة لمعادلة الماجستير</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="fw-bold mb-3" style="color: var(--primary-container);">يجب رفع صور مصدقة واضحة بصيغة PDF للمستندات التالية عند التقديم:</p>
                <ol class="list-group list-group-numbered fs-9 p-0 mb-0" style="padding-right: 0;">
                    <li class="list-group-item">صورة عن قرار معادلة الشهادة الثانوية (إذا كانت الثانوية غير سورية).</li>
                    <li class="list-group-item">صورة عن معادلة الشهادة الجامعية الأولى (إذا كانت غير سورية).</li>
                    <li class="list-group-item">كتاب صادر عن الجامعة يتضمن طلب تقويم درجاته العلمية.</li>
                    <li class="list-group-item">نسخة مصدقة أصولاً عن شهادة الدراسة الثانوية السورية.</li>
                    <li class="list-group-item">نسخة مصدقة أصولاً عن الإجازة الجامعية الأولى.</li>
                    <li class="list-group-item">نسخة مصدقة أصولاً عن شهادة الماجستير.</li>
                    <li class="list-group-item">وثيقة مصدقة أصولاً تتضمن تاريخ التسجيل بالدرجة وتاريخ المناقشة وتاريخ الحصول على الشهادة.</li>
                    <li class="list-group-item">ملخص باللغة العربية عن رسالة الماجستير.</li>
                    <li class="list-group-item">شهادة خبرة تدريسية لا تقل عن سنتين ما بعد الحصول على الدرجة العلمية.</li>
                    <li class="list-group-item">العقود وإيصالات الرواتب مصدقة أصولاً تثبت الخبرة.</li>
                    <li class="list-group-item">شهادة اللغة الإنكليزية من المعهد العالي للغات + شهادة ICDL معتمدة.</li>
                    <li class="list-group-item">السيرة الذاتية للمرشح متضمنة عنوان إقامته ورقم هاتفه.</li>
                    <li class="list-group-item">إيصال تسديد رسم تعادل بقيمة 100,000 ل.س.</li>
                </ol>
            </div>
            <div class="modal-footer bg-light p-2">
                <button type="button" class="btn btn-sm btn-outline-navy" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

@endsection
