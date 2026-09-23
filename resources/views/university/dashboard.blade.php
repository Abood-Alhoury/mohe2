@extends('layouts.university')

@section('title', 'الرئيسية - لوحة تحكم الجامعة')

@section('content')

<!-- 1. INSTITUTIONAL WELCOME BANNER -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm text-white p-4" style="background: linear-gradient(135deg, var(--primary), var(--primary-container)); border-radius: 8px; border-top: 3px solid var(--heritage-gold);">
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

<!-- Site Closed Alert Notice -->
@if(!empty($siteLocked) && $siteLocked)
<div class="alert border-0 shadow-sm d-flex align-items-center mb-4 p-3.5" role="alert" style="background-color: #FEE2E2; color: #991B1B; border-right: 4px solid #DC2626 !important; border-radius: 6px;">
    <i class="fa-solid fa-lock fs-3 me-3 text-danger"></i>
    <div>
        <h6 class="alert-heading fw-bold mb-1 text-danger"><i class="fa-solid fa-ban me-1"></i> تقديم الطلبات الجديدة مغلق حالياً لجميع الجامعات بقرار الإدارة</h6>
        <p class="mb-0 small" style="color: #7F1D1D;">{{ $siteNotice ?: 'الموقع مغلق حالياً من قبل الإدارة لتلقي طلبات جديدة. يمكنك تصفح طلباتك السابقة، طباعة البيانات والـ PDF، وإرسال المراسلات مع الوزارة بشكل طبيعي.' }}</p>
    </div>
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
    <div class="col-12 col-sm-6 col-md-3">
        @if(!empty($siteLocked) && $siteLocked)
            <div class="action-portal-card text-center opacity-75 position-relative" style="background-color: #f8fafc; border-color: #cbd5e1; cursor: not-allowed;" title="تقديم الطلبات الجديدة مغلق حالياً لجميع الجامعات بقرار الإدارة">
                <span class="position-absolute top-0 end-0 badge bg-danger m-2 fs-9"><i class="fa-solid fa-lock"></i> مغلق</span>
                <i class="fa-solid fa-file-circle-plus fs-2 mb-2 text-muted"></i>
                <h6 class="fw-bold mb-1 text-muted" style="font-size: 0.92rem;">تقديم طلب تعادل جديد</h6>
                <p class="label-sm text-danger fw-bold mb-0">مغلق للتقديم الجديد حالياً</p>
            </div>
        @else
            <a href="{{ route('university.apply.options') }}" class="action-portal-card text-center">
                <i class="fa-solid fa-file-circle-plus fs-2 mb-2" style="color: #1A2A44;"></i>
                <h6 class="fw-bold mb-1" style="color: #1A2A44; font-size: 0.92rem;">تقديم طلب تعادل جديد</h6>
                <p class="label-sm text-muted mb-0">اختر نوع درجة التعادل للبدء</p>
            </a>
        @endif
    </div>

    <!-- Quick Action 2: Messages -->
    <div class="col-12 col-sm-6 col-md-3">
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
            <p class="label-sm text-muted mb-0">التواصل مع الوزارة ومتابعة الردود</p>
        </a>
    </div>

    <!-- Quick Action 3: Drafts Management (إدارة المسودات) -->
    <div class="col-12 col-sm-6 col-md-3">
        <a href="{{ route('university.drafts.index') }}" class="action-portal-card text-center position-relative">
            <div class="position-relative d-inline-block mb-2">
                <i class="fa-solid fa-floppy-disk fs-2" style="color: #1A2A44;"></i>
                @if(isset($draftsCount) && $draftsCount > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark fw-bold" style="font-size: 0.65rem;">
                        {{ $draftsCount }}
                    </span>
                @endif
            </div>
            <h6 class="fw-bold mb-1" style="color: #1A2A44; font-size: 0.92rem;">المسودات المحفوظة</h6>
            <p class="label-sm text-muted mb-0">متابعة واستكمال الطلبات غير المكتملة</p>
        </a>
    </div>

    <!-- Quick Action 4: Required Documents -->
    <div class="col-12 col-sm-6 col-md-3">
        <a href="{{ route('university.required_documents') }}" class="action-portal-card text-center">
            <i class="fa-solid fa-folder-closed fs-2 mb-2" style="color: #1A2A44;"></i>
            <h6 class="fw-bold mb-1" style="color: #1A2A44; font-size: 0.92rem;">الأوراق والشهادات المطلوبة</h6>
            <p class="label-sm text-muted mb-0">دليل الثبوتيات والمستندات أصولاً</p>
        </a>
    </div>
</div>

<!-- 4. RECENT APPLICATIONS DATA TABLE WITH NUDGE (حث الطلب) SERVICE -->
<div class="card-academic-table mb-4" id="recent-applications-section">
    <div class="table-header-slab d-flex flex-wrap align-items-center justify-content-between gap-3">
        <!-- 1. RIGHT: SECTION TITLE -->
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-list-check fs-5" style="color: #C5A059;"></i>
            <h6 class="fw-bold mb-0" style="color: #1A2A44; font-size: 1.05rem;">
                آخر الطلبات المقدمة من الجامعة لمجلس التعليم العالي
            </h6>
        </div>

        <!-- 2. CENTER: SEARCH BOX (LIVE AJAX SEARCH) -->
        <div class="flex-grow-1 mx-md-3" style="max-width: 420px;">
            <form id="recent-apps-search-form" action="{{ route('university.dashboard') }}#recent-applications-section" method="GET" class="position-relative m-0">
                <div class="input-group input-group-sm shadow-sm" style="border-radius: 4px; overflow: hidden; border: 1px solid #C5C6CE;">
                    <span class="input-group-text bg-white border-0 ps-3 pe-2 text-muted">
                        <i class="fa-solid fa-magnifying-glass" id="search-spinner-icon" style="color: #C5A059;"></i>
                    </span>
                    <input type="text" 
                           id="recent-apps-search-input"
                           name="search" 
                           value="{{ request('search', '') }}" 
                           class="form-control border-0 bg-white shadow-none ps-1" 
                           placeholder="البحث باسم المرشح أو رقم المعاملة أو الكلية..." 
                           style="font-size: 0.88rem;"
                           autocomplete="off">
                    <button type="button" 
                            id="recent-apps-clear-btn"
                            class="input-group-text bg-white border-0 text-muted px-2" 
                            title="مسح البحث"
                            style="cursor: pointer; display: {{ request('search') ? 'inline-flex' : 'none' }};">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                    <button type="submit" id="recent-apps-submit-btn" class="btn btn-gold-cta px-3 fw-bold border-0">بحث</button>
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

    <div id="recent-applications-table-wrapper">
        @include('university.partials._recent_applications_table')
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Smoothly scroll to the applications table if arriving with a search query or hash
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('search') || window.location.hash === '#recent-applications-section') {
        const tableSection = document.getElementById('recent-applications-section');
        if (tableSection) {
            setTimeout(function() {
                tableSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);
        }
    }

    // 2. Live AJAX Search without page reload
    const searchForm = document.getElementById('recent-apps-search-form');
    const searchInput = document.getElementById('recent-apps-search-input');
    const clearBtn = document.getElementById('recent-apps-clear-btn');
    const spinnerIcon = document.getElementById('search-spinner-icon');
    const tableWrapper = document.getElementById('recent-applications-table-wrapper');

    let debounceTimer = null;
    let abortController = null;

    function executeAjaxSearch(query) {
        if (!tableWrapper) return;

        // Toggle clear button
        if (clearBtn) {
            clearBtn.style.display = query ? 'inline-flex' : 'none';
        }

        // Cancel pending request if any
        if (abortController) {
            abortController.abort();
        }
        abortController = new AbortController();

        // Show spinner
        if (spinnerIcon) {
            spinnerIcon.className = 'fa-solid fa-circle-notch fa-spin';
            spinnerIcon.style.color = '#C5A059';
        }

        // Build target URL
        const fetchUrl = new URL("{{ route('university.dashboard') }}", window.location.origin);
        if (query) {
            fetchUrl.searchParams.set('search', query);
        }
        fetchUrl.searchParams.set('ajax', '1');

        fetch(fetchUrl.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            },
            signal: abortController.signal
        })
        .then(response => {
            if (!response.ok) throw new Error('Search request failed');
            return response.text();
        })
        .then(html => {
            tableWrapper.innerHTML = html;

            // Sync URL without reloading or scrolling
            const newUrl = new URL(window.location.href);
            if (query) {
                newUrl.searchParams.set('search', query);
            } else {
                newUrl.searchParams.delete('search');
            }
            window.history.replaceState({}, '', newUrl.toString());
        })
        .catch(err => {
            if (err.name !== 'AbortError') {
                console.error('Search error:', err);
            }
        })
        .finally(() => {
            if (spinnerIcon) {
                spinnerIcon.className = 'fa-solid fa-magnifying-glass';
                spinnerIcon.style.color = '#C5A059';
            }
        });
    }

    if (searchForm && searchInput) {
        // Prevent full form submission and page reload on Enter or click 'بحث'
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (debounceTimer) clearTimeout(debounceTimer);
            executeAjaxSearch(searchInput.value.trim());
        });

        // Instant Live Search while typing (debounced by 350ms)
        searchInput.addEventListener('input', function() {
            const val = this.value.trim();
            if (clearBtn) {
                clearBtn.style.display = val ? 'inline-flex' : 'none';
            }
            if (debounceTimer) clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function() {
                executeAjaxSearch(val);
            }, 350);
        });

        // Clear button click
        if (clearBtn) {
            clearBtn.addEventListener('click', function(e) {
                e.preventDefault();
                searchInput.value = '';
                this.style.display = 'none';
                if (debounceTimer) clearTimeout(debounceTimer);
                executeAjaxSearch('');
                searchInput.focus();
            });
        }
    }
});
</script>
@endpush
