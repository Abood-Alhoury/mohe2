@extends('layouts.university')

@section('title', 'أرشيف القرارات والمعاملات الصادرة - بوابة الجامعات')

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

    .archive-page-wrapper {
        font-family: 'Tajawal', sans-serif;
    }

    /* 1. HERO BENTO BANNER */
    .hero-archive-card {
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

    .hero-archive-card::before {
        content: '';
        position: absolute;
        top: -50px;
        left: -50px;
        width: 220px;
        height: 220px;
        background: radial-gradient(circle, rgba(197,155,39,0.18) 0%, transparent 70%);
        border-radius: 50%;
    }

    .hero-archive-text h2 {
        font-size: 1.45rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 6px;
        color: #ffffff;
    }

    .hero-archive-text p {
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
        font-size: 0.92rem;
        padding: 11px 22px;
        border-radius: 10px;
        border: none;
        cursor: pointer;
        box-shadow: 0 4px 15px var(--accent-gold-glow);
        transition: all 0.25s ease;
        text-decoration: none !important;
        white-space: nowrap;
    }

    .hero-cta-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(197, 155, 39, 0.4);
        color: #0b1526 !important;
    }

    /* 2. KPI CARDS */
    .archive-kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        gap: 14px;
        margin-bottom: 22px;
    }

    .kpi-tile {
        background: #ffffff;
        border: 1px solid var(--border-subtle);
        border-radius: 14px;
        padding: 16px 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: all 0.2s ease;
        text-decoration: none !important;
        color: inherit;
    }

    .kpi-tile:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.04);
        border-color: var(--accent-gold);
    }

    .kpi-tile.active {
        border-color: var(--accent-gold);
        background: #fffdf7;
        box-shadow: 0 4px 15px rgba(197, 155, 39, 0.12);
    }

    .kpi-tile-info span {
        display: block;
        font-size: 0.8rem;
        color: var(--text-muted);
        font-weight: 600;
        margin-bottom: 4px;
    }

    .kpi-tile-info strong {
        font-size: 1.45rem;
        font-weight: 800;
        color: var(--secondary-navy);
        font-family: 'Plus Jakarta Sans', 'Tajawal', sans-serif;
    }

    .kpi-tile-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
    }

    .kpi-gold { background: rgba(197, 155, 39, 0.12); color: #c59b27; }
    .kpi-emerald { background: rgba(16, 185, 129, 0.12); color: #10b981; }
    .kpi-sky { background: rgba(2, 132, 199, 0.12); color: #0284c7; }
    .kpi-indigo { background: rgba(99, 102, 241, 0.12); color: #6366f1; }
    .kpi-amber { background: rgba(245, 158, 11, 0.12); color: #d97706; }
    .kpi-rose { background: rgba(244, 63, 94, 0.12); color: #e11d48; }

    /* 3. FILTER SLAB */
    .archive-filter-slab {
        background: #ffffff;
        border: 1px solid var(--border-subtle);
        border-radius: 14px;
        padding: 16px 20px;
        margin-bottom: 22px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.02);
    }

    .filter-controls-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 12px;
    }

    .archive-search-box {
        position: relative;
        flex: 1;
        min-width: 260px;
    }

    .archive-search-box input {
        width: 100%;
        padding: 10px 42px 10px 14px;
        border: 1.5px solid var(--border-subtle);
        border-radius: 10px;
        font-size: 0.9rem;
        background: #f8fafc;
        outline: none;
        transition: all 0.2s;
        font-family: 'Tajawal', sans-serif;
    }

    .archive-search-box input:focus {
        border-color: var(--secondary-navy);
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(14, 30, 56, 0.08);
    }

    .archive-search-box i {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
    }

    .archive-select {
        padding: 10px 14px;
        border: 1.5px solid var(--border-subtle);
        border-radius: 10px;
        font-size: 0.88rem;
        font-weight: 600;
        color: var(--text-main);
        background: #f8fafc;
        outline: none;
        cursor: pointer;
        font-family: 'Tajawal', sans-serif;
    }

    .archive-track-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 14px;
        padding-top: 14px;
        border-top: 1px solid #f1f5f9;
    }

    .track-tab-btn {
        padding: 6px 14px;
        border-radius: 8px;
        font-size: 0.82rem;
        font-weight: 700;
        text-decoration: none !important;
        color: var(--text-muted);
        background: #f1f5f9;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
    }

    .track-tab-btn:hover {
        background: #e2e8f0;
        color: var(--secondary-navy);
    }

    .track-tab-btn.active {
        background: var(--secondary-navy);
        color: #ffffff;
    }

    /* 4. DATA TABLE CARD */
    .archive-table-card {
        background: #ffffff;
        border: 1px solid var(--border-subtle);
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        overflow: hidden;
    }

    .archive-table-head {
        padding: 16px 22px;
        border-bottom: 1px solid var(--border-subtle);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #fafbfd;
    }

    .archive-table-head h3 {
        font-size: 1.05rem;
        font-weight: 800;
        color: var(--secondary-navy);
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
    }

    .archive-table-responsive {
        width: 100%;
        overflow-x: auto;
    }

    .archive-table {
        width: 100%;
        border-collapse: collapse;
        text-align: right;
    }

    .archive-table th {
        background: #f8fafc;
        color: var(--text-muted);
        font-size: 0.82rem;
        font-weight: 700;
        padding: 13px 18px;
        border-bottom: 1.5px solid var(--border-subtle);
        white-space: nowrap;
    }

    .archive-table td {
        padding: 14px 18px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.88rem;
        vertical-align: middle;
    }

    .archive-table tr:hover td {
        background: #fafcff;
    }

    .app-no-badge {
        font-family: monospace;
        font-weight: 800;
        color: #334155;
        background: #f1f5f9;
        padding: 4px 8px;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
        display: inline-block;
    }

    .cand-name {
        font-weight: 800;
        color: var(--secondary-navy);
        display: block;
        font-size: 0.92rem;
    }

    .cand-natid {
        font-family: monospace;
        font-size: 0.76rem;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 4px;
        margin-top: 2px;
    }

    .pill-degree {
        font-size: 0.8rem;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .pill-phd { background: #fdf2f8; color: #db2777; border: 1px solid #fbcfe8; }
    .pill-theo { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
    .pill-app { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
    .pill-fac { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; }

    .status-badge-issued {
        background: #ecfdf5;
        color: #059669;
        border: 1px solid #a7f3d0;
        padding: 4px 10px;
        border-radius: 20px;
        font-weight: 800;
        font-size: 0.78rem;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .status-badge-rejected {
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
        padding: 4px 10px;
        border-radius: 20px;
        font-weight: 800;
        font-size: 0.78rem;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .decision-info-box {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .decision-no {
        font-weight: 800;
        color: var(--secondary-navy);
        font-size: 0.85rem;
    }

    .decision-date {
        font-size: 0.76rem;
        color: #64748b;
    }

    /* ACTION BUTTONS */
    .action-btns-cluster {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .btn-act-gold {
        background: linear-gradient(135deg, #c59b27 0%, #a88118 100%);
        color: #ffffff !important;
        border: none;
        padding: 7px 12px;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        text-decoration: none !important;
        box-shadow: 0 2px 8px rgba(197, 155, 39, 0.25);
        transition: all 0.2s;
        white-space: nowrap;
    }

    .btn-act-gold:hover {
        background: #a88118;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(197, 155, 39, 0.4);
    }

    .btn-act-bundle {
        background: #0e1e38;
        color: #ffffff !important;
        border: none;
        padding: 7px 12px;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        text-decoration: none !important;
        box-shadow: 0 2px 8px rgba(14, 30, 56, 0.2);
        transition: all 0.2s;
        white-space: nowrap;
    }

    .btn-act-bundle:hover {
        background: #1b3560;
        transform: translateY(-1px);
    }

    .btn-act-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: 1px solid var(--border-subtle);
        background: #ffffff;
        color: #475569;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        transition: all 0.2s;
        text-decoration: none !important;
    }

    .btn-act-icon:hover {
        background: #f1f5f9;
        color: var(--secondary-navy);
        border-color: #cbd5e1;
    }
</style>
@endpush

@section('content')
<div class="archive-page-wrapper" dir="rtl">

    <!-- 1. HERO BENTO BANNER -->
    <section class="hero-archive-card">
        <div class="hero-archive-text">
            <h2>
                <i class="fa-solid fa-stamp" style="color: var(--accent-gold);"></i>
                أرشيف القرارات والمعاملات الصادرة
            </h2>
            <p>
                مستودع القرارات الأكاديمية الصادرة والمعتمدة رسمياً لجامعة ({{ $universityName }}). تتيح هذه البوابة تحميل القرارات الرسمية وتنزيل الإضبارة المدمجة كاملة (Bundle PDF).
            </p>
        </div>
        <div>
            <a href="{{ route('university.apply.options') }}" class="hero-cta-btn">
                <i class="fa-solid fa-plus"></i>
                تقديم معاملة تعادل جديدة
            </a>
        </div>
    </section>

    <!-- 2. KPI CARDS -->
    <section class="archive-kpi-grid">
        <a href="{{ route('university.archive', ['track' => 'all', 'search' => $search, 'year' => $yearFilter]) }}" class="kpi-tile {{ (!request('track') || request('track') == 'all') ? 'active' : '' }}">
            <div class="kpi-tile-info">
                <span>إجمالي القرارات</span>
                <strong>{{ $kpi['total'] }}</strong>
            </div>
            <div class="kpi-tile-icon kpi-gold"><i class="fa-solid fa-stamp"></i></div>
        </a>

        <a href="{{ route('university.archive', ['track' => 'phd', 'search' => $search, 'year' => $yearFilter]) }}" class="kpi-tile {{ request('track') == 'phd' ? 'active' : '' }}">
            <div class="kpi-tile-info">
                <span>الدكتوراه</span>
                <strong>{{ $kpi['phd'] }}</strong>
            </div>
            <div class="kpi-tile-icon kpi-rose"><i class="fa-solid fa-graduation-cap"></i></div>
        </a>

        <a href="{{ route('university.archive', ['track' => 'master-theo', 'search' => $search, 'year' => $yearFilter]) }}" class="kpi-tile {{ request('track') == 'master-theo' ? 'active' : '' }}">
            <div class="kpi-tile-info">
                <span>الماجستير النظري</span>
                <strong>{{ $kpi['master_theo'] }}</strong>
            </div>
            <div class="kpi-tile-icon kpi-sky"><i class="fa-solid fa-book-bookmark"></i></div>
        </a>

        <a href="{{ route('university.archive', ['track' => 'master-app', 'search' => $search, 'year' => $yearFilter]) }}" class="kpi-tile {{ request('track') == 'master-app' ? 'active' : '' }}">
            <div class="kpi-tile-info">
                <span>الماجستير التطبيقي</span>
                <strong>{{ $kpi['master_app'] }}</strong>
            </div>
            <div class="kpi-tile-icon kpi-emerald"><i class="fa-solid fa-screwdriver-wrench"></i></div>
        </a>

        <a href="{{ route('university.archive', ['track' => 'faculty', 'search' => $search, 'year' => $yearFilter]) }}" class="kpi-tile {{ request('track') == 'faculty' ? 'active' : '' }}">
            <div class="kpi-tile-info">
                <span>سماح التدريس</span>
                <strong>{{ $kpi['faculty'] }}</strong>
            </div>
            <div class="kpi-tile-icon kpi-amber"><i class="fa-solid fa-chalkboard-user"></i></div>
        </a>

        <a href="{{ route('university.archive', ['track' => 'rejected', 'search' => $search, 'year' => $yearFilter]) }}" class="kpi-tile {{ request('track') == 'rejected' ? 'active' : '' }}">
            <div class="kpi-tile-info">
                <span>عدم الموافقة</span>
                <strong>{{ $kpi['rejected'] }}</strong>
            </div>
            <div class="kpi-tile-icon kpi-indigo"><i class="fa-solid fa-circle-xmark"></i></div>
        </a>
    </section>

    <!-- 3. SEARCH & FILTER SLAB -->
    <section class="archive-filter-slab">
        <form method="GET" action="{{ route('university.archive') }}">
            <input type="hidden" name="track" value="{{ request('track', 'all') }}">
            
            <div class="filter-controls-row">
                <div class="archive-search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="ابحث برقم المعاملة، اسم المرشح، الرقم الوطني، أو رقم القرار...">
                </div>

                <select name="year" class="archive-select" onchange="this.form.submit()">
                    <option value="">جميع السنوات</option>
                    @for($y = date('Y'); $y >= 2020; $y--)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>عام {{ $y }}</option>
                    @endfor
                </select>

                <button type="submit" class="btn btn-dark px-4 fw-bold" style="border-radius: 10px; font-size: 0.88rem;">
                    <i class="fa-solid fa-filter me-1"></i> تصفية
                </button>

                @if($search || request('year') || (request('track') && request('track') != 'all'))
                    <a href="{{ route('university.archive') }}" class="btn btn-outline-secondary px-3" style="border-radius: 10px; font-size: 0.88rem;" title="إعادة تعيين الفلاتر">
                        <i class="fa-solid fa-rotate-right"></i>
                    </a>
                @endif
            </div>

            <!-- Track Tabs -->
            <div class="archive-track-tabs">
                <a href="{{ route('university.archive', array_merge(request()->query(), ['track' => 'all'])) }}" class="track-tab-btn {{ (!request('track') || request('track') == 'all') ? 'active' : '' }}">
                    <i class="fa-solid fa-list-check"></i> الكل
                </a>
                <a href="{{ route('university.archive', array_merge(request()->query(), ['track' => 'phd'])) }}" class="track-tab-btn {{ request('track') == 'phd' ? 'active' : '' }}">
                    <i class="fa-solid fa-graduation-cap"></i> الدكتوراه
                </a>
                <a href="{{ route('university.archive', array_merge(request()->query(), ['track' => 'master-theo'])) }}" class="track-tab-btn {{ request('track') == 'master-theo' ? 'active' : '' }}">
                    <i class="fa-solid fa-book-bookmark"></i> الماجستير النظري
                </a>
                <a href="{{ route('university.archive', array_merge(request()->query(), ['track' => 'master-app'])) }}" class="track-tab-btn {{ request('track') == 'master-app' ? 'active' : '' }}">
                    <i class="fa-solid fa-screwdriver-wrench"></i> الماجستير التطبيقي
                </a>
                <a href="{{ route('university.archive', array_merge(request()->query(), ['track' => 'faculty'])) }}" class="track-tab-btn {{ request('track') == 'faculty' ? 'active' : '' }}">
                    <i class="fa-solid fa-chalkboard-user"></i> سماح التدريس
                </a>
                <a href="{{ route('university.archive', array_merge(request()->query(), ['track' => 'rejected'])) }}" class="track-tab-btn {{ request('track') == 'rejected' ? 'active' : '' }}">
                    <i class="fa-solid fa-triangle-exclamation"></i> عدم الموافقة
                </a>
            </div>
        </form>
    </section>

    <!-- 4. DATA TABLE -->
    <section class="archive-table-card">
        <div class="archive-table-head">
            <h3>
                <i class="fa-solid fa-folder-closed" style="color: var(--accent-gold);"></i>
                سجل القرارات الأكاديمية المؤرشفة
            </h3>
            <span class="text-muted fs-8 fw-bold">عرض {{ $archivedApplications->total() }} معاملة</span>
        </div>

        <div class="archive-table-responsive">
            <table class="archive-table">
                <thead>
                    <tr>
                        <th>رقم المعاملة</th>
                        <th>المرشح والرقم الوطني</th>
                        <th>المسار الأكاديمي</th>
                        <th>الكلية والقسم</th>
                        <th>بيانات القرار الرسمي</th>
                        <th>الحالة</th>
                        <th style="text-align: center;">القرارات والإضبارة</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($archivedApplications as $app)
                        @php
                            $reqType = (string)$app->request_type;
                            $reqTypeId = (int)$app->request_type_id;
                            $isDoc = $reqTypeId === 3 || $reqTypeId === 7 || str_contains($reqType, 'دكتور') || str_contains($reqType, 'دكتورة');
                            $isMasterTheo = $reqTypeId === 2 || $reqTypeId === 6 || (str_contains($reqType, 'ماجستير') && str_contains($reqType, 'نظري'));
                            $isMasterApp = $reqTypeId === 1 || $reqTypeId === 5 || (str_contains($reqType, 'ماجستير') && str_contains($reqType, 'تطبيقي'));
                            $isFac = $reqTypeId === 4 || str_contains($reqType, 'سماح') || str_contains($reqType, 'تدريس');
                            $isRejected = in_array($app->status, ['مرفوض', 9, '9', 10, '10']);
                            $decision = $app->latestDecision ?? $app->decisions->first();
                        @endphp
                        <tr>
                            <td>
                                <span class="app-no-badge">{{ $app->application_no ?? ('#' . $app->id) }}</span>
                            </td>
                            <td>
                                <span class="cand-name">{{ optional($app->candidate)->full_name ?? 'مرشح غير محدد' }}</span>
                                <span class="cand-natid">
                                    <i class="fa-regular fa-id-card text-muted"></i>
                                    {{ optional($app->candidate)->national_id ?? optional($app->candidate)->id_number ?? '—' }}
                                </span>
                            </td>
                            <td>
                                @if($isDoc)
                                    <span class="pill-degree pill-phd"><i class="fa-solid fa-graduation-cap"></i> {{ $app->request_type }}</span>
                                @elseif($isMasterTheo)
                                    <span class="pill-degree pill-theo"><i class="fa-solid fa-book-open"></i> {{ $app->request_type }}</span>
                                @elseif($isMasterApp)
                                    <span class="pill-degree pill-app"><i class="fa-solid fa-screwdriver-wrench"></i> {{ $app->request_type }}</span>
                                @elseif($isFac)
                                    <span class="pill-degree pill-fac"><i class="fa-solid fa-chalkboard-user"></i> {{ $app->request_type }}</span>
                                @else
                                    <span class="pill-degree pill-theo"><i class="fa-solid fa-file-lines"></i> {{ $app->request_type ?? 'تعادل' }}</span>
                                @endif
                            </td>
                            <td>
                                <div style="font-weight: 700; color: #1e293b;">{{ $app->work_faculty ?? 'غير محدد' }}</div>
                                <div style="font-size: 0.78rem; color: #64748b;">{{ $app->work_department ?? '' }}</div>
                            </td>
                            <td>
                                @if($decision)
                                    <div class="decision-info-box">
                                        <span class="decision-no">
                                            <i class="fa-solid fa-stamp text-warning me-1"></i>
                                            {{ $decision->decision_no ?? ('قرار #' . $app->application_no) }}
                                        </span>
                                        <span class="decision-date">
                                            {{ $decision->decision_date ? \Carbon\Carbon::parse($decision->decision_date)->format('d/m/Y') : ($app->updated_at ? $app->updated_at->format('d/m/Y') : '—') }}
                                        </span>
                                    </div>
                                @else
                                    <span class="text-muted fs-8">صادر أصولاً</span>
                                @endif
                            </td>
                            <td>
                                @if($isRejected)
                                    <span class="status-badge-rejected">
                                        <i class="fa-solid fa-circle-xmark"></i> مرفوض
                                    </span>
                                @else
                                    <span class="status-badge-issued">
                                        <i class="fa-solid fa-circle-check"></i> معتمدة (تم الصدور)
                                    </span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <div class="action-btns-cluster">
                                    {{-- 1. Download / View Official Decision PDF --}}
                                    <a href="{{ route('university.applications.view_decision_pdf', $app->id) }}" target="_blank" class="btn-act-gold" title="تحميل واستعراض القرار الرسمي الصادر">
                                        <i class="fa-solid fa-stamp"></i> القرار الرسمي
                                    </a>

                                    {{-- 2. Download Full Merged Package (Bundle PDF) --}}
                                    <a href="{{ route('university.applications.download_consolidated_pdf', $app->id) }}" target="_blank" class="btn-act-bundle" title="تحميل الإضبارة والمرفقات كاملة كملف مدمج (Bundle PDF)">
                                        <i class="fa-solid fa-file-zipper"></i> الإضبارة المدمجة
                                    </a>

                                    {{-- 3. View Mozhakkara --}}
                                    <a href="{{ route('university.applications.mozhakkara', $app->id) }}" target="_blank" class="btn-act-icon" title="معاينة مذكرة العرض">
                                        <i class="fa-solid fa-file-lines text-secondary"></i>
                                    </a>

                                    {{-- 4. Audit Trail Messages (Read-Only) --}}
                                    <a href="{{ route('university.messages', ['application_id' => $app->id]) }}" class="btn-act-icon" title="سجل المراسلات التاريخي (للاطلاع فقط)">
                                        <i class="fa-solid fa-comment-dots text-muted"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-folder-open fs-1 opacity-50 mb-3 d-block" style="color: var(--accent-gold);"></i>
                                <h6 class="fw-bold text-dark mb-1">لا توجد معاملات مؤرشفة مطابقة</h6>
                                <p class="fs-8 text-muted mb-0">لم يتم العثور على قرارات أو معاملات صادرة مطابقة للبحث أو التصفية الحالية.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($archivedApplications->hasPages())
            <div class="p-3 border-top d-flex justify-content-center">
                {{ $archivedApplications->links() }}
            </div>
        @endif
    </section>

</div>
@endsection
