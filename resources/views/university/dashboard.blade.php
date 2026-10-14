@extends('layouts.university')

@section('title', 'الرئيسية - بوابة الجامعات للتعادل الأكاديمي')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
<style>
    :root {
        --primary-navy: #091322;
        --secondary-navy: #0e1e38;
        --accent-gold: #c59b27;
        --accent-gold-light: #f5d77f;
        --accent-gold-glow: rgba(197, 155, 39, 0.25);
        --emerald-accent: #10b981;
        --emerald-glow: rgba(16, 185, 129, 0.2);
        --bg-page: #f1f5f9;
        --surface-card: #ffffff;
        --border-subtle: #e2e8f0;
        --text-main: #0f172a;
        --text-muted: #64748b;
    }

    .dashboard-bento-wrapper {
        font-family: 'Tajawal', sans-serif;
    }

    /* HERO BENTO BANNER */
    .hero-bento-card {
        background: linear-gradient(135deg, var(--secondary-navy) 0%, #13284c 100%);
        border-radius: 16px;
        padding: 24px 30px;
        color: white;
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: relative;
        overflow: hidden;
        box-shadow: 0 8px 30px rgba(14, 30, 56, 0.12);
        border: 1px solid rgba(255,255,255,0.08);
        margin-bottom: 22px;
    }

    .hero-bento-card::before {
        content: '';
        position: absolute;
        top: -50px;
        left: -50px;
        width: 200px;
        height: 200px;
        background: radial-gradient(circle, rgba(197,155,39,0.15) 0%, transparent 70%);
        border-radius: 50%;
    }

    .hero-welcome-text h2 {
        font-size: 1.5rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 6px;
        color: #ffffff;
    }

    .hero-welcome-text p {
        color: #94a3b8;
        font-size: 0.9rem;
        margin-bottom: 0;
    }

    .hero-cta-btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background: linear-gradient(135deg, var(--accent-gold) 0%, #aa841d 100%);
        color: #0b1526 !important;
        font-weight: 800;
        font-size: 0.95rem;
        padding: 12px 24px;
        border-radius: 10px;
        border: none;
        cursor: pointer;
        box-shadow: 0 4px 15px var(--accent-gold-glow);
        transition: all 0.25s ease;
        text-decoration: none;
        white-space: nowrap;
    }

    .hero-cta-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(197, 155, 39, 0.4);
        color: #0b1526 !important;
    }

    /* 3. METRIC KPI CARDS (BENTO STYLE) */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .metric-card {
        background: var(--surface-card);
        border-radius: 14px;
        padding: 20px;
        border: 1px solid var(--border-subtle);
        box-shadow: 0 2px 8px rgba(0,0,0,0.03);
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: all 0.25s ease;
        position: relative;
        overflow: hidden;
    }

    .metric-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.06);
        border-color: #cbd5e1;
    }

    .metric-data span {
        font-size: 0.82rem;
        font-weight: 700;
        color: var(--text-muted);
        display: block;
        margin-bottom: 6px;
    }

    .metric-data strong {
        font-size: 2rem;
        font-weight: 800;
        color: var(--text-main);
        font-family: 'Plus Jakarta Sans', 'Tajawal', sans-serif;
    }

    .metric-icon-box {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
    }

    /* 4. QUICK ACTION SHORTCUTS (4 CARDS) */
    .quick-actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 16px;
        margin-bottom: 28px;
    }

    .action-card {
        background: var(--surface-card);
        border: 1.5px solid var(--border-subtle);
        border-radius: 12px;
        padding: 18px 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        text-decoration: none;
        color: var(--text-main) !important;
        transition: all 0.2s ease;
        box-shadow: 0 2px 6px rgba(0,0,0,0.02);
    }

    .action-card:hover {
        border-color: var(--accent-gold);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.05);
        color: var(--text-main) !important;
    }

    .action-icon-circle {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: var(--secondary-navy);
        transition: all 0.2s;
        flex-shrink: 0;
    }

    .action-card:hover .action-icon-circle {
        background: var(--secondary-navy);
        color: var(--accent-gold-light);
    }

    .action-card-text h4 {
        font-size: 0.95rem;
        font-weight: 800;
        margin-bottom: 3px;
        color: var(--text-main);
    }

    .action-card-text p {
        font-size: 0.78rem;
        color: var(--text-muted);
        margin-bottom: 0;
    }

    /* 5. DATA GRID & APPLICATIONS TABLE */
    .table-slab-container {
        background: var(--surface-card);
        border-radius: 16px;
        border: 1px solid var(--border-subtle);
        box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        overflow: hidden;
    }

    .table-slab-header {
        padding: 18px 24px;
        border-bottom: 1px solid var(--border-subtle);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        background: #ffffff;
    }

    .slab-title h3 {
        font-size: 1.1rem;
        font-weight: 800;
        color: var(--secondary-navy);
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 0;
    }

    .table-interactive-tools {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .search-pill-box {
        position: relative;
        width: 340px;
        max-width: 100%;
    }

    .search-pill-box input {
        width: 100%;
        padding: 10px 42px 10px 16px;
        border: 1.5px solid var(--border-subtle);
        border-radius: 30px;
        font-size: 0.88rem;
        outline: none;
        background: #f8fafc;
        transition: all 0.2s;
    }

    .search-pill-box input:focus {
        background: #fff;
        border-color: var(--secondary-navy);
        box-shadow: 0 0 0 3px rgba(14, 30, 56, 0.08);
    }

    .search-pill-box i {
        position: absolute;
        right: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
    }

    /* Modern Table Style */
    .table-responsive {
        overflow-x: auto;
    }

    table.modern-table {
        width: 100%;
        border-collapse: collapse;
        text-align: right;
        margin-bottom: 0;
    }

    table.modern-table th {
        background: #f8fafc;
        color: var(--text-muted);
        font-size: 0.82rem;
        font-weight: 700;
        padding: 14px 20px;
        border-bottom: 1.5px solid var(--border-subtle);
        white-space: nowrap;
    }

    table.modern-table td {
        padding: 16px 20px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.92rem;
        vertical-align: middle;
    }

    table.modern-table tr:hover td {
        background: #fafcff;
    }

    /* Badges */
    .status-badge-issued {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: var(--emerald-glow);
        color: #047857;
        border: 1px solid rgba(16, 185, 129, 0.4);
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.82rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .status-badge-study {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(2, 132, 199, 0.1);
        color: #0284c7;
        border: 1px solid rgba(2, 132, 199, 0.3);
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.82rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .status-badge-paper {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(217, 119, 6, 0.1);
        color: #d97706;
        border: 1px solid rgba(217, 119, 6, 0.3);
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.82rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .status-badge-suspended {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(100, 116, 139, 0.1);
        color: #64748b;
        border: 1px solid rgba(100, 116, 139, 0.3);
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.82rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .pill-degree {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #f5f3ff;
        color: #7c3aed;
        border: 1px solid #ddd6fe;
        padding: 3px 10px;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 700;
        white-space: nowrap;
    }

    /* Action Buttons */
    .action-cell-cluster {
        display: flex;
        align-items: center;
        gap: 8px;
        justify-content: center;
    }

    .btn-stamp-action {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        background: linear-gradient(135deg, var(--accent-gold) 0%, #a07812 100%);
        color: #0e1e38 !important;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 2px 8px var(--accent-gold-glow);
        transition: all 0.2s;
        font-size: 16px;
        text-decoration: none;
    }

    .btn-stamp-action:hover {
        transform: scale(1.08);
        box-shadow: 0 4px 14px rgba(197, 155, 39, 0.4);
        color: #0e1e38 !important;
    }

    .btn-soft-action {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        border: 1px solid var(--border-subtle);
        background: white;
        color: #475569 !important;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        font-size: 14px;
    }

    .btn-soft-action:hover {
        background: var(--secondary-navy);
        color: white !important;
        border-color: var(--secondary-navy);
    }
</style>
@endpush

@section('content')
<div class="dashboard-bento-wrapper">

    <!-- Site Closed Alert Notice -->
    @if(!empty($siteLocked) && $siteLocked)
    <div class="alert border-0 shadow-sm d-flex align-items-center mb-4 p-3.5" role="alert" style="background-color: #FEE2E2; color: #991B1B; border-right: 4px solid #DC2626 !important; border-radius: 12px;">
        <i class="fa-solid fa-lock fs-3 me-3 text-danger"></i>
        <div>
            <h6 class="alert-heading fw-bold mb-1 text-danger"><i class="fa-solid fa-ban me-1"></i> تقديم الطلبات الجديدة مغلق حالياً لجميع الجامعات بقرار الإدارة</h6>
            <p class="mb-0 small" style="color: #7F1D1D;">{{ $siteNotice ?: 'الموقع مغلق حالياً من قبل الإدارة لتلقي طلبات جديدة. يمكنك تصفح طلباتك السابقة، طباعة البيانات والـ PDF، وإرسال المراسلات مع الوزارة بشكل طبيعي.' }}</p>
        </div>
    </div>
    @endif

    <!-- Yellow Card Alert Notice -->
    @if(isset($user) && $user->card_status === 'yellow_card')
    <div class="alert border-0 shadow-sm d-flex align-items-center mb-4 p-3" role="alert" style="background-color: #fef3c7; color: #92400e; border-right: 4px solid var(--accent-gold) !important; border-radius: 12px;">
        <i class="fa-solid fa-circle-exclamation fs-3 me-3 text-warning"></i>
        <div>
            <h6 class="alert-heading fw-bold mb-1 text-warning">⚠️ تنبيه رسمي: حساب الجامعة لديه بطاقة صفراء مؤقتة</h6>
            <p class="mb-0 small">يمكنك مواصلة تقديم ومتابعة الطلبات، ولكن يرجى استكمال النواقص المطلوبة والتواصل مع إدارة التعادل لرفع التجميد الجزئي.</p>
        </div>
    </div>
    @endif

    <!-- 1. HERO BENTO WELCOME BANNER -->
    <section class="hero-bento-card">
        <div class="hero-welcome-text">
            <h2>
                أهلاً بك، {{ $universityName }}!
                <span style="font-size: 0.8rem; background: rgba(255,255,255,0.12); padding: 3px 10px; border-radius: 20px; font-weight: 600;">
                    جلسة معتمدة 2026
                </span>
            </h2>
            <p>مرحباً بك في البوابة الإلكترونية المخصصة لتقديم ومتابعة معاملات تعادل الشهادات العلمية والتدريس.</p>
        </div>
        <div>
            @if(!empty($siteLocked) && $siteLocked)
                <button class="hero-cta-btn opacity-50" style="cursor: not-allowed;" title="تقديم الطلبات الجديدة مغلق حالياً لجميع الجامعات بقرار الإدارة" disabled>
                    <i class="fa-solid fa-lock"></i> التقديم مغلق حالياً
                </button>
            @else
                <a href="{{ route('university.apply.options') }}" class="hero-cta-btn">
                    <i class="fa-solid fa-plus-circle"></i> تقديم معاملة تعادل جديدة
                </a>
            @endif
        </div>
    </section>

    <!-- 2. KPI METRICS (BENTO GRID) -->
    <section class="kpi-grid">
        <div class="metric-card">
            <div class="metric-data">
                <span>إجمالي معاملات التعادل</span>
                <strong>{{ number_format($totalApps) }}</strong>
            </div>
            <div class="metric-icon-box" style="background: #eff6ff; color: #1d4ed8;">
                <i class="fa-solid fa-folder-open"></i>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-data">
                <span>طلبات قيد الدراسة</span>
                <strong>{{ number_format($underStudyCount) }}</strong>
            </div>
            <div class="metric-icon-box" style="background: #fefce8; color: #ca8a04;">
                <i class="fa-solid fa-hourglass-half"></i>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-data">
                <span>مواضيع اللجنة العامة (معلق)</span>
                <strong>{{ number_format($suspendedCount) }}</strong>
            </div>
            <div class="metric-icon-box" style="background: #f1f5f9; color: #475569;">
                <i class="fa-solid fa-pause"></i>
            </div>
        </div>

        <a href="{{ route('university.archive') }}" class="metric-card" style="border-right: 4px solid var(--emerald-accent); text-decoration: none; color: inherit;" title="الانتقال إلى أرشيف القرارات والمعاملات الصادرة">
            <div class="metric-data">
                <span>القرارات الصادرة (الأرشيف)</span>
                <strong style="color: #059669;">{{ number_format($approvedCount) }}</strong>
            </div>
            <div class="metric-icon-box" style="background: var(--emerald-glow); color: var(--emerald-accent);">
                <i class="fa-solid fa-stamp"></i>
            </div>
        </a>
    </section>

    <!-- 3. QUICK ACTION CARDS (4 SHORTCUTS) -->
    <section class="quick-actions-grid">
        @if(!empty($siteLocked) && $siteLocked)
            <div class="action-card opacity-75" style="cursor: not-allowed;" title="تقديم الطلبات الجديدة مغلق حالياً لجميع الجامعات بقرار الإدارة">
                <div class="action-icon-circle">
                    <i class="fa-solid fa-lock text-danger"></i>
                </div>
                <div class="action-card-text">
                    <h4 class="text-muted">تقديم طلب تعادل جديد</h4>
                    <p class="text-danger fw-bold">مغلق للتقديم الجديد حالياً</p>
                </div>
            </div>
        @else
            <a href="{{ route('university.apply.options') }}" class="action-card">
                <div class="action-icon-circle">
                    <i class="fa-solid fa-file-circle-plus"></i>
                </div>
                <div class="action-card-text">
                    <h4>تقديم طلب تعادل جديد</h4>
                    <p>اختر نوع درجة التعادل للبدء الفوري</p>
                </div>
            </a>
        @endif

        <a href="{{ route('university.messages') }}" class="action-card position-relative">
            <div class="action-icon-circle position-relative">
                <i class="fa-solid fa-comments"></i>
                @if(isset($notifications) && $notifications->count() > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                        {{ $notifications->count() }}
                    </span>
                @endif
            </div>
            <div class="action-card-text">
                <h4>المراسلات والإشعارات</h4>
                <p>التواصل المباشر ومتابعة ردود الوزارة</p>
            </div>
        </a>

        <a href="{{ route('university.drafts.index') }}" class="action-card position-relative">
            <div class="action-icon-circle position-relative">
                <i class="fa-solid fa-box-archive"></i>
                @if(isset($draftsCount) && $draftsCount > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark fw-bold" style="font-size: 0.65rem;">
                        {{ $draftsCount }}
                    </span>
                @endif
            </div>
            <div class="action-card-text">
                <h4>المسودات المحفوظة</h4>
                <p>متابعة واستكمال الطلبات غير المكتملة</p>
            </div>
        </a>

        <a href="{{ route('university.required_documents') }}" class="action-card" data-bs-toggle="modal" data-bs-target="#requiredPapersModal">
            <div class="action-icon-circle">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div class="action-card-text">
                <h4>الأوراق والشهادات المطلوبة</h4>
                <p>دليل الثبوتيات والمستندات أصولاً</p>
            </div>
        </a>
    </section>

    <!-- 4. DATA GRID: APPLICATIONS TABLE -->
    <section class="table-slab-container" id="recent-applications-section">
        <div class="table-slab-header">
            <div class="slab-title">
                <h3><i class="fa-solid fa-table-cells" style="color: var(--accent-gold);"></i> آخر الطلبات المقدمة من الجامعة لمجلس التعليم العالي</h3>
            </div>

            <div class="table-interactive-tools">
                <div class="search-pill-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="live-filter" placeholder="ابحث باسم المرشح، الكلية، أو رقم المعاملة..." onkeyup="filterTable()">
                </div>
            </div>
        </div>

        <div class="table-responsive" id="recent-applications-table-wrapper">
            @include('university.partials._recent_applications_table')
        </div>
    </section>

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
                <div class="p-3 rounded-circle d-inline-flex mb-3" style="background-color: var(--secondary-container, #e2e8f0);">
                    <i class="fa-solid fa-hourglass-start fs-2" style="color: var(--accent-gold);"></i>
                </div>
                <h6 class="fw-bold mb-2" style="color: var(--secondary-navy);">بوابة تحويل المعاملات بين الجامعات قيد التطوير</h6>
                <p class="text-muted small mb-0">يرجى مراجعة إدارة التعادل يدوياً في الوزارة لحين إتاحة الخدمة الإلكترونية المباشرة.</p>
            </div>
            <div class="modal-footer bg-light p-2 justify-content-end">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">إغلاق</button>
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
                <div class="p-3 rounded-circle d-inline-flex mb-3" style="background-color: var(--secondary-container, #e2e8f0);">
                    <i class="fa-solid fa-hourglass-start fs-2" style="color: var(--accent-gold);"></i>
                </div>
                <h6 class="fw-bold mb-2" style="color: var(--secondary-navy);">بوابة تعديل المقررات بعد تقديم الطلب</h6>
                <p class="text-muted small mb-0">يمكنك تحديد وإضافة مقررات المرشح التدريسية حالياً أثناء إنشاء المعاملة في الخطوة الخامسة من المنشئ.</p>
            </div>
            <div class="modal-footer bg-light p-2 justify-content-end">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

<!-- Required Papers Modal (Full 6 Categories) -->
<div class="modal fade" id="requiredPapersModal" tabindex="-1" aria-labelledby="requiredPapersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #071526 0%, #152B47 100%); border-bottom: 3.5px solid var(--accent-gold);">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-inline-flex align-items-center justify-content-center p-1.5 rounded-circle bg-white shadow-sm" style="border: 2px solid var(--accent-gold); width: 44px; height: 44px;">
                        <i class="fa-solid fa-folder-open fs-5" style="color: var(--secondary-navy);"></i>
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
                        <div class="bg-white p-3.5 rounded border shadow-sm" style="border-top: 4px solid var(--secondary-navy) !important;">
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
                        <div class="bg-white p-3.5 rounded border shadow-sm" style="border-top: 4px solid var(--accent-gold) !important;">
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
                <a href="{{ route('university.required_documents') }}" class="btn btn-dark btn-sm fw-bold" style="background-color: var(--secondary-navy);">
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
    function filterTable() {
        const query = (document.getElementById('live-filter')?.value || '').toLowerCase().trim();
        const rows = document.querySelectorAll('#dashboard-table tbody tr');

        rows.forEach(row => {
            if (row.querySelector('td[colspan]')) return;
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(query) ? '' : 'none';
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const liveFilter = document.getElementById('live-filter');
        const tableWrapper = document.getElementById('recent-applications-table-wrapper');
        let debounceTimer = null;

        if (liveFilter && tableWrapper) {
            liveFilter.addEventListener('input', function() {
                filterTable();

                clearTimeout(debounceTimer);
                const term = this.value.trim();
                debounceTimer = setTimeout(() => {
                    const fetchUrl = new URL("{{ route('university.dashboard') }}", window.location.origin);
                    if (term) {
                        fetchUrl.searchParams.set('search', term);
                    }
                    fetchUrl.searchParams.set('ajax', '1');

                    fetch(fetchUrl.toString(), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html'
                        }
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Search failed');
                        return response.text();
                    })
                    .then(html => {
                        tableWrapper.innerHTML = html;
                        filterTable();
                    })
                    .catch(() => {});
                }, 400);
            });
        }
    });
</script>
@endpush
