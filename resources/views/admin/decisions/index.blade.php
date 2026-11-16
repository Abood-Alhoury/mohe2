@extends('layouts.admin')

@section('title', 'إصدار ورصد القرارات الرسمية - مجلس التعليم العالي')

@push('styles')
    <!-- خطوط عربية وأيقونات FontAwesome -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Amiri:wght@400;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --imperial-navy: #0e1e38;
            --imperial-navy-dark: #081224;
            --imperial-navy-light: #18335d;
            --heritage-gold: #c59b27;
            --heritage-gold-light: #f3d47f;
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

        /* Ensure parent flex container never expands past viewport */
        main.flex-grow-1 {
            min-width: 0 !important;
        }

        .decisions-scope {
            font-family: 'Tajawal', sans-serif;
            color: var(--text-dark);
            padding: 0.2rem 0.45rem 1.25rem;
            width: 100%;
            max-width: 100%;
            min-width: 0;
            box-sizing: border-box;
        }

        /* Top Government Bar */
        .gov-top-bar {
            background: linear-gradient(135deg, var(--imperial-navy-dark) 0%, var(--imperial-navy) 100%);
            color: white;
            padding: 14px 24px;
            border-bottom: 3.5px solid var(--heritage-gold);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }

        .gov-branding {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .gov-logo-box {
            width: 48px;
            height: 48px;
            background: rgba(255,255,255,0.08);
            border: 1.5px solid var(--heritage-gold-light);
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
            margin: 0;
            color: white;
        }

        .gov-titles span {
            font-size: 0.8rem;
            color: #94a3b8;
            display: block;
            margin-top: 2px;
        }

        .gov-user-pill {
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(255,255,255,0.06);
            padding: 8px 16px;
            border-radius: 30px;
            border: 1px solid rgba(255,255,255,0.1);
        }

        .gov-user-avatar {
            width: 34px;
            height: 34px;
            background: var(--heritage-gold);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: var(--imperial-navy-dark);
            font-size: 14px;
        }

        /* 5 Master Tabs */
        .tracks-nav {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            margin-bottom: 14px;
            background: #ffffff;
            padding: 8px 10px;
            border-radius: 10px;
            border: 1.5px solid #cbd5e1 !important;
            box-shadow: 0 2px 10px rgba(14, 30, 56, 0.05);
            width: 100%;
            box-sizing: border-box;
        }

        .track-btn {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 6px 12px;
            border-radius: 7px;
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--text-muted);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .track-btn:hover {
            border-color: var(--imperial-navy-light);
            color: var(--imperial-navy);
            background: #f1f5f9;
        }

        .track-btn.active {
            background: var(--imperial-navy);
            border-color: var(--imperial-navy);
            color: white;
            box-shadow: 0 3px 10px rgba(14, 30, 56, 0.16);
        }

        .track-btn.btn-rejected.active {
            background: var(--badge-rejected);
            border-color: var(--badge-rejected);
            color: white;
        }

        .track-counter {
            background: rgba(15, 23, 42, 0.08);
            padding: 1px 7px;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 700;
        }

        .track-btn.active .track-counter {
            background: var(--heritage-gold);
            color: var(--imperial-navy-dark);
            font-weight: 800;
        }

        /* Workspace Grid: Form (Right) + Table (Left) */
        .workspace-grid {
            display: grid;
            grid-template-columns: 380px minmax(0, 1fr);
            gap: 16px;
            align-items: start;
            width: 100%;
            box-sizing: border-box;
        }

        @media (max-width: 1120px) {
            .workspace-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Issuance Card (Form Panel) */
        .form-card {
            background: #ffffff;
            border-radius: 12px !important;
            border: 1.5px solid #cbd5e1 !important;
            border-top: 3.5px solid var(--imperial-navy) !important;
            box-shadow: 0 4px 16px rgba(14, 30, 56, 0.06) !important;
            position: sticky;
            top: 16px;
            overflow: hidden;
            box-sizing: border-box;
            padding: 14px 16px !important;
            min-width: 0;
        }

        .form-card-header {
            padding: 0 0 10px 0 !important;
            border-bottom: 1.5px solid #e2e8f0 !important;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            background: #ffffff;
            margin-bottom: 12px !important;
        }

        .form-card-header h2 {
            font-size: 0.94rem;
            font-weight: 800;
            color: var(--imperial-navy);
            display: flex;
            align-items: center;
            gap: 6px;
            margin: 0;
            line-height: 1.35;
        }

        .form-body {
            padding: 0;
        }

        .form-group {
            margin-bottom: 10px;
        }

        .form-label {
            display: block;
            font-size: 0.76rem;
            font-weight: 700;
            color: #334155;
            margin-bottom: 4px;
            line-height: 1.3;
        }

        .form-control-custom {
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
            padding: 6px 10px;
            height: 36px;
            border: 1.5px solid #94a3b8 !important;
            border-radius: 8px;
            font-size: 0.81rem;
            outline: none;
            transition: all 0.2s;
            background: #ffffff;
            color: #0f172a;
        }

        textarea.form-control-custom {
            height: auto;
            min-height: 58px;
        }

        input[type="file"].form-control-custom {
            height: auto;
            padding: 6px 10px;
            font-size: 0.76rem;
            border: 1.5px dashed #94a3b8 !important;
            background: #ffffff;
        }

        .form-control-custom[readonly] {
            background: #f1f5f9 !important;
            border-color: #cbd5e1 !important;
            color: #1e293b;
        }

        .form-control-custom:focus {
            border-color: #c59b27 !important;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(197, 155, 39, 0.16);
        }

        /* Sub-sections for Decisions */
        .decision-block {
            background: #f8fafc !important;
            border: 1.5px solid #cbd5e1 !important;
            border-radius: 10px !important;
            padding: 11px 13px !important;
            margin-bottom: 11px !important;
            position: relative;
            box-sizing: border-box;
            overflow: hidden;
            box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.02);
        }

        .decision-block.block-eligibility {
            background: #f8fbff !important;
            border: 1.5px solid #bae6fd !important;
            border-right: 4px solid #0284c7 !important;
        }

        .decision-block.block-equivalence {
            background: #fcfcfd !important;
            border: 1.5px solid #cbd5e1 !important;
            border-right: 4px solid var(--heritage-gold) !important;
        }

        .decision-block.block-rejected {
            border: 1.5px solid #fecaca !important;
            border-right: 4px solid var(--badge-rejected) !important;
            background: #fff5f5 !important;
        }

        .block-title {
            font-size: 0.78rem;
            font-weight: 800;
            color: var(--imperial-navy);
            margin-bottom: 9px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 6px;
        }

        .row-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            align-items: end;
        }

        .row-2 > div {
            min-width: 0;
        }

        .btn-submit-decision {
            width: 100%;
            background: #c59b27;
            color: #ffffff;
            border: none;
            padding: 9px 14px;
            border-radius: 8px;
            font-size: 0.84rem;
            font-weight: 800;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            box-shadow: 0 3px 8px rgba(197, 155, 39, 0.25);
            transition: all 0.2s;
            margin-top: 6px;
        }

        .btn-submit-decision:hover {
            background: #b0891e;
            transform: translateY(-1px);
            box-shadow: 0 6px 12px rgba(197, 155, 39, 0.3);
        }

        /* Pending Requests Table (Left Panel) */
        .table-card {
            background: #ffffff;
            border-radius: 12px !important;
            border: 1.5px solid #cbd5e1 !important;
            border-top: 3.5px solid var(--heritage-gold) !important;
            box-shadow: 0 4px 16px rgba(14, 30, 56, 0.06);
            overflow: hidden;
            min-width: 0;
            width: 100%;
            box-sizing: border-box;
        }

        .table-card-header {
            padding: 11px 16px;
            border-bottom: 1.5px solid #cbd5e1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f8fafc;
            flex-wrap: wrap;
            gap: 10px;
        }

        .table-card-header h2 {
            font-size: 0.94rem;
            font-weight: 800;
            color: var(--imperial-navy);
            display: flex;
            align-items: center;
            gap: 7px;
            margin: 0;
        }

        .search-mini {
            position: relative;
            width: 230px;
            max-width: 100%;
        }

        .search-mini input {
            width: 100%;
            height: 34px;
            padding: 5px 32px 5px 10px;
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            font-size: 0.79rem;
            outline: none;
            background: #ffffff;
            box-sizing: border-box;
        }

        .search-mini input:focus {
            border-color: var(--heritage-gold);
            box-shadow: 0 0 0 2px rgba(197, 155, 39, 0.15);
        }

        .search-mini i {
            position: absolute;
            right: 11px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 0.8rem;
        }

        table.requests-table {
            width: 100%;
            border-collapse: collapse;
            text-align: right;
            margin-bottom: 0;
        }

        table.requests-table th {
            background: #f1f5f9;
            color: #334155;
            font-size: 0.77rem;
            font-weight: 800;
            padding: 9px 12px;
            border-bottom: 1.5px solid #cbd5e1;
            border-left: 1px solid #e2e8f0;
            white-space: nowrap;
        }

        table.requests-table td {
            padding: 9px 12px;
            border-bottom: 1px solid #cbd5e1;
            border-left: 1px solid #e2e8f0;
            font-size: 0.81rem;
            vertical-align: middle;
        }

        table.requests-table th:last-child,
        table.requests-table td:last-child {
            border-left: none;
        }

        table.requests-table tr:hover td {
            background: #f8fbff;
        }

        table.requests-table tr.selected td {
            background: #eff6ff;
            border-right: 3px solid var(--imperial-navy);
        }

        .cand-info strong {
            display: block;
            color: var(--imperial-navy);
            font-size: 0.84rem;
            cursor: pointer;
        }

        .cand-info span {
            font-size: 0.72rem;
            color: var(--text-muted);
            font-family: monospace;
        }

        /* Action Buttons */
        .btn-act-table {
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 0.76rem;
            font-weight: 700;
            border: 1px solid #cbd5e1;
            background: white;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            white-space: nowrap;
            transition: all 0.15s;
        }

        .btn-act-table:hover {
            background: var(--imperial-navy);
            color: white;
            border-color: var(--imperial-navy);
        }

        .btn-act-select {
            background: #eff6ff;
            color: #1d4ed8;
            border-color: #bfdbfe;
        }

        .btn-act-select:hover {
            background: #1d4ed8;
            color: white;
        }

        .btn-act-reject {
            background: #fef2f2;
            color: #dc2626;
            border-color: #fecaca;
        }

        .btn-act-reject:hover {
            background: #dc2626;
            color: white;
        }


        /* Success Alert Toast */
        .toast-notify {
            position: fixed;
            bottom: 25px;
            left: 25px;
            background: #065f46;
            color: white;
            padding: 14px 22px;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            font-weight: 700;
            display: none;
            align-items: center;
            gap: 10px;
            z-index: 10000;
            border-right: 4px solid #34d399;
        }
    </style>
@endpush

@section('content')
<div class="decisions-scope" dir="rtl">



    <!-- 5 TABS NAVIGATION -->
    <nav class="tracks-nav">
        <button type="button" class="track-btn {{ $activeTrack === 'master-theo' ? 'active' : '' }}" onclick="switchTrack('master-theo', this)">
            <i class="fa-solid fa-book-open"></i> الماجستير النظري (داخلي وخارجي)
            <span class="track-counter" id="cnt-mtheo">{{ $cntMtheo }}</span>
        </button>
        <button type="button" class="track-btn {{ $activeTrack === 'master-app' ? 'active' : '' }}" onclick="switchTrack('master-app', this)">
            <i class="fa-solid fa-screwdriver-wrench"></i> الماجستير التطبيقي (داخلي وخارجي)
            <span class="track-counter" id="cnt-mapp">{{ $cntMapp }}</span>
        </button>
        <button type="button" class="track-btn {{ $activeTrack === 'phd' ? 'active' : '' }}" onclick="switchTrack('phd', this)">
            <i class="fa-solid fa-graduation-cap"></i> الدكتوراه (داخلية وخارجية)
            <span class="track-counter" id="cnt-phd">{{ $cntPhd }}</span>
        </button>
        <button type="button" class="track-btn {{ $activeTrack === 'faculty' ? 'active' : '' }}" onclick="switchTrack('faculty', this)">
            <i class="fa-solid fa-building-columns"></i> السماح بالتدريس (حكومي)
            <span class="track-counter" id="cnt-fac">{{ $cntFac }}</span>
        </button>
        <button type="button" class="track-btn btn-rejected {{ $activeTrack === 'rejected' ? 'active' : '' }}" onclick="switchTrack('rejected', this)">
            <i class="fa-solid fa-circle-xmark"></i> قرارات عدم الموافقة
            <span class="track-counter" id="cnt-rej">{{ $cntRej }}</span>
        </button>
    </nav>

    <!-- WORKSPACE GRID -->
    <div class="workspace-grid">

        <!-- FORM PANEL (RIGHT) -->
        <section class="form-card">
            <div class="form-card-header">
                <h2><i class="fa-solid fa-file-pen" style="color: var(--heritage-gold);"></i> <span id="form-title">إصدار ورصد قرار ماجستير نظري</span></h2>
                <span class="badge" id="form-badge" style="background: #e0f2fe; color: #0284c7; padding: 3px 8px; border-radius: 6px; font-weight: 800; font-size: 0.7rem; white-space: nowrap;">قرار موحد (ورقة واحدة تتضمن الأهلية)</span>
            </div>

            <div class="form-body">
                <form id="decision-form" method="POST" action="{{ route('admin.decisions.store') }}" enctype="multipart/form-data" onsubmit="return validateBeforeSubmit(event)">
                    @csrf
                    <input type="hidden" name="application_id" id="form-application-id" value="">

                    <!-- Candidate Selector -->
                    <div class="form-group">
                        <label class="form-label">المرشح والمعاملة المراد إصدار قرار لها :</label>
                        <select id="selected-candidate-id" class="form-control-custom" onchange="syncSelectedCandidate()">
                            <!-- Populated dynamically via JS -->
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">الجامعة الطالبة للتقويم :</label>
                        <input type="text" id="target-uni-name" class="form-control-custom" value="" readonly style="font-weight: bold;">
                    </div>

                    <!-- BLOCK 1: ELIGIBILITY DECISION (Metadata shown for Theoretical Master & PhD) -->
                    <div class="decision-block block-eligibility" id="block-eligibility">
                        <div class="block-title">
                            <span><i class="fa-solid fa-certificate text-primary"></i> 1. بيانات قرار الأهلية (ديباجة القرار الموحد)</span>
                            <span style="font-size: 0.7rem; color: #0284c7;">رقم وتاريخ الأهلية</span>
                        </div>
                        <div class="row-2">
                            <div class="form-group mb-0">
                                <label class="form-label">رقم قرار الأهلية :</label>
                                <input type="text" name="eligibility_decision_no" id="elig-no" class="form-control-custom" placeholder="مثال: 502/2026" value="">
                            </div>
                            <div class="form-group mb-0">
                                <label class="form-label">تاريخ صدور الأهلية :</label>
                                <input type="date" name="eligibility_decision_date" id="elig-date" class="form-control-custom" value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>

                    <!-- BLOCK 2: EQUIVALENCE / UNIFIED DECISION (Always present except when rejected) -->
                    <div class="decision-block block-equivalence" id="block-equivalence">
                        <div class="block-title">
                            <span id="equiv-block-label"><i class="fa-solid fa-stamp" style="color: var(--heritage-gold);"></i> 2. بيانات القرار الموحد الموقّع (ورقة واحدة)</span>
                            <span style="font-size: 0.7rem; color: var(--heritage-gold-dark);">قرار لجنة التأهيل</span>
                        </div>
                        <div class="row-2 mb-2">
                            <div class="form-group mb-0">
                                <label class="form-label" id="lbl-equiv-no">رقم قرار التعادل :</label>
                                <input type="text" name="decision_no" id="equiv-no" class="form-control-custom" placeholder="مثال: 105/2026" value="" required>
                            </div>
                            <div class="form-group mb-0">
                                <label class="form-label">تاريخ صدور القرار :</label>
                                <input type="date" name="decision_date" id="equiv-date" class="form-control-custom" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label class="form-label" id="lbl-equiv-file">نسخة القرار الرسمي الموقّع (PDF/صورة - ورقة واحدة) :</label>
                            <input type="file" name="decision_file" id="equiv-file" class="form-control-custom" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                    </div>

                    <!-- BLOCK 3: REJECTION BLOCK (Only shown for 'rejected' track) -->
                    <div class="decision-block block-rejected" id="block-rejected" style="display: none;">
                        <div class="block-title">
                            <span style="color: var(--badge-rejected);"><i class="fa-solid fa-ban"></i> بيانات كتاب عدم الموافقة المسبّب</span>
                            <span style="font-size: 0.7rem; color: #dc2626;">رفض رسمي مسبّب</span>
                        </div>
                        <div class="row-2 mb-2">
                            <div class="form-group mb-0">
                                <label class="form-label">رقم كتاب عدم الموافقة :</label>
                                <input type="text" id="rej-no" class="form-control-custom" placeholder="مثال: 12/غ.م">
                            </div>
                            <div class="form-group mb-0">
                                <label class="form-label">تاريخ الجلسة / القرار :</label>
                                <input type="date" id="rej-date" class="form-control-custom" value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="form-group mb-2">
                            <label class="form-label">أسباب عدم الموافقة (حيثيات المحضر) :</label>
                            <textarea id="rej-reason" class="form-control-custom" rows="2" placeholder="أدخل حيثيات وأسباب عدم الموافقة لتدوينها في الكتاب..."></textarea>
                        </div>
                        <div class="form-group mb-0">
                            <label class="form-label">نسخة كتاب الرفض الموقّع (PDF/صورة) :</label>
                            <input type="file" id="rej-file" class="form-control-custom" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                    </div>

                    <!-- Actions -->
                    <a href="#" id="btn-generate-selected-draft" target="_blank" class="btn w-100 fw-bold mb-2 d-none align-items-center justify-content-center gap-2" style="padding: 9px 12px; border-radius: 9px; font-size: 0.85rem;">
                        <i class="fa-solid fa-file-word"></i>
                        <span id="btn-generate-selected-label">توليد / تحميل Word مسودة القرار</span>
                    </a>
                    <button type="submit" id="btn-submit" class="btn-submit-decision">
                        <i class="fa-solid fa-circle-check"></i>
                        اعتماد ورصد القرار نهائياً
                    </button>
                </form>
            </div>
        </section>

        <!-- PENDING REQUESTS TABLE (LEFT) -->
        <section class="table-card">
            <div class="table-card-header">
                <h2>
                    <i class="fa-solid fa-clock-rotate-left" style="color: var(--imperial-navy);"></i>
                    <span id="table-heading">طلبات الماجستير النظري الجاهزة للإصدار</span>
                </h2>
                <div class="search-mini">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="table-search" placeholder="ابحث بالاسم أو الرقم الوطني..." onkeyup="filterPendingTable()">
                </div>
            </div>

            <div style="overflow-x: auto;">
                <table class="requests-table">
                    <thead>
                        <tr>
                            <th># الطلب</th>
                            <th>المرشح والرقم الوطني</th>
                            <th>المؤهل الأكاديمي المطلوب</th>
                            <th>الجامعة الطالبة</th>
                            <th>حالة المعاملة</th>
                            <th style="text-align: center;">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody id="pending-table-body">
                        <!-- Populated dynamically via JS -->
                    </tbody>
                </table>
            </div>

            {{-- Footer link to Central Archive --}}
            <div class="d-flex flex-wrap justify-content-between align-items-center px-3 py-2.5 gap-2" style="background: #f8fafc; border-top: 1.5px solid #cbd5e1;">
                <div class="text-muted d-flex align-items-center gap-2" style="font-size: 0.78rem;">
                    <i class="fa-solid fa-circle-info text-primary"></i>
                    <span>القرارات المعتمدة هنا تُرَحّل تلقائياً للأرشيف المركزي للوزارة مع إشعار الجامعة فوراً.</span>
                </div>
                <a href="{{ route('admin.archive.index') }}" class="btn btn-outline-dark btn-sm fw-bold px-3 py-1.5 rounded-3" style="font-size: 0.78rem; border-color: #94a3b8;">
                    <i class="fa-solid fa-box-archive me-1"></i>
                    الانتقال إلى الأرشيف المركزي ({{ $issuedCount ?? 0 }} قرار صادر)
                </a>
            </div>
        </section>

    </div>
</div>



<!-- TOAST NOTIFICATION -->
<div class="toast-notify" id="toast">
    <i class="fa-solid fa-circle-check fs-5"></i>
    <span id="toast-msg">تم اعتماد القرار وحفظه بالأرشيف المركزي بنجاح!</span>
</div>

@push('scripts')
<script>
    // Real Applications Data injected directly from Laravel
    const applicationsData = @json($applicationsData);

    // Routes Map for dynamic form action
    const formRoutes = {
        'master-theo': "{{ route('admin.decisions.store') }}",
        'master-app': "{{ route('admin.applied_decisions.store') }}",
        'phd': "{{ route('admin.doctorate_decisions.store') }}",
        'faculty': "{{ route('admin.faculty_decisions.store') }}",
        'rejected': "{{ route('admin.rejected_decisions.store') }}"
    };

    let activeTrack = '{{ $activeTrack ?? "master-theo" }}';
    let currentSelectedCandidate = null;

    // Switch between 5 Tracks
    function switchTrack(trackKey, btnElement) {
        activeTrack = trackKey;

        // UI Tab sync
        document.querySelectorAll('.track-btn').forEach(btn => btn.classList.remove('active'));
        if (btnElement) {
            btnElement.classList.add('active');
        } else {
            const defaultBtn = document.querySelector(`.track-btn[onclick*="${trackKey}"]`);
            if (defaultBtn) defaultBtn.classList.add('active');
        }

        // Form Adaptation Logic
        const form = document.getElementById('decision-form');
        const formTitle = document.getElementById('form-title');
        const formBadge = document.getElementById('form-badge');
        const blockElig = document.getElementById('block-eligibility');
        const blockEquiv = document.getElementById('block-equivalence');
        const blockRej = document.getElementById('block-rejected');
        const equivLabel = document.getElementById('lbl-equiv-no');
        const equivBlockLabel = document.getElementById('equiv-block-label');

        // Update form submission route
        if (form && formRoutes[trackKey]) {
            form.action = formRoutes[trackKey];
        }

        // Enable / Disable inputs according to active track
        if (trackKey === 'master-theo') {
            formTitle.innerText = 'إصدار ورصد قرار ماجستير نظري';
            formBadge.innerText = 'قرار موحد (ورقة واحدة تتضمن الأهلية)';
            formBadge.style.background = '#e0f2fe';
            formBadge.style.color = '#0284c7';
            blockElig.style.display = 'block';
            blockEquiv.style.display = 'block';
            blockRej.style.display = 'none';
            equivLabel.innerText = 'رقم قرار التعادل :';
            if (equivBlockLabel) equivBlockLabel.innerHTML = '<i class="fa-solid fa-stamp" style="color: var(--heritage-gold);"></i> 2. بيانات القرار الموحد الموقّع (ورقة واحدة)';
            setInputsState(true, true, false);
        } else if (trackKey === 'master-app') {
            formTitle.innerText = 'إصدار ورصد قرار ماجستير تطبيقي';
            formBadge.innerText = 'قرار معادلة فقط (بدون أهلية)';
            formBadge.style.background = '#ccfbf1';
            formBadge.style.color = '#0d9488';
            blockElig.style.display = 'none';
            blockEquiv.style.display = 'block';
            blockRej.style.display = 'none';
            equivLabel.innerText = 'رقم قرار التعادل :';
            if (equivBlockLabel) equivBlockLabel.innerHTML = '<i class="fa-solid fa-stamp" style="color: var(--heritage-gold);"></i> بيانات قرار التعادل الموقّع';
            setInputsState(false, true, false);
        } else if (trackKey === 'phd') {
            formTitle.innerText = 'إصدار ورصد قرار دكتوراه';
            formBadge.innerText = 'قرار موحد (ورقة واحدة تتضمن الأهلية)';
            formBadge.style.background = '#f5f3ff';
            formBadge.style.color = '#7c3aed';
            blockElig.style.display = 'block';
            blockEquiv.style.display = 'block';
            blockRej.style.display = 'none';
            equivLabel.innerText = 'رقم قرار التعادل :';
            if (equivBlockLabel) equivBlockLabel.innerHTML = '<i class="fa-solid fa-stamp" style="color: var(--heritage-gold);"></i> 2. بيانات القرار الموحد الموقّع (ورقة واحدة)';
            setInputsState(true, true, false);
        } else if (trackKey === 'faculty') {
            formTitle.innerText = 'إصدار كتاب سماح بالتدريس';
            formBadge.innerText = 'كتاب سماح وحيد (بدون أهلية)';
            formBadge.style.background = '#fef3c7';
            formBadge.style.color = '#d97706';
            blockElig.style.display = 'none';
            blockEquiv.style.display = 'block';
            blockRej.style.display = 'none';
            equivLabel.innerText = 'رقم قرار السماح :';
            if (equivBlockLabel) equivBlockLabel.innerHTML = '<i class="fa-solid fa-stamp" style="color: var(--heritage-gold);"></i> بيانات كتاب السماح الموقّع';
            setInputsState(false, true, false);
        } else if (trackKey === 'rejected') {
            formTitle.innerText = 'إصدار كتاب عدم موافقة (رفض مسبّب)';
            formBadge.innerText = 'قرار عدم موافقة';
            formBadge.style.background = '#fee2e2';
            formBadge.style.color = '#dc2626';
            blockElig.style.display = 'none';
            blockEquiv.style.display = 'none';
            blockRej.style.display = 'block';
            setInputsState(false, false, true);
        }

        // Populate Candidates Dropdown and Table for selected track
        renderTrackCandidates();
    }

    // Helper to toggle active input fields for form submission
    function setInputsState(enableElig, enableEquiv, enableRej) {
        // Block 1 (Eligibility Metadata)
        const eligNo = document.getElementById('elig-no');
        const eligDate = document.getElementById('elig-date');
        if (eligNo) {
            eligNo.disabled = !enableElig;
            eligNo.required = enableElig;
        }
        if (eligDate) {
            eligDate.disabled = !enableElig;
            eligDate.required = enableElig;
        }

        // Block 2 (Equivalence)
        const equivNo = document.getElementById('equiv-no');
        const equivDate = document.getElementById('equiv-date');
        const equivFile = document.getElementById('equiv-file');
        if (equivNo) {
            equivNo.disabled = !enableEquiv;
            if (enableEquiv) equivNo.name = 'decision_no';
            else equivNo.removeAttribute('name');
        }
        if (equivDate) {
            equivDate.disabled = !enableEquiv;
            if (enableEquiv) equivDate.name = 'decision_date';
            else equivDate.removeAttribute('name');
        }
        if (equivFile) {
            equivFile.disabled = !enableEquiv;
            if (enableEquiv) equivFile.name = 'decision_file';
            else equivFile.removeAttribute('name');
        }

        // Block 3 (Rejection)
        const rejNo = document.getElementById('rej-no');
        const rejDate = document.getElementById('rej-date');
        const rejReason = document.getElementById('rej-reason');
        const rejFile = document.getElementById('rej-file');
        if (rejNo) {
            rejNo.disabled = !enableRej;
            rejNo.required = enableRej;
            if (enableRej) rejNo.name = 'decision_no';
            else rejNo.removeAttribute('name');
        }
        if (rejDate) {
            rejDate.disabled = !enableRej;
            rejDate.required = enableRej;
            if (enableRej) rejDate.name = 'decision_date';
            else rejDate.removeAttribute('name');
        }
        if (rejReason) {
            rejReason.disabled = !enableRej;
            if (enableRej) rejReason.name = 'notes';
            else rejReason.removeAttribute('name');
        }
        if (rejFile) {
            rejFile.disabled = !enableRej;
            if (enableRej) rejFile.name = 'decision_file';
            else rejFile.removeAttribute('name');
        }
    }

    // Render Candidates for Selected Track
    function renderTrackCandidates() {
        const select = document.getElementById('selected-candidate-id');
        const tbody = document.getElementById('pending-table-body');
        const heading = document.getElementById('table-heading');
        
        select.innerHTML = '';
        tbody.innerHTML = '';

        const filtered = applicationsData.filter(app => app.track === activeTrack);

        if (activeTrack === 'master-theo') heading.innerText = 'طلبات الماجستير النظري الجاهزة للإصدار';
        else if (activeTrack === 'master-app') heading.innerText = 'طلبات الماجستير التطبيقي الجاهزة للإصدار';
        else if (activeTrack === 'phd') heading.innerText = 'طلبات الدكتوراه الجاهزة للإصدار';
        else if (activeTrack === 'faculty') heading.innerText = 'طلبات السماح بالتدريس (الحكومية) الجاهزة';
        else if (activeTrack === 'rejected') heading.innerText = 'طلبات صدر بحقها عدم موافقة من اللجنة';

        if (filtered.length === 0) {
            select.innerHTML = '<option value="">-- لا توجد معاملات بانتظار هذا المسار --</option>';
            tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; color: #94a3b8; padding: 30px;"><i class="fa-solid fa-folder-open fs-2 mb-2 d-block"></i> لا توجد معاملات بانتظار الإصدار في هذا المسار حالياً.</td></tr>`;
            document.getElementById('form-application-id').value = '';
            document.getElementById('target-uni-name').value = 'لا يوجد مرشح محدد';
            currentSelectedCandidate = null;
            updateGenerateDraftButton();
            return;
        }

        filtered.forEach((app, index) => {
            // Populate select option
            const opt = document.createElement('option');
            opt.value = app.id;
            opt.innerText = `${app.name} (${app.appNo} - ${app.degree})`;
            select.appendChild(opt);

            // Populate Table Row
            const tr = document.createElement('tr');
            tr.onclick = (e) => {
                if (e.target.tagName !== 'BUTTON' && !e.target.closest('button') && !e.target.closest('a')) {
                    selectCandidateFromRow(app.id, tr);
                }
            };

            const isRejected = (activeTrack === 'rejected');
            const statusBadge = isRejected
                ? `<span class="badge" style="background: #fef2f2; color: #dc2626; font-weight: 700; padding: 3px 8px; border-radius: 6px; font-size: 0.74rem; border: 1px solid #fecaca;"><i class="fa-solid fa-circle-xmark"></i> عدم موافقة</span>`
                : `<span class="badge" style="background: #ecfdf5; color: #059669; font-weight: 700; padding: 3px 8px; border-radius: 6px; font-size: 0.74rem; border: 1px solid #a7f3d0;"><i class="fa-solid fa-check"></i> موافقة اللجنة</span>`;

            tr.innerHTML = `
                <td class="fw-bold" style="color: var(--imperial-navy);">${app.appNo}</td>
                <td>
                    <div class="cand-info">
                        <strong>${app.name}</strong>
                        <span>${app.natId}</span>
                    </div>
                </td>
                <td>${app.degree}</td>
                <td class="fw-bold" style="color: var(--imperial-navy);">${app.uni}</td>
                <td>${statusBadge}</td>
                <td style="text-align: center;">
                    <div class="d-inline-flex align-items-center justify-content-center gap-1 flex-wrap">
                        <a href="/admin/reports/${app.id}/generate-decision" target="_blank" class="btn-act-table text-decoration-none" style="background: ${isRejected ? '#fef2f2' : '#eff6ff'}; color: ${isRejected ? '#dc2626' : '#1d4ed8'}; border: 1px solid ${isRejected ? '#fecaca' : '#bfdbfe'};" title="فتح مسودة القرار للطباعة أو التصدير بصيغة Word">
                            <i class="fa-solid fa-file-word"></i> توليد / Word
                        </a>
                        <button type="button" class="btn-act-table btn-act-select" onclick="selectCandidateFromRow(${app.id}, this.closest('tr'))">
                            <i class="fa-solid fa-pen-to-square"></i> رصد القرار
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });

        // Select the first one by default or match pre-selected app_id
        const preId = '{{ request("app_id") }}';
        let targetApp = filtered.find(a => a.id == preId) || filtered[0];
        const targetIndex = filtered.indexOf(targetApp);
        const targetRow = tbody.querySelectorAll('tr')[targetIndex >= 0 ? targetIndex : 0];

        selectCandidateFromRow(targetApp.id, targetRow);
    }

    function updateGenerateDraftButton() {
        const btn = document.getElementById('btn-generate-selected-draft');
        const labelEl = document.getElementById('btn-generate-selected-label');
        if (!btn || !labelEl) return;

        if (!currentSelectedCandidate) {
            btn.classList.add('d-none');
            btn.classList.remove('d-flex');
            return;
        }

        const decNoInput = activeTrack === 'rejected'
            ? document.getElementById('rej-no')
            : document.getElementById('equiv-no');
        const decNo = decNoInput ? decNoInput.value.trim() : '';
        const query = decNo ? `?decision_no=${encodeURIComponent(decNo)}` : '';

        btn.href = `/admin/reports/${currentSelectedCandidate.id}/generate-decision${query}`;
        if (activeTrack === 'rejected') {
            labelEl.innerText = 'توليد / طباعة / Word كتاب عدم الموافقة';
            btn.style.background = '#fef2f2';
            btn.style.color = '#dc2626';
            btn.style.border = '1.5px solid #fca5a5';
        } else {
            labelEl.innerText = 'توليد / طباعة / Word مسودة القرار';
            btn.style.background = '#eff6ff';
            btn.style.color = '#1d4ed8';
            btn.style.border = '1.5px solid #93c5fd';
        }
        btn.classList.remove('d-none');
        btn.classList.add('d-flex');
    }

    // Row Selection
    function selectCandidateFromRow(candId, trElement) {
        document.querySelectorAll('#pending-table-body tr').forEach(r => r.classList.remove('selected'));
        if (trElement) trElement.classList.add('selected');

        document.getElementById('selected-candidate-id').value = candId;
        document.getElementById('form-application-id').value = candId;

        currentSelectedCandidate = applicationsData.find(a => a.id === candId);
        if (currentSelectedCandidate) {
            document.getElementById('target-uni-name').value = currentSelectedCandidate.uni;
            const eligNo = document.getElementById('elig-no');
            const eligDate = document.getElementById('elig-date');
            const rejReason = document.getElementById('rej-reason');
            if (eligNo) eligNo.value = currentSelectedCandidate.eligNo || '';
            if (eligDate) eligDate.value = currentSelectedCandidate.eligDate || '{{ date('Y-m-d') }}';
            if (rejReason) rejReason.value = currentSelectedCandidate.reason || '';
        }
        updateGenerateDraftButton();
    }

    function syncSelectedCandidate() {
        const candId = parseInt(document.getElementById('selected-candidate-id').value);
        if (candId) {
            const rows = document.querySelectorAll('#pending-table-body tr');
            const targetIndex = applicationsData.filter(a => a.track === activeTrack).findIndex(a => a.id === candId);
            const targetRow = targetIndex >= 0 ? rows[targetIndex] : null;
            selectCandidateFromRow(candId, targetRow);
        }
    }


    // Client Table Filter
    function filterPendingTable() {
        const query = document.getElementById('table-search').value.toLowerCase().trim();
        document.querySelectorAll('#pending-table-body tr').forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(query) ? '' : 'none';
        });
    }

    // Submit Validation & Feedback
    function validateBeforeSubmit(e) {
        const appId = document.getElementById('form-application-id').value;
        if (!appId || !currentSelectedCandidate) {
            alert('يرجى اختيار مرشح أولاً لرصد واعتماد القرار له.');
            return false;
        }

        if (activeTrack === 'rejected') {
            const file = document.getElementById('rej-file');
            if (!file.files || file.files.length === 0) {
                alert('يرجى إرفاق نسخة كتاب عدم الموافقة الموقع (PDF أو صورة).');
                return false;
            }
        } else {
            const file = document.getElementById('equiv-file');
            if (!file.files || file.files.length === 0) {
                alert('يرجى إرفاق نسخة قرار التعادل أو السماح الموقع (PDF أو صورة).');
                return false;
            }
        }

        return true;
    }

    // Initialize on load
    document.addEventListener('DOMContentLoaded', function() {
        switchTrack(activeTrack, null);
        ['rej-no', 'equiv-no'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('input', updateGenerateDraftButton);
        });
    });
</script>
@endpush
@endsection
