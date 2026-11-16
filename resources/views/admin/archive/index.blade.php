@extends('layouts.admin')

@section('title', 'الأرشيف المركزي وسجل القرارات الأكاديمية - مجلس التعليم العالي')

@push('styles')
<style>
    :root {
        --imperial-navy: #0e1e38;
        --imperial-navy-dark: #081224;
        --imperial-navy-light: #162f56;
        --heritage-gold: #c59b27;
        --heritage-gold-light: #e6be53;
        --heritage-gold-dark: #9a7617;
        --bg-body: #f4f6fa;
        --surface-card: #ffffff;
        --border-color: #e2e8f0;
        --text-dark: #1e293b;
        --text-muted: #64748b;
        --badge-phd: #7c3aed;
        --badge-master-theo: #0284c7;
        --badge-master-app: #0d9488;
        --badge-faculty: #d97706;
        --badge-rejected: #dc2626;
    }

    /* Government Executive Banner */
    .gov-top-bar {
        background: linear-gradient(135deg, var(--imperial-navy-dark) 0%, var(--imperial-navy) 100%);
        color: white;
        padding: 14px 24px;
        border-bottom: 3.5px solid var(--heritage-gold);
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.12);
        border-radius: 10px;
        margin-bottom: 22px;
    }

    .gov-branding {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .gov-logo-box {
        width: 46px;
        height: 46px;
        background: rgba(255,255,255,0.08);
        border: 1px solid var(--heritage-gold-light);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        color: var(--heritage-gold-light);
    }

    .gov-titles h1 {
        font-size: 1.15rem;
        font-weight: 800;
        letter-spacing: -0.3px;
        margin: 0;
        color: #ffffff;
    }

    .gov-titles span {
        font-size: 0.8rem;
        color: #cbd5e1;
        display: block;
        margin-top: 2px;
    }

    .gov-user-pill {
        display: flex;
        align-items: center;
        gap: 12px;
        background: rgba(255,255,255,0.06);
        padding: 6px 14px;
        border-radius: 30px;
        border: 1px solid rgba(255,255,255,0.12);
    }

    .gov-user-avatar {
        width: 32px;
        height: 32px;
        background: var(--heritage-gold);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: var(--imperial-navy-dark);
        font-size: 14px;
    }

    /* KPI Metric Cards */
    .kpi-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 14px;
        margin-bottom: 22px;
    }

    .kpi-card {
        background: var(--surface-card);
        border-radius: 10px;
        padding: 16px 18px;
        border: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 2px 6px rgba(0,0,0,0.03);
        cursor: pointer;
        transition: all 0.22s ease;
        position: relative;
        overflow: hidden;
    }

    .kpi-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 4px;
        height: 100%;
        background: var(--card-color, var(--imperial-navy));
    }

    .kpi-card:hover, .kpi-card.active {
        transform: translateY(-2.5px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        border-color: var(--card-color, var(--imperial-navy));
    }

    .kpi-card.active {
        background: #fafcff;
    }

    .kpi-data span {
        font-size: 0.78rem;
        color: var(--text-muted);
        font-weight: 600;
        display: block;
        margin-bottom: 4px;
    }

    .kpi-data strong {
        font-size: 1.55rem;
        font-weight: 800;
        color: var(--text-dark);
        line-height: 1;
    }

    .kpi-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 19px;
        background: rgba(0,0,0,0.03);
        color: var(--card-color, var(--imperial-navy));
    }

    /* Filter & Search Panel */
    .filter-panel {
        background: var(--surface-card);
        border-radius: 12px;
        padding: 18px 20px;
        border: 1px solid var(--border-color);
        box-shadow: 0 3px 10px rgba(0,0,0,0.03);
        margin-bottom: 20px;
    }

    .search-row {
        display: flex;
        gap: 12px;
        margin-bottom: 14px;
        flex-wrap: wrap;
    }

    .search-box {
        flex: 1;
        min-width: 280px;
        position: relative;
    }

    .search-box i {
        position: absolute;
        right: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 15px;
    }

    .search-input {
        width: 100%;
        padding: 11px 44px 11px 16px;
        border: 1.5px solid var(--border-color);
        border-radius: 8px;
        font-size: 0.92rem;
        outline: none;
        transition: all 0.2s;
        background: #f8fafc;
        direction: rtl;
    }

    .search-input:focus {
        border-color: var(--imperial-navy);
        background: #fff;
        box-shadow: 0 0 0 3px rgba(14, 30, 56, 0.08);
    }

    .select-filter {
        padding: 10px 14px;
        border: 1.5px solid var(--border-color);
        border-radius: 8px;
        font-size: 0.88rem;
        font-weight: 600;
        color: var(--text-dark);
        background: #f8fafc;
        outline: none;
        cursor: pointer;
    }

    .select-filter:focus {
        border-color: var(--imperial-navy);
    }

    .btn-export {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #10b981;
        color: white;
        border: none;
        padding: 10px 18px;
        border-radius: 8px;
        font-weight: 700;
        cursor: pointer;
        transition: 0.2s;
        font-size: 0.88rem;
    }

    .btn-export:hover {
        background: #059669;
        color: white;
    }

    /* 5 Group Tabs Navigation */
    .group-nav {
        display: flex;
        align-items: center;
        gap: 8px;
        border-top: 1px solid #f1f5f9;
        padding-top: 12px;
        overflow-x: auto;
    }

    .tab-btn {
        background: transparent;
        border: 1.5px solid transparent;
        padding: 7px 14px;
        border-radius: 30px;
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--text-muted);
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 7px;
        transition: all 0.2s;
        white-space: nowrap;
    }

    .tab-btn:hover {
        background: #f1f5f9;
        color: var(--imperial-navy);
    }

    .tab-btn.active {
        background: var(--imperial-navy);
        color: white;
    }

    .tab-btn.tab-rejected.active {
        background: var(--badge-rejected);
        color: white;
    }

    .tab-badge {
        background: rgba(0,0,0,0.08);
        padding: 2px 7px;
        border-radius: 20px;
        font-size: 0.72rem;
    }

    .tab-btn.active .tab-badge {
        background: rgba(255,255,255,0.25);
        color: white;
    }

    /* Data Grid Table */
    .archive-card {
        background: var(--surface-card);
        border-radius: 12px;
        border: 1px solid var(--border-color);
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        overflow: hidden;
    }

    .archive-header {
        padding: 16px 20px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #fcfdfe;
    }

    .archive-header h2 {
        font-size: 1.05rem;
        font-weight: 800;
        color: var(--imperial-navy);
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0;
    }

    .result-count {
        font-size: 0.85rem;
        color: var(--text-muted);
        font-weight: 600;
    }

    table.archive-table {
        width: 100%;
        border-collapse: collapse;
        text-align: right;
    }

    table.archive-table th {
        background: #f8fafc;
        color: var(--text-muted);
        font-size: 0.82rem;
        font-weight: 700;
        padding: 13px 16px;
        border-bottom: 1.5px solid var(--border-color);
        white-space: nowrap;
    }

    table.archive-table td {
        padding: 13px 16px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.88rem;
        vertical-align: middle;
    }

    table.archive-table tr:hover td {
        background: #fbfdff;
    }

    /* Candidate cell */
    .cand-name {
        font-weight: 800;
        color: var(--imperial-navy);
        font-size: 0.92rem;
    }

    .cand-natid {
        font-size: 0.78rem;
        color: #64748b;
        font-family: monospace;
        letter-spacing: 0.5px;
        display: block;
        margin-top: 2px;
    }

    /* Pill Badges */
    .badge-track {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 9px;
        border-radius: 6px;
        font-size: 0.76rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .badge-phd { background: #f5f3ff; color: var(--badge-phd); border: 1px solid #ddd6fe; }
    .badge-master-theo { background: #f0f9ff; color: var(--badge-master-theo); border: 1px solid #bae6fd; }
    .badge-master-app { background: #f0fdfa; color: var(--badge-master-app); border: 1px solid #99f6e4; }
    .badge-faculty { background: #fffbeb; color: var(--badge-faculty); border: 1px solid #fde68a; }
    .badge-rejected { background: #fef2f2; color: var(--badge-rejected); border: 1px solid #fecaca; }

    /* Action Buttons */
    .action-group {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .btn-act {
        border: 1px solid var(--border-color);
        background: white;
        padding: 5px 9px;
        border-radius: 6px;
        font-size: 0.78rem;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all 0.15s ease;
        color: var(--text-dark);
        text-decoration: none;
    }

    .btn-act:hover {
        background: var(--imperial-navy);
        color: white;
        border-color: var(--imperial-navy);
    }

    .btn-act-pdf {
        color: #dc2626;
        border-color: #fca5a5;
        background: #fff5f5;
    }

    .btn-act-pdf:hover {
        background: #dc2626;
        color: white;
        border-color: #dc2626;
    }

    /* Modal Preview Dialog (PDF Simulator) */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(14, 30, 56, 0.75);
        backdrop-filter: blur(4px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        padding: 20px;
    }

    .modal-container {
        background: white;
        width: 100%;
        max-width: 880px;
        max-height: 94vh;
        border-radius: 12px;
        box-shadow: 0 25px 50px -12px rgba(0,0,0,0.3);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border: 2px solid var(--heritage-gold);
        animation: modalFadeIn 0.25s ease-out;
    }

    @keyframes modalFadeIn {
        from { opacity: 0; transform: scale(0.96); }
        to { opacity: 1; transform: scale(1); }
    }

    .modal-header-cust {
        background: var(--imperial-navy);
        color: white;
        padding: 12px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .modal-header-cust h3 {
        font-size: 1.05rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0;
        color: white;
    }

    .modal-actions {
        display: flex;
        gap: 8px;
    }

    .modal-body-cust {
        padding: 24px 30px;
        overflow-y: auto;
        background: #f1f5f9;
        display: flex;
        justify-content: center;
    }

    /* A4 Document Paper Inside Modal */
    .modal-a4-sheet {
        background: white;
        width: 210mm;
        min-height: 270mm;
        padding: 18mm 20mm;
        box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        font-family: 'Amiri', serif, 'Tajawal';
        color: #000;
        position: relative;
        box-sizing: border-box;
    }

    .doc-header-tbl {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
</style>
@endpush

@section('content')

<div class="archive-container-custom" dir="rtl">

   

    <!-- 1. KPI CARDS (CLICKABLE) -->
    <section class="kpi-row">
        <div class="kpi-card active" onclick="filterByTrack('all', this)" style="--card-color: var(--imperial-navy);">
            <div class="kpi-data">
                <span>إجمالي القرارات المؤرشفة</span>
                <strong id="stat-all">{{ $kpi['all'] }}</strong>
            </div>
            <div class="kpi-icon"><i class="fa-solid fa-folder-tree"></i></div>
        </div>

        <div class="kpi-card" onclick="filterByTrack('phd', this)" style="--card-color: var(--badge-phd);">
            <div class="kpi-data">
                <span>قرارات الدكتوراه (قرار موحد: تعادل + أهلية)</span>
                <strong id="stat-phd">{{ $kpi['phd'] }}</strong>
            </div>
            <div class="kpi-icon"><i class="fa-solid fa-graduation-cap"></i></div>
        </div>

        <div class="kpi-card" onclick="filterByTrack('master-theo', this)" style="--card-color: var(--badge-master-theo);">
            <div class="kpi-data">
                <span>الماجستير النظري (قرار موحد: تعادل + أهلية)</span>
                <strong id="stat-mtheo">{{ $kpi['master_theo'] }}</strong>
            </div>
            <div class="kpi-icon"><i class="fa-solid fa-book-open"></i></div>
        </div>

        <div class="kpi-card" onclick="filterByTrack('master-app', this)" style="--card-color: var(--badge-master-app);">
            <div class="kpi-data">
                <span>الماجستير التطبيقي (تعادل فقط)</span>
                <strong id="stat-mapp">{{ $kpi['master_app'] }}</strong>
            </div>
            <div class="kpi-icon"><i class="fa-solid fa-briefcase"></i></div>
        </div>

        <div class="kpi-card" onclick="filterByTrack('faculty', this)" style="--card-color: var(--badge-faculty);">
            <div class="kpi-data">
                <span>سماح التدريس (حكومي)</span>
                <strong id="stat-fac">{{ $kpi['faculty'] }}</strong>
            </div>
            <div class="kpi-icon"><i class="fa-solid fa-building-columns"></i></div>
        </div>

        <div class="kpi-card" onclick="filterByTrack('rejected', this)" style="--card-color: var(--badge-rejected);">
            <div class="kpi-data">
                <span>قرارات عدم الموافقة</span>
                <strong id="stat-rej">{{ $kpi['rejected'] }}</strong>
            </div>
            <div class="kpi-icon"><i class="fa-solid fa-circle-xmark"></i></div>
        </div>
    </section>

    <!-- 2. SEARCH & FILTER PANEL -->
    <section class="filter-panel">
        <div class="search-row">
            <div class="search-box">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="live-search" class="search-input" placeholder="ابحث فوراً بالرقم الوطني (11 خانة)، اسم المرشح، رقم القرار، أو الجامعة..." onkeyup="performSearch()">
            </div>
            <select id="filter-year" class="select-filter" onchange="performSearch()">
                <option value="">جميع الأعوام</option>
                @foreach($years as $yr)
                    <option value="{{ $yr }}" {{ $yr == date('Y') ? 'selected' : '' }}>عام {{ $yr }}</option>
                @endforeach
            </select>
            <select id="filter-uni" class="select-filter" onchange="performSearch()">
                <option value="">كل الجامعات الطالبة</option>
                @foreach($universities as $u)
                    <option value="{{ $u }}">{{ $u }}</option>
                @endforeach
            </select>
            <button class="btn-export" onclick="exportData()">
                <i class="fa-solid fa-file-excel"></i> تصدير الكشف (Excel)
            </button>
        </div>

        <!-- 5 TABS NAVIGATION -->
        <div class="group-nav">
            <button class="tab-btn active" onclick="filterByTrack('all', this)">
                <i class="fa-solid fa-list-check"></i> الكل <span class="tab-badge" id="badge-all">{{ $kpi['all'] }}</span>
            </button>
            <button class="tab-btn" onclick="filterByTrack('phd', this)">
                <i class="fa-solid fa-graduation-cap"></i> الدكتوراه <span class="tab-badge">{{ $kpi['phd'] }}</span>
            </button>
            <button class="tab-btn" onclick="filterByTrack('master-theo', this)">
                <i class="fa-solid fa-book-bookmark"></i> الماجستير النظري <span class="tab-badge">{{ $kpi['master_theo'] }}</span>
            </button>
            <button class="tab-btn" onclick="filterByTrack('master-app', this)">
                <i class="fa-solid fa-screwdriver-wrench"></i> الماجستير التطبيقي <span class="tab-badge">{{ $kpi['master_app'] }}</span>
            </button>
            <button class="tab-btn" onclick="filterByTrack('faculty', this)">
                <i class="fa-solid fa-chalkboard-user"></i> السماح بالتدريس <span class="tab-badge">{{ $kpi['faculty'] }}</span>
            </button>
            <button class="tab-btn tab-rejected" onclick="filterByTrack('rejected', this)">
                <i class="fa-solid fa-triangle-exclamation"></i> عدم الموافقة <span class="tab-badge">{{ $kpi['rejected'] }}</span>
            </button>
        </div>
    </section>

    <!-- 3. DATA GRID -->
    <section class="archive-card">
        <div class="archive-header">
            <h2><i class="fa-solid fa-table-list" style="color: var(--heritage-gold);"></i> مستودع القرارات الأكاديمية الصادرة والمعتمدة</h2>
            <span class="result-count">يتم عرض <b id="displayed-count">{{ $archiveItems->count() }}</b> قرارات مطابقة</span>
        </div>

        <div class="table-responsive">
            <table class="archive-table" id="archive-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>المرشح والرقم الوطني</th>
                        <th>المسار والمعاملة</th>
                        <th>الجامعة الطالبة</th>
                        <th>قرار المعادلة / التعادل</th>
                        <th>بيانات قرار الأهلية</th>
                        <th>تاريخ الصدور</th>
                        <th style="text-align: center;">الإجراءات السريعة</th>
                    </tr>
                </thead>
                <tbody id="table-body">
                    @forelse($archiveItems as $item)
                        @php
                            $app = $item['app'];
                            $cand = $item['candidate'];
                            $dec = $item['decision'];
                            $track = $item['track'];
                            $candName = $cand->full_name ?? '—';
                            $natId = $cand->national_id ?? $cand->id_number ?? '—';
                            $firstEdu = $app->educations->first();
                            $degree = ($firstEdu?->level?->name ?? $app->request_type) . ($firstEdu?->exact_specialization ? ' في ' . $firstEdu->exact_specialization : '');
                            $grantingUni = $firstEdu?->university?->name ?? $firstEdu?->faculty ?? $item['uniName'];
                            $statusLabel = $item['isRejected'] ? 'عدم موافقة' : 'موافق عليه';
                        @endphp
                        <tr data-track="{{ $track }}" data-year="{{ $item['year'] }}" data-uni="{{ $item['uniName'] }}">
                            <td class="fw-bold font-monospace">{{ $app->application_no ?? ('#' . $app->id) }}</td>
                            <td>
                                <div class="cand-name">
                                    {{ $candName }}
                                </div>
                                <span class="cand-natid">{{ $natId }}</span>
                            </td>
                            <td>
                                @if($track === 'phd')
                                    <span class="badge-track badge-phd"><i class="fa-solid fa-star"></i> {{ $app->request_type ?? 'دكتوراه' }}</span>
                                @elseif($track === 'master-theo')
                                    <span class="badge-track badge-master-theo"><i class="fa-solid fa-book-open"></i> {{ $app->request_type ?? 'ماجستير نظري' }}</span>
                                @elseif($track === 'master-app')
                                    <span class="badge-track badge-master-app"><i class="fa-solid fa-briefcase"></i> {{ $app->request_type ?? 'ماجستير تطبيقي' }}</span>
                                @elseif($track === 'faculty')
                                    <span class="badge-track badge-faculty"><i class="fa-solid fa-building-columns"></i> سماح بالتدريس</span>
                                @elseif($track === 'rejected')
                                    <span class="badge-track badge-rejected"><i class="fa-solid fa-ban"></i> عدم موافقة</span>
                                @else
                                    <span class="badge-track" style="background: #f1f5f9; color: #475569;">{{ $app->request_type }}</span>
                                @endif
                            </td>
                            <td class="fw-bold">{{ $item['uniName'] }}</td>
                            
                            {{-- قرار المعادلة / التعادل الموحد / كتاب عدم الموافقة --}}
                            <td>
                                @if($item['isRejected'])
                                    <div style="color: var(--badge-rejected); font-weight: 800;">كتاب: {{ $dec->decision_no ?? '—' }}</div>
                                    @if($dec && $dec->file_path)
                                        <a href="{{ route('admin.decisions.view-file', ['id' => $dec->id, 'type' => 'equivalence']) }}" target="_blank" class="btn-act btn-act-pdf" style="font-size: 0.72rem; padding: 2px 6px; margin-top: 3px; color: #dc2626;">
                                            <i class="fa-solid fa-file-pdf"></i> كتاب عدم الموافقة
                                        </a>
                                    @else
                                        <span class="text-muted fs-8">—</span>
                                    @endif
                                @else
                                    <div>رقم: <strong>{{ $dec->decision_no ?? '—' }}</strong></div>
                                    @if($dec && $dec->file_path)
                                        <a href="{{ route('admin.decisions.view-file', ['id' => $dec->id, 'type' => 'equivalence']) }}" target="_blank" class="btn-act btn-act-pdf" style="font-size: 0.72rem; padding: 2px 6px; margin-top: 3px;">
                                            <i class="fa-solid fa-file-pdf"></i> {{ ($track === 'phd' || $track === 'master-theo') ? 'PDF القرار الموحد' : 'PDF القرار' }}
                                        </a>
                                    @endif
                                @endif
                            </td>

                            {{-- بيانات قرار الأهلية (مُدرجة في القرار الموحد للمسارات النظرية والدكتوراه) --}}
                            <td>
                                @if($item['isRejected'])
                                    <span style="color: #ef4444; font-size: 0.8rem; font-weight: bold;">— (غير مطلوب / عدم موافقة)</span>
                                @elseif($track === 'master-app' || $track === 'faculty')
                                    <span style="color: #94a3b8; font-weight: bold;">— (غير مطلوب)</span>
                                @else
                                    @if($dec && ($dec->eligibility_decision_no || $dec->eligibility_decision_date))
                                        <div>رقم الأهلية: <strong>{{ $dec->eligibility_decision_no ?: '—' }}</strong></div>
                                        <div class="text-muted" style="font-size: 0.78rem; margin-top: 2px;">
                                            تاريخ الأهلية: <strong>{{ $dec->eligibility_decision_date ? \Carbon\Carbon::parse($dec->eligibility_decision_date)->format('d/m/Y') : '—' }}</strong>
                                        </div>
                                    @else
                                        <span class="text-muted fs-8">—</span>
                                    @endif
                                @endif
                            </td>

                            <td class="text-muted">{{ $item['dateFormatted'] }}</td>
                            <td style="text-align: center;">
                                <div class="action-group" style="justify-content: center;">
                                    <a href="{{ route('admin.reports.download_consolidated_pdf', $app->id) }}" target="_blank" class="btn-act" style="background: #0e1e38; color: white; display: inline-flex; align-items: center; gap: 5px; padding: 5px 10px; font-size: 0.78rem; font-weight: 700; border-radius: 6px;" title="تحميل الإضبارة والمرفقات كاملة كملف مدمج (Bundle PDF)">
                                        <i class="fa-solid fa-file-zipper" style="color: #c59b27;"></i> الإضبارة المدمجة
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-folder-open fs-1 opacity-50 mb-2 d-block"></i>
                                <span>لا توجد قرارات مؤرشفة حالياً في النظام.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

</div>

<!-- 4. MODAL: LIVE PDF DECISION PREVIEW (SOLVES IDM ISSUE) -->
<div class="modal-overlay" id="pdfModal">
    <div class="modal-container">
        <div class="modal-header-cust">
            <h3><i class="fa-solid fa-file-circle-check" style="color: var(--heritage-gold-light);"></i> المعاينة الرسمية للوثيقة المؤرشفة</h3>
            <div class="modal-actions">
                <button class="btn-act" onclick="window.print()" style="background: var(--heritage-gold); color: white; border: none;">
                    <i class="fa-solid fa-print"></i> طباعة فورية
                </button>
                <a href="#" id="modal-download-direct-btn" target="_blank" class="btn-act" style="display: none; background: white; color: var(--imperial-navy);">
                    <i class="fa-solid fa-file-arrow-down"></i> فتح الملف الأصلي
                </a>
                <button class="btn-act" onclick="closeModal('pdfModal')">
                    <i class="fa-solid fa-xmark"></i> إغلاق
                </button>
            </div>
        </div>
        <div class="modal-body-cust">
            <div class="modal-a4-sheet">
                <!-- Republic Header Table -->
                <table class="doc-header-tbl">
                    <tr>
                        <td style="width: 40%; text-align: right; font-weight: bold; font-size: 13.5px; line-height: 1.5;">
                            الجمهورية العربية السورية<br>
                            مجلس التعليم العالي<br>
                            لجنة التأهيل ومعادلة الدرجات العلمية<br>
                            الرقم: <span id="m-dec-no" style="color: #b45309;">--</span><br>
                            التاريخ: <span id="m-dec-date">--</span>
                        </td>
                        <td style="width: 20%; text-align: center;">
                            <i class="fa-solid fa-shield-halved" style="font-size: 44px; color: var(--heritage-gold);"></i>
                        </td>
                        <td dir="ltr" style="width: 40%; text-align: left; font-weight: bold; font-size: 12.5px; line-height: 1.4;">
                            Syrian Arab Republic<br>
                            Council of Higher Education
                        </td>
                    </tr>
                </table>

                <div style="text-align: center; margin: 25px 0 15px; font-weight: bold; font-size: 18px; text-decoration: underline;" id="m-title">
                    قرار معادلة رسمي
                </div>

                <div style="font-size: 14.5px; line-height: 2; text-align: justify; margin-bottom: 22px;">
                    بناءً على أحكام قانون تنظيم الجامعات رقم /6/ لعام 2006 ولائحته التنفيذية، وعلى قرارات مجلس التعليم العالي ولجنة التأهيل ومعادلة الدرجات العلمية:
                </div>

                <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; padding: 18px 20px; border-radius: 6px; font-size: 15.5px; line-height: 2.2; margin-bottom: 35px; text-align: justify;" id="m-body">
                    --
                </div>

                <!-- Official Signatures -->
                <table style="width: 100%; text-align: center; font-size: 14.5px; font-weight: bold; margin-top: 45px;">
                    <tr>
                        <td style="width: 50%;">
                            مدير التعادل والإنتاج العلمي<br><br>
                            <strong>المهندس عمار هلال</strong>
                        </td>
                        <td style="width: 50%;">
                            أمين مجلس التعليم العالي<br><br>
                            <strong>الدكتور علي الجاسم</strong>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- 5. MODAL: CANDIDATE 360° PROFILE -->
<div class="modal-overlay" id="candModal">
    <div class="modal-container" style="max-width: 580px;">
        <div class="modal-header-cust">
            <h3><i class="fa-solid fa-address-card" style="color: var(--heritage-gold-light);"></i> السجل الأكاديمي الشامل 360°</h3>
            <button class="btn-act" onclick="closeModal('candModal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div style="padding: 24px; background: white;">
            <div style="display: flex; gap: 16px; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 16px; margin-bottom: 16px;">
                <div style="width: 52px; height: 52px; border-radius: 50%; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                    <i class="fa-solid fa-user-graduate"></i>
                </div>
                <div>
                    <h4 id="c-name" style="font-size: 1.15rem; font-weight: 800; color: var(--imperial-navy); margin: 0;">--</h4>
                    <span id="c-natid" style="font-family: monospace; color: var(--text-muted); font-size: 0.88rem;">--</span>
                </div>
            </div>

            <div style="display: grid; gap: 11px; font-size: 0.9rem;">
                <div><strong>الشهادة الحالية:</strong> <span id="c-degree">--</span></div>
                <div><strong>الجامعة المانحة:</strong> <span id="c-uni">--</span></div>
                <div><strong>الحالة القانونية:</strong> <span id="c-status" class="badge-track badge-phd">--</span></div>
                <div style="margin-top: 14px; border-top: 1px dashed var(--border-color); padding-top: 12px;">
                    <div style="font-weight: bold; margin-bottom: 6px; color: var(--heritage-gold);"><i class="fa-solid fa-timeline me-1"></i> التسلسل الأكاديمي المعتمد:</div>
                    <ul style="padding-right: 20px; color: #475569; font-size: 0.85rem; line-height: 1.8; margin-bottom: 0;">
                        <li>الإجازة الجامعية الأولى (سورية/معادلة) - معتمدة بالأرشيف</li>
                        <li>درجة الدراسات العليا (أطروحة بحثية مسجلة) - معتمدة</li>
                        <li>التأهيل والتدريس بالجامعات الخاصة - ممهور بالختم الوزاري</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let currentTrack = 'all';

    // Filter Table by Track
    function filterByTrack(track, element) {
        currentTrack = track;
        
        // Sync Tabs UI
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        if(element.classList.contains('tab-btn')) {
            element.classList.add('active');
        } else {
            // Triggered from KPI card
            const matchingTab = Array.from(document.querySelectorAll('.tab-btn')).find(b => b.getAttribute('onclick')?.includes(track));
            if(matchingTab) matchingTab.classList.add('active');
        }

        // Sync KPI Card UI
        document.querySelectorAll('.kpi-card').forEach(card => card.classList.remove('active'));
        const matchingCard = Array.from(document.querySelectorAll('.kpi-card')).find(c => c.getAttribute('onclick')?.includes(track));
        if(matchingCard) matchingCard.classList.add('active');

        performSearch();
    }

    // Real-Time Search & Multi-Criteria Filtering
    function performSearch() {
        const query = document.getElementById('live-search').value.toLowerCase().trim();
        const year = document.getElementById('filter-year').value;
        const uni = document.getElementById('filter-uni').value;
        const rows = document.querySelectorAll('#table-body tr');

        let visibleCount = 0;

        rows.forEach(row => {
            const track = row.getAttribute('data-track');
            const rowYear = row.getAttribute('data-year');
            const rowUni = row.getAttribute('data-uni');
            const text = row.innerText.toLowerCase();

            const matchTrack = (currentTrack === 'all' || track === currentTrack);
            const matchYear = (year === '' || rowYear === year);
            const matchUni = (uni === '' || (rowUni && rowUni.includes(uni)));
            const matchQuery = (query === '' || text.includes(query));

            if (matchTrack && matchYear && matchUni && matchQuery) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const counterEl = document.getElementById('displayed-count');
        if (counterEl) counterEl.innerText = visibleCount;
    }

    // Live PDF Preview Modal
    function openPreviewModal(title, candName, decNo, decDate, bodyText, pdfUrl) {
        document.getElementById('m-title').innerText = title + ' - ' + candName;
        document.getElementById('m-dec-no').innerText = decNo;
        document.getElementById('m-dec-date').innerText = decDate;
        document.getElementById('m-body').innerText = bodyText;
        
        const directBtn = document.getElementById('modal-download-direct-btn');
        if (pdfUrl) {
            directBtn.href = pdfUrl;
            directBtn.style.display = 'inline-flex';
        } else {
            directBtn.style.display = 'none';
        }

        document.getElementById('pdfModal').style.display = 'flex';
    }

    // Candidate 360 Profile
    function viewCandidate360(name, natId, degree, uni, status) {
        document.getElementById('c-name').innerText = name;
        document.getElementById('c-natid').innerText = 'الرقم الوطني: ' + natId;
        document.getElementById('c-degree').innerText = degree;
        document.getElementById('c-uni').innerText = uni;
        document.getElementById('c-status').innerText = status;
        document.getElementById('candModal').style.display = 'flex';
    }

    // Close Modals
    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
    }

    // Close on Outside Click
    window.onclick = function(event) {
        if (event.target.classList.contains('modal-overlay')) {
            event.target.style.display = 'none';
        }
    };

    // Export Table Data to pristine UTF-8 Microsoft Excel (.xls) with RTL alignment
    function exportData() {
        const rows = document.querySelectorAll('#table-body tr');
        let tableRowsHtml = '';
        let exportedCount = 0;

        rows.forEach(row => {
            if (row.style.display !== 'none') {
                const cols = row.querySelectorAll('td');
                if (cols.length >= 7) {
                    const appNo = cols[0].innerText.trim();
                    const candEl = cols[1].querySelector('.cand-name');
                    const candName = candEl ? candEl.innerText.trim() : '';
                    const natIdEl = cols[1].querySelector('.cand-natid');
                    const natId = natIdEl ? natIdEl.innerText.trim() : '';
                    const track = cols[2].innerText.trim();
                    const uni = cols[3].innerText.trim();
                    const equiv = cols[4].innerText.trim().replace(/[\r\n]+/g, ' - ');
                    const elig = cols[5].innerText.trim().replace(/[\r\n]+/g, ' - ');
                    const date = cols[6].innerText.trim();

                    tableRowsHtml += `
                    <tr>
                        <td style="mso-number-format:'\\@'; text-align:center;">${appNo}</td>
                        <td style="font-weight:bold;">${candName}</td>
                        <td style="mso-number-format:'\\@'; text-align:center;">${natId}</td>
                        <td>${track}</td>
                        <td>${uni}</td>
                        <td style="mso-number-format:'\\@';">${equiv}</td>
                        <td style="mso-number-format:'\\@';">${elig}</td>
                        <td style="text-align:center;">${date}</td>
                    </tr>`;
                    exportedCount++;
                }
            }
        });

        if (exportedCount === 0) {
            alert('لا توجد بيانات مطابقة للفلترة الحالية لتصديرها.');
            return;
        }

        const excelXml = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">' +
        '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">' +
        '<!--[if gte mso 9]><xml>' +
        '<x' + ':ExcelWorkbook><x' + ':ExcelWorksheets><x' + ':ExcelWorksheet>' +
        '<x' + ':Name>الأرشيف المركزي</x' + ':Name>' +
        '<x' + ':WorksheetOptions><x' + ':DisplayRightToLeft/></x' + ':WorksheetOptions>' +
        '</x' + ':ExcelWorksheet></x' + ':ExcelWorksheets></x' + ':ExcelWorkbook>' +
        '</xml><![endif]-->' +
        '<style>' +
        'body { font-family: "Segoe UI", Tahoma, Arial, sans-serif; direction: rtl; text-align: right; }' +
        'table { border-collapse: collapse; direction: rtl; width: 100%; }' +
        'th { background-color: #0e1e38; color: #ffffff; font-weight: bold; border: 1px solid #c59b27; padding: 10px 14px; font-size: 13px; text-align: right; }' +
        'td { border: 1px solid #cbd5e1; padding: 8px 12px; font-size: 12px; vertical-align: middle; text-align: right; }' +
        'tr:nth-child(even) { background-color: #f8fafc; }' +
        '</style></head>' +
        '<body dir="rtl">' +
        '<h3 style="color:#0e1e38; font-family: Tahoma, sans-serif; margin-bottom: 4px;">الجمهورية العربية السورية - مجلس التعليم العالي</h3>' +
        '<h4 style="color:#c59b27; margin-top: 0; margin-bottom: 8px;">الأرشيف المركزي وسجل القرارات الأكاديمية الصادرة والمعتمدة</h4>' +
        '<p style="color:#64748b; font-size:12px; margin-bottom: 14px;">تاريخ استخراج الكشف: ' + new Date().toLocaleDateString('ar-SY') + ' | إجمالي السجلات: ' + exportedCount + '</p>' +
        '<table border="1"><thead><tr>' +
        '<th style="text-align:center;">رقم المعاملة</th><th>اسم المرشح</th><th style="text-align:center;">الرقم الوطني</th>' +
        '<th>المسار الأكاديمي</th><th>الجامعة الطالبة</th><th>قرار المعادلة</th><th>قرار الأهلية</th><th style="text-align:center;">تاريخ الصدور</th>' +
        '</tr></thead><tbody>' +
        tableRowsHtml +
        '</tbody></table></body></html>';

        const blob = new Blob(['\uFEFF' + excelXml], { type: 'application/vnd.ms-excel;charset=utf-8;' });
        const link = document.createElement("a");
        const url = URL.createObjectURL(blob);
        link.setAttribute("href", url);
        link.setAttribute("download", `كشف_القرارات_والأرشيف_المركزي_${new Date().toISOString().slice(0,10)}.xls`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const params = new URLSearchParams(window.location.search);
        const initialTrack = params.get('track');
        if (initialTrack) {
            const targetTab = Array.from(document.querySelectorAll('.tab-btn')).find(b => b.getAttribute('onclick')?.includes(`'${initialTrack}'`));
            if (targetTab) {
                filterByTrack(initialTrack, targetTab);
            }
        }
    });
</script>
@endpush

@endsection
