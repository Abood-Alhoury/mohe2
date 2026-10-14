@extends('layouts.admin')

@section('title', 'شؤون وقرارات اللجنة العامة - مجلس التعليم العالي')

@push('styles')
<style>
    :root {
        --imperial-navy: #0e1e38;
        --imperial-navy-dark: #081224;
        --heritage-gold: #c59b27;
        --heritage-gold-light: #f5d77f;
        --heritage-gold-glow: rgba(197, 155, 39, 0.25);
        --emerald: #10b981;
        --emerald-bg: #ecfdf5;
        --rose: #ef4444;
        --rose-bg: #fef2f2;
        --sky: #0284c7;
        --sky-bg: #f0f9ff;
        --amber: #f59e0b;
        --amber-bg: #fffbeb;
        --bg-page: #f1f5f9;
        --surface-card: #ffffff;
        --border-subtle: #e2e8f0;
        --text-dark: #0f172a;
        --text-muted: #64748b;
    }

    /* Hero Session Card */
    .committee-hero-card {
        background: white;
        border-radius: 14px;
        padding: 22px 28px;
        border: 1px solid var(--border-subtle);
        box-shadow: 0 4px 18px rgba(0,0,0,0.02);
        border-right: 5px solid var(--heritage-gold);
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 16px;
    }

    .hero-left-cluster {
        display: flex;
        align-items: center;
        gap: 18px;
    }

    .committee-badge-icon {
        width: 58px;
        height: 58px;
        border-radius: 14px;
        background: #fffbeb;
        border: 1.5px solid #fde68a;
        color: #d97706;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        box-shadow: 0 4px 12px rgba(217, 119, 6, 0.1);
        flex-shrink: 0;
    }

    .hero-texts h2 {
        font-size: 1.25rem;
        font-weight: 900;
        color: var(--imperial-navy);
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 4px;
        flex-wrap: wrap;
    }

    .session-status-tag {
        background: #ecfdf5;
        color: #059669;
        border: 1px solid #a7f3d0;
        padding: 3px 12px;
        border-radius: 20px;
        font-size: 0.76rem;
        font-weight: 800;
    }

    .hero-texts p {
        font-size: 0.86rem;
        color: var(--text-muted);
        margin-bottom: 0;
    }

    .hero-actions-cluster {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .btn-session-act {
        padding: 9px 18px;
        border-radius: 8px;
        font-size: 0.88rem;
        font-weight: 700;
        border: 1.5px solid var(--border-subtle);
        background: white;
        color: var(--text-dark);
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
        text-decoration: none;
    }

    .btn-session-act:hover {
        border-color: var(--imperial-navy);
        background: #f8fafc;
        color: var(--imperial-navy);
    }

    .btn-confirm-all {
        background: linear-gradient(135deg, var(--heritage-gold) 0%, #aa841d 100%);
        color: #081224;
        font-weight: 800;
        border: none;
        box-shadow: 0 4px 12px var(--heritage-gold-glow);
    }

    .btn-confirm-all:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(197, 155, 39, 0.35);
        color: #081224;
    }

    /* KPI BENTO CARDS */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        margin-bottom: 22px;
    }

    .kpi-card {
        background: var(--surface-card);
        border-radius: 12px;
        padding: 18px 20px;
        border: 1.5px solid var(--border-subtle);
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 2px 6px rgba(0,0,0,0.02);
        transition: all 0.2s;
    }

    .kpi-card:hover {
        transform: translateY(-2px);
        border-color: #cbd5e1;
        box-shadow: 0 6px 18px rgba(0,0,0,0.04);
    }

    .kpi-data span {
        font-size: 0.8rem;
        font-weight: 700;
        color: var(--text-muted);
        display: block;
        margin-bottom: 4px;
    }

    .kpi-data strong {
        font-size: 1.8rem;
        font-weight: 900;
        color: var(--text-dark);
        font-family: 'IBM Plex Sans Arabic', sans-serif;
    }

    .kpi-icon-box {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }

    /* SMART RULE NOTICE (EXECUTIVE BANNER) */
    .rule-matrix-banner {
        background: linear-gradient(135deg, #0e1e38 0%, #17335d 100%);
        border-radius: 12px;
        padding: 16px 22px;
        color: white;
        margin-bottom: 22px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border: 1px solid rgba(197, 155, 39, 0.3);
        flex-wrap: wrap;
        gap: 14px;
    }

    .rule-texts {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .rule-texts i {
        font-size: 24px;
        color: var(--heritage-gold-light);
    }

    .rule-texts div strong {
        font-size: 0.92rem;
        display: block;
        margin-bottom: 2px;
    }

    .rule-texts div span {
        font-size: 0.78rem;
        color: #cbd5e1;
    }

    .rule-badges-cluster {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .rule-chip {
        background: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.15);
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.78rem;
        font-weight: 700;
        white-space: nowrap;
    }

    /* FILTER & SEARCH PANEL */
    .filter-panel {
        background: white;
        border-radius: 12px;
        padding: 14px 20px;
        border: 1px solid var(--border-subtle);
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }

    .search-pill {
        flex: 1;
        min-width: 300px;
        position: relative;
    }

    .search-pill input {
        width: 100%;
        padding: 10px 42px 10px 16px;
        border: 1.5px solid var(--border-subtle);
        border-radius: 30px;
        font-size: 0.88rem;
        background: #f8fafc;
        outline: none;
        transition: all 0.2s;
    }

    .search-pill input:focus {
        background: #fff;
        border-color: var(--imperial-navy);
        box-shadow: 0 0 0 3px rgba(14, 30, 56, 0.08);
    }

    .search-pill i {
        position: absolute;
        right: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
    }

    .select-filter-pill {
        padding: 9px 16px;
        border: 1.5px solid var(--border-subtle);
        border-radius: 30px;
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--text-dark);
        background: #f8fafc;
        outline: none;
        cursor: pointer;
    }

    /* DATA GRID CONTAINER */
    .table-card-container {
        background: white;
        border-radius: 14px;
        border: 1px solid var(--border-subtle);
        box-shadow: 0 4px 20px rgba(0,0,0,0.02);
        overflow: hidden;
    }

    .table-responsive {
        overflow-x: auto;
    }

    table.modern-table {
        width: 100%;
        border-collapse: collapse;
        text-align: right;
    }

    table.modern-table th {
        background: #f8fafc;
        color: var(--text-muted);
        font-size: 0.82rem;
        font-weight: 700;
        padding: 14px 18px;
        border-bottom: 1.5px solid var(--border-subtle);
        white-space: nowrap;
    }

    table.modern-table td {
        padding: 16px 18px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.9rem;
        vertical-align: middle;
    }

    table.modern-table tr:hover td {
        background: #fafcff;
    }

    /* Decision Selector Dropdown */
    .decision-select-box {
        padding: 7px 12px;
        border-radius: 8px;
        border: 1.5px solid #cbd5e1;
        font-size: 0.84rem;
        font-weight: 800;
        background: #fff;
        outline: none;
        cursor: pointer;
        width: 100%;
        max-width: 220px;
    }

    .decision-select-box:focus {
        border-color: var(--heritage-gold);
        box-shadow: 0 0 0 3px var(--heritage-gold-glow);
    }

    /* EMPTY STATE WRAPPER (PREMIUM OFFICIAL) */
    .empty-committee-state {
        padding: 65px 24px;
        text-align: center;
        max-width: 600px;
        margin: 0 auto;
    }

    .empty-icon-halo {
        width: 90px;
        height: 90px;
        border-radius: 50%;
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        border: 2px dashed #93c5fd;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 38px;
        color: #1d4ed8;
        margin: 0 auto 20px;
        box-shadow: 0 10px 25px rgba(29, 78, 216, 0.1);
    }

    .empty-committee-state h3 {
        font-size: 1.25rem;
        font-weight: 800;
        color: var(--imperial-navy);
        margin-bottom: 6px;
    }

    .empty-committee-state p {
        font-size: 0.88rem;
        color: var(--text-muted);
        line-height: 1.8;
        margin-bottom: 22px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0" dir="rtl">

    <!-- 1. HERO SESSION CARD -->
    <section class="committee-hero-card">
        <div class="hero-left-cluster">
            <div class="committee-badge-icon">
                <i class="fa-solid fa-users-rectangle"></i>
            </div>
            <div class="hero-texts">
                <h2>
                    شؤون وقرارات اللجنة العامة لمعادلة الشهادات
                    <span class="session-status-tag"><i class="fa-solid fa-circle-dot"></i> الجلسة الحالية لعام {{ date('Y') }}</span>
                </h2>
                <p>استعراض ودراسة المعاملات المحالة للجنة العامة، وتثبيت التوصيات والقرارات الوزارية النهائية أصولاً.</p>
            </div>
        </div>

        <div class="hero-actions-cluster">
            <button type="button" class="btn-session-act" onclick="window.print()">
                <i class="fa-solid fa-print text-secondary"></i> طباعة جدول الأعمال
            </button>
            <a href="{{ route('admin.archive.index') }}" class="btn-session-act btn-confirm-all text-decoration-none">
                <i class="fa-solid fa-box-archive"></i> أرشيف الجلسات والقرارات
            </a>
        </div>
    </section>

    <!-- 2. KPI BENTO ROW -->
    <section class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-data">
                <span>المعاملات المدرجة بالجلسة</span>
                <strong id="kpi-total">{{ $totalCount ?? $committeeApps->count() }}</strong>
            </div>
            <div class="kpi-icon-box" style="background: #eff6ff; color: #1d4ed8;">
                <i class="fa-solid fa-folder-tree"></i>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-data">
                <span>بانتظار البت والتصويت</span>
                <strong style="color: var(--amber);" id="kpi-pending">{{ $pendingCount ?? $committeeApps->count() }}</strong>
            </div>
            <div class="kpi-icon-box" style="background: var(--amber-bg); color: var(--amber);">
                <i class="fa-solid fa-hourglass-half"></i>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-data">
                <span>تمت الموافقة والاعتماد</span>
                <strong style="color: var(--emerald);" id="kpi-approved">{{ $approvedCount ?? 0 }}</strong>
            </div>
            <div class="kpi-icon-box" style="background: var(--emerald-bg); color: var(--emerald);">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-data">
                <span>تعديل مسار / عدم موافقة</span>
                <strong style="color: var(--rose);" id="kpi-rejected">{{ $rejectedCount ?? 0 }}</strong>
            </div>
            <div class="kpi-icon-box" style="background: var(--rose-bg); color: var(--rose);">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
        </div>
    </section>

    <!-- 3. SMART RULE REMINDER BANNER -->
    <section class="rule-matrix-banner">
        <div class="rule-texts">
            <i class="fa-solid fa-gavel"></i>
            <div>
                <strong>ضوابط وقواعد قرارات اللجنة العامة المعتمدة :</strong>
                <span>يتم تطبيق خيارات البت وفق نوع المعاملة ومسار الدرجة العلمية المرفوعة.</span>
            </div>
        </div>
        <div class="rule-badges-cluster">
            <span class="rule-chip" style="background: rgba(2, 132, 199, 0.25); border-color: #38bdf8;">
                📘 الماجستير الخارجي النظري: (مقبول نظري / تكليف تطبيقي / مرفوض)
            </span>
            <span class="rule-chip" style="background: rgba(13, 148, 136, 0.25); border-color: #2dd4bf;">
                🛠️ الماجستير التطبيقي: (مقبول تطبيقي / مرفوض)
            </span>
            <span class="rule-chip" style="background: rgba(168, 85, 247, 0.25); border-color: #c084fc;">
                🔬 الدكتوراه الخارجية: (إحالة للإنتاج العلمي / مرفوض)
            </span>
        </div>
    </section>

    <!-- 4. SEARCH & FILTER PANEL -->
    <section class="filter-panel">
        <div class="search-pill">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="liveSearch" placeholder="ابحث باسم المرشح، الكلية، رقم المعاملة، أو الجامعة..." onkeyup="filterTable()">
        </div>
        <select class="select-filter-pill" id="uniFilter" onchange="filterTable()">
            <option value="">-- كافة الجامعات الطالبة --</option>
            @php
                $uniqueUnis = $committeeApps->pluck('workUniversity.name')->filter()->unique();
            @endphp
            @foreach($uniqueUnis as $uniName)
                <option value="{{ $uniName }}">{{ $uniName }}</option>
            @endforeach
        </select>
    </section>

    <!-- 5. COMMITTEE TABLE CONTAINER -->
    <section class="table-card-container">
        @if($committeeApps->count() > 0)
        <!-- POPULATED TABLE VIEW -->
        <div class="table-responsive" id="populatedView">
            <table class="modern-table" id="committeeTable">
                <thead>
                    <tr>
                        <th>معرّف المعاملة</th>
                        <th>نوع الطلب والمسار</th>
                        <th>الجامعة الطالبة</th>
                        <th>اسم المرشح والمعلومات</th>
                        <th>الكلية والقسم</th>
                        <th>المؤهل والجامعة المانحة</th>
                        <th>قرار وتوصية اللجنة العامة</th>
                        <th style="text-align: center;">الإضبارة</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($committeeApps as $app)
                    @php
                        $lastEd = $app->educations->last();
                        $reqType = $app->request_type ?? '';
                        $isForeignDoctorate = str_contains($reqType, 'دكتورة خارجية') || str_contains($reqType, 'دكتوراه خارجية')
                            || ((str_contains($reqType, 'دكتوراه') || str_contains($reqType, 'دكتورة')) && str_contains($reqType, 'خارجي'));
                        $isForeignMaster = !$isForeignDoctorate && (str_contains($reqType, 'خارجي') || str_contains($reqType, 'غير سوري'));
                        $isTheoretical = $isForeignMaster && str_contains($reqType, 'نظري');
                        $isApplied = $isForeignMaster && str_contains($reqType, 'تطبيقي');
                        $grantingUni = optional($lastEd)->university->name ?: (optional($lastEd)->university_other ?: (optional($lastEd)->university_text ?: 'جامعة مانحة'));
                        $grantingCountry = optional(optional($lastEd)->country)->name ?: 'خارج القطر';
                    @endphp
                    <tr>
                        <td>
                            <strong style="font-family: monospace; color: #475569;">{{ $app->application_no ?? ('#' . $app->id) }}</strong>
                        </td>
                        <td>
                            @if($isForeignDoctorate)
                                <span style="background: #fdf4ff; color: #9333ea; border: 1px solid #f0abfc; padding: 3px 8px; border-radius: 4px; font-weight: 700; font-size: 0.78rem;">
                                    <i class="fa-solid fa-graduation-cap"></i> دكتوراه خارجية
                                </span>
                            @elseif($isTheoretical)
                                <span style="background: #f0f9ff; color: #0284c7; border: 1px solid #bae6fd; padding: 3px 8px; border-radius: 4px; font-weight: 700; font-size: 0.78rem;">
                                    <i class="fa-solid fa-globe"></i> ماجستير خارجي - نظري
                                </span>
                            @elseif($isApplied)
                                <span style="background: #f0fdfa; color: #0d9488; border: 1px solid #99f6e4; padding: 3px 8px; border-radius: 4px; font-weight: 700; font-size: 0.78rem;">
                                    <i class="fa-solid fa-screwdriver-wrench"></i> ماجستير خارجي - تطبيقي
                                </span>
                            @else
                                <span style="background: #f8fafc; color: #475569; border: 1px solid #e2e8f0; padding: 3px 8px; border-radius: 4px; font-weight: 700; font-size: 0.78rem;">
                                    {{ $app->request_type ?? 'تعادل شهادة' }}
                                </span>
                            @endif
                        </td>
                        <td style="font-weight: 700; color: var(--imperial-navy);">
                            {{ $app->workUniversity->name ?? 'غير محددة' }}
                        </td>
                        <td>
                            <strong style="color: var(--imperial-navy); font-size: 0.95rem;">{{ $app->candidate->full_name ?? 'غ/م' }}</strong>
                            <span style="display: block; font-size: 0.75rem; color: var(--text-muted); font-family: monospace;">{{ $app->candidate->national_id ?? '---' }}</span>
                        </td>
                        <td>
                            <div style="font-weight: 600;">{{ $app->work_faculty ?? 'إدارة الجامعة' }}</div>
                            <span style="display: block; font-size: 0.75rem; color: var(--text-muted);">{{ $app->work_department ?? '' }}</span>
                        </td>
                        <td>
                            <strong>{{ optional($lastEd->level)->name ?? 'مؤهل جامعي' }}</strong>
                            <span style="display: block; font-size: 0.75rem; color: #0284c7;">{{ $grantingUni }} ({{ $grantingCountry }})</span>
                        </td>
                        <td>
                            @if($isForeignDoctorate || $isForeignMaster)
                                <button type="button" class="btn btn-sm btn-solid-navy px-3 py-1.5 fw-bold shadow-xs d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#decisionModal-{{ $app->id }}" style="background: var(--imperial-navy); color: white; border-radius: 8px; font-size: 0.82rem;">
                                    <i class="fa-solid fa-gavel" style="color: var(--heritage-gold-light);"></i>
                                    <span>اتخاذ القرار والبت</span>
                                </button>
                            @else
                                <form action="{{ route('admin.committee.decide', $app->id) }}" method="POST" class="d-flex gap-1.5 align-items-center">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" name="decision" value="موافقة" class="btn btn-sm fw-bold px-2.5 py-1 text-white d-inline-flex align-items-center gap-1" style="background: #059669; border-radius: 6px; font-size: 0.78rem;" onclick="return confirm('إقرار موافقة اللجنة العامة على المعاملة؟');">
                                        <i class="fa-solid fa-circle-check"></i> موافقة
                                    </button>
                                    <button type="button" class="btn btn-sm fw-bold px-2.5 py-1 text-white d-inline-flex align-items-center gap-1" style="background: #dc2626; border-radius: 6px; font-size: 0.78rem;" data-bs-toggle="modal" data-bs-target="#decisionModal-{{ $app->id }}">
                                        <i class="fa-solid fa-circle-xmark"></i> رفض
                                    </button>
                                </form>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <a href="{{ route('admin.reports.show', $app->id) }}" class="btn-session-act" style="padding: 6px 12px; border-radius: 6px;" title="معاينة إضبارة ومذكرة المعاملة">
                                <i class="fa-solid fa-eye text-primary"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <!-- EMPTY STATE (حالة عدم وجود معاملات) -->
        <div class="empty-committee-state" id="emptyView">
            <div class="empty-icon-halo">
                <i class="fa-solid fa-clipboard-check"></i>
            </div>
            <h3>لا توجد طلبات أو معاملات محالة إلى اللجنة العامة حالياً</h3>
            <p>
                تم استكمال كافة مداولات البت في الملفات المعروضة على جدول أعمال الجلسة الحالية أصولاً،<br>
                أو لم تقم أمانة المجلس بتحويل طلبات جديدة لهذه الجلسة حتى الآن.
            </p>
            <div style="display: flex; gap: 10px; justify-content: center;">
                <a href="{{ route('admin.archive.index') }}" class="btn-session-act">
                    <i class="fa-solid fa-clock-rotate-left"></i> مراجعة أرشيف الجلسات والقرارات السابقة
                </a>
            </div>
        </div>
        @endif
    </section>

</div>

<!-- DECISION MODALS -->
@foreach($committeeApps as $app)
@php
    $lastEducation = $app->educations->last();
    $reqType = $app->request_type ?? '';
    $isForeignDoctorate = str_contains($reqType, 'دكتورة خارجية') || str_contains($reqType, 'دكتوراه خارجية')
        || ((str_contains($reqType, 'دكتوراه') || str_contains($reqType, 'دكتورة')) && str_contains($reqType, 'خارجي'));
    $isForeignMaster = !$isForeignDoctorate && (str_contains($reqType, 'خارجي') || str_contains($reqType, 'غير سوري'));
    $isTheoretical = $isForeignMaster && str_contains($reqType, 'نظري');
@endphp
<div class="modal fade" id="decisionModal-{{ $app->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-top: 4px solid var(--heritage-gold) !important; border-radius: 12px;">
            <form action="{{ route('admin.committee.decide', $app->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header bg-light py-3 px-4">
                    <h6 class="modal-title fw-bold" style="color: var(--imperial-navy);">
                        <i class="fa-solid fa-gavel me-1.5" style="color: var(--heritage-gold);"></i>
                        قرار وتوصية اللجنة العامة - طلب: #{{ $app->application_no }}
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4 text-start" dir="rtl">
                    <div class="p-3 bg-light rounded-3 mb-3 border fs-7">
                        <div class="mb-1"><strong>المرشح:</strong> {{ $app->candidate->full_name }}</div>
                        <div><strong>نوع الطلب الحالي:</strong> <span class="badge" style="background: var(--imperial-navy); color: white;">{{ $app->request_type }}</span></div>
                    </div>

                    <!-- Dropdown for decisions -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">قرار وتوصية اللجنة العامة : <span class="text-danger">*</span></label>
                        <select name="decision_action" id="decision_select_{{ $app->id }}" class="form-select fw-bold" onchange="handleDecisionChange('{{ $app->id }}', this.value)" required>
                            <option value="" disabled selected>-- اختر القرار من القائمة --</option>
                            
                            @if($isForeignDoctorate)
                                <option value="approved_to_scientific_production" class="text-success fw-bold">
                                    ✔ 1. موافقة وإحالة إلى لجنة الإنتاج العلمي (← إنتاج علمي)
                                </option>
                                <option value="rejected" class="text-danger fw-bold">
                                    ✖ 2. عدم موافقة / رفض التعادل
                                </option>
                            @elseif($isTheoretical)
                                <option value="approved_theoretical" class="text-success fw-bold">
                                    ✔ 1. قبول (مسار نظري - اعتماد خبرة سنتين فأكثر ← بانتظار المقابلة)
                                </option>
                                <option value="approved_applied" class="text-primary fw-bold">
                                    🔄 2. تكليف وتخفيض لتطبيقي (عضو هيئة فنية ← بانتظار إصدار القرار مباشرة)
                                </option>
                                <option value="rejected" class="text-danger fw-bold">
                                    ✖ 3. عدم موافقة (رفض تعادل الشهادة)
                                </option>
                            @elseif($isForeignMaster)
                                <option value="approved_applied" class="text-success fw-bold">
                                    ✔ 1. قبول (مسار تطبيقي - عضو هيئة فنية ← بانتظار إصدار القرار مباشرة)
                                </option>
                                <option value="rejected" class="text-danger fw-bold">
                                    ✖ 2. عدم موافقة (رفض تعادل الشهادة)
                                </option>
                            @else
                                <option value="موافقة" class="text-success fw-bold">
                                    ✔ 1. موافقة اللجنة العامة واعتماد السير بالمعاملة
                                </option>
                                <option value="rejected" class="text-danger fw-bold">
                                    ✖ 2. عدم موافقة / رفض المعاملة
                                </option>
                            @endif
                        </select>
                    </div>

                    <!-- Rejection Reason Textarea -->
                    <div id="rejection_box_{{ $app->id }}" style="display: none;" class="mb-3">
                        <label class="form-label fw-bold text-danger">سبب الرفض وملاحظات اللجنة : <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" id="rejection_input_{{ $app->id }}" class="form-control" rows="3" placeholder="أدخل أسباب عدم الموافقة على التعادل بالتفصيل..."></textarea>
                    </div>

                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark">رقم الجلسة / المحضر :</label>
                            <input type="text" name="committee_meeting_no" class="form-control" placeholder="مثال: 4">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark">تاريخ الجلسة :</label>
                            <input type="date" name="committee_date" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light py-3 px-4">
                    <button type="button" class="btn btn-outline-secondary px-3" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn px-4 fw-bold" style="background: var(--imperial-navy); color: white;">تأكيد واعتماد القرار</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<script>
    function handleDecisionChange(appId, val) {
        const rejBox = document.getElementById('rejection_box_' + appId);
        const rejInput = document.getElementById('rejection_input_' + appId);

        if (val === 'rejected' || val === 'رفض') {
            if (rejBox) rejBox.style.display = 'block';
            if (rejInput) rejInput.setAttribute('required', 'required');
        } else {
            if (rejBox) rejBox.style.display = 'none';
            if (rejInput) rejInput.removeAttribute('required');
        }
    }

    function filterTable() {
        const query = (document.getElementById('liveSearch')?.value || '').toLowerCase().trim();
        const uniFilter = (document.getElementById('uniFilter')?.value || '').toLowerCase().trim();
        const rows = document.querySelectorAll('#committeeTable tbody tr');

        rows.forEach(r => {
            const text = r.innerText.toLowerCase();
            const matchesQuery = !query || text.includes(query);
            const matchesUni = !uniFilter || text.includes(uniFilter);
            r.style.display = (matchesQuery && matchesUni) ? '' : 'none';
        });
    }
</script>
@endsection