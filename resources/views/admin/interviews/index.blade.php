@extends('layouts.admin')

@section('title', 'إدارة وحجز مواعيد المقابلات الشفهية والعملية - مجلس التعليم العالي')

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
        --amber: #f59e0b;
        --amber-bg: #fffbeb;
        --bg-page: #f1f5f9;
        --surface-card: #ffffff;
        --border-subtle: #e2e8f0;
        --text-dark: #0f172a;
        --text-muted: #64748b;
    }

    /* Hero Header Card */
    .interviews-hero {
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

    .hero-info-side {
        display: flex;
        align-items: center;
        gap: 18px;
    }

    .hero-icon-box {
        width: 58px;
        height: 58px;
        border-radius: 14px;
        background: #eff6ff;
        border: 1.5px solid #bfdbfe;
        color: #1d4ed8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        box-shadow: 0 4px 12px rgba(29, 78, 216, 0.1);
        flex-shrink: 0;
    }

    .hero-titles h2 {
        font-size: 1.25rem;
        font-weight: 900;
        color: var(--imperial-navy);
        margin-bottom: 4px;
    }

    .hero-titles p {
        font-size: 0.86rem;
        color: var(--text-muted);
        margin-bottom: 0;
    }

    /* KPI Bento Grid */
    .kpi-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        margin-bottom: 22px;
    }

    .kpi-card {
        background: var(--surface-card);
        border-radius: 12px;
        padding: 18px 22px;
        border: 1.5px solid var(--border-subtle);
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        transition: all 0.2s ease;
    }

    .kpi-card:hover {
        transform: translateY(-2px);
        border-color: #cbd5e1;
        box-shadow: 0 6px 18px rgba(0,0,0,0.05);
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

    .kpi-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }

    /* DYNAMIC SCHEDULING ACTION BAR (SMART SCHEDULER) */
    .smart-scheduler-panel {
        background: linear-gradient(135deg, var(--imperial-navy) 0%, #152c50 100%);
        border-radius: 14px;
        padding: 20px 24px;
        color: white;
        box-shadow: 0 8px 25px rgba(14, 30, 56, 0.12);
        border: 1.5px solid rgba(197, 155, 39, 0.4);
        margin-bottom: 22px;
        position: relative;
        overflow: hidden;
    }

    .scheduler-top-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 14px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        padding-bottom: 10px;
    }

    .scheduler-title {
        font-size: 0.95rem;
        font-weight: 800;
        color: #ffffff;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .selected-pill-counter {
        background: rgba(197, 155, 39, 0.2);
        color: var(--heritage-gold-light);
        border: 1px solid var(--heritage-gold);
        padding: 4px 14px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 800;
    }

    .scheduler-inputs-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 1.6fr auto;
        gap: 14px;
        align-items: end;
    }

    @media (max-width: 980px) {
        .scheduler-inputs-grid {
            grid-template-columns: 1fr;
        }
    }

    .input-block label {
        display: block;
        font-size: 0.8rem;
        font-weight: 700;
        color: #cbd5e1;
        margin-bottom: 6px;
    }

    .input-block input, .input-block select {
        width: 100%;
        padding: 10px 14px;
        border-radius: 8px;
        border: 1px solid rgba(255,255,255,0.15);
        background: rgba(255,255,255,0.08);
        color: #ffffff;
        font-size: 0.88rem;
        outline: none;
        transition: all 0.2s;
    }

    .input-block input:focus, .input-block select:focus {
        background: rgba(255,255,255,0.16);
        border-color: var(--heritage-gold);
        box-shadow: 0 0 0 3px rgba(197, 155, 39, 0.2);
    }

    .input-block input::placeholder {
        color: #94a3b8;
    }

    .btn-confirm-schedule {
        background: linear-gradient(135deg, var(--heritage-gold) 0%, #aa841d 100%);
        color: #081224;
        font-weight: 800;
        font-size: 0.92rem;
        padding: 11px 22px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 14px var(--heritage-gold-glow);
        transition: all 0.2s;
        height: 42px;
        white-space: nowrap;
    }

    .btn-confirm-schedule:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(197, 155, 39, 0.4);
        color: #081224;
    }

    /* UNIFIED SEARCH & FILTER PANEL */
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

    /* ELEVATED CANDIDATES TABLE */
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

    table.modern-table tr.row-selected td {
        background: #f0f7ff;
    }

    .custom-checkbox {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: var(--imperial-navy);
    }

    /* Candidate Info Cluster */
    .cand-info-box {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .cand-avatar-mini {
        width: 38px;
        height: 38px;
        border-radius: 8px;
        background: #e0f2fe;
        color: #0284c7;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        font-weight: 800;
        flex-shrink: 0;
    }

    .cand-names strong {
        font-size: 0.95rem;
        color: var(--imperial-navy);
        display: block;
    }

    .cand-names span {
        font-size: 0.76rem;
        color: var(--text-muted);
        font-family: monospace;
    }

    /* Status Pills */
    .status-pill-pending {
        background: #fffbeb;
        color: #d97706;
        border: 1px solid #fde68a;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.78rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
    }

    .status-pill-scheduled {
        background: #f0f9ff;
        color: #0284c7;
        border: 1px solid #bae6fd;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.78rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
    }

    /* Row Action Triggers */
    .actions-cluster {
        display: flex;
        align-items: center;
        gap: 6px;
        justify-content: center;
    }

    .btn-eval {
        border: none;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 0.78rem;
        font-weight: 800;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all 0.15s ease;
        text-decoration: none;
    }

    .btn-pass {
        background: #ecfdf5;
        color: #059669;
        border: 1px solid #a7f3d0;
    }

    .btn-pass:hover {
        background: #059669;
        color: white;
    }

    .btn-fail {
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }

    .btn-fail:hover {
        background: #dc2626;
        color: white;
    }

    .btn-edit-sched {
        background: #f8fafc;
        color: #475569;
        border: 1px solid #cbd5e1;
        padding: 6px 10px;
    }

    .btn-edit-sched:hover {
        background: var(--imperial-navy);
        color: white;
        border-color: var(--imperial-navy);
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0" dir="rtl">

    <!-- 1. HERO HEADER CARD -->
    <section class="interviews-hero">
        <div class="hero-info-side">
            <div class="hero-icon-box">
                <i class="fa-solid fa-calendar-days"></i>
            </div>
            <div class="hero-titles">
                <h2>منظومة حجز وجدولة المقابلات الشفهية والعملية</h2>
                <p>تحديد مواعيد ولجان المقابلات للمرشحين بحالة (بانتظار المقابلة)، ورصد نتائج اجتياز الأهلية التدريسية.</p>
            </div>
        </div>
        <div>
            <span class="badge" style="background: rgba(14,30,56,0.06); color: var(--imperial-navy); border: 1px solid var(--border-subtle); padding: 8px 14px; border-radius: 30px; font-weight: 700; font-size: 0.82rem;">
                <i class="fa-solid fa-calendar-check text-warning me-1"></i> دورة مقابلات {{ date('Y') }}
            </span>
        </div>
    </section>

    <!-- 2. KPI METRICS -->
    <section class="kpi-row">
        <div class="kpi-card">
            <div class="kpi-data">
                <span>إجمالي بانتظار المقابلة</span>
                <strong style="color: #0284c7;">{{ $totalAwaitingCount }}</strong>
            </div>
            <div class="kpi-icon" style="background: #e0f2fe; color: #0284c7;">
                <i class="fa-solid fa-user-clock"></i>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-data">
                <span>تم تحديد الموعد لهم</span>
                <strong style="color: var(--amber);">{{ $scheduledCount }}</strong>
            </div>
            <div class="kpi-icon" style="background: var(--amber-bg); color: var(--amber);">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-data">
                <span>اجتازوا المقابلة (مؤهلون)</span>
                <strong style="color: var(--emerald);">{{ $passedCount ?? 0 }}</strong>
            </div>
            <div class="kpi-icon" style="background: var(--emerald-bg); color: var(--emerald);">
                <i class="fa-solid fa-user-check"></i>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-data">
                <span>لم يجتازوا المقابلة</span>
                <strong style="color: var(--rose);">{{ $failedCount ?? 0 }}</strong>
            </div>
            <div class="kpi-icon" style="background: var(--rose-bg); color: var(--rose);">
                <i class="fa-solid fa-user-xmark"></i>
            </div>
        </div>
    </section>

    <!-- 3. SMART DYNAMIC SCHEDULER PANEL -->
    <form action="{{ route('admin.interviews.batch_schedule') }}" method="POST" id="batchInterviewForm" onsubmit="return validateBatchSchedule()">
        @csrf
        <section class="smart-scheduler-panel">
            <div class="scheduler-top-meta">
                <div class="scheduler-title">
                    <i class="fa-solid fa-clock" style="color: var(--heritage-gold-light);"></i>
                    تحديد موعد وساعة المقابلة الموحدة للمرشحين المحددين
                </div>
                <span class="selected-pill-counter" id="selectedCounter">
                    <i class="fa-solid fa-check-double"></i> <span id="countNum">0</span> مرشح محدد
                </span>
            </div>

            <div class="scheduler-inputs-grid">
                <div class="input-block">
                    <label><i class="fa-regular fa-calendar"></i> تاريخ المقابلة :</label>
                    <input type="date" name="interview_date" value="{{ date('Y-m-d', strtotime('+3 days')) }}" required>
                </div>
                <div class="input-block">
                    <label><i class="fa-regular fa-clock"></i> توقيت المقابلة :</label>
                    <input type="text" name="interview_time" value="الساعة 10:00 صباحاً" required>
                </div>
                <div class="input-block">
                    <label><i class="fa-solid fa-location-dot"></i> مكان / ملاحظات القاعة :</label>
                    <input type="text" name="interview_notes" value="مبنى وزارة التعليم العالي والبحث العلمي - القاعة الرئيسية">
                </div>
                <div>
                    <button type="submit" class="btn-confirm-schedule">
                        <i class="fa-solid fa-floppy-disk"></i> تثبيت وحفظ الموعد
                    </button>
                </div>
            </div>
        </section>
    </form>

    <!-- 4. SEARCH & FILTER PANEL -->
    <section class="filter-panel">
        <div class="search-pill">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="candSearch" placeholder="ابحث باسم المرشح، الرقم الوطني، أو رقم المعاملة..." onkeyup="filterCandidates()">
        </div>
        <select class="select-filter-pill" id="uniFilter" onchange="filterCandidates()">
            <option value="">-- كافة الجامعات الطالبة --</option>
            @foreach($universities as $u)
                <option value="{{ $u->name }}" {{ $universityFilter == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
            @endforeach
        </select>
    </section>

    <!-- 5. CANDIDATES DATA GRID -->
    <section class="table-card-container">
        <div class="table-responsive">
            <table class="modern-table" id="candidatesTable">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">
                            <input type="checkbox" class="custom-checkbox" id="selectAllMaster" onchange="toggleSelectAll(this)">
                        </th>
                        <th>معرّف الطلب</th>
                        <th>المرشح والمعلومات</th>
                        <th>الجامعة والكلية</th>
                        <th>المؤهل الأكاديمي المطلوب</th>
                        <th>موعد المقابلة المعتمد</th>
                        <th style="text-align: center;">الإجراءات وقرار الأهلية</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($applications as $app)
                    @php
                        $lastEd = $app->educations->last();
                        $firstLetter = mb_substr($app->candidate->full_name ?? 'م', 0, 1, 'UTF-8');
                    @endphp
                    <tr id="row-{{ $app->id }}">
                        <td style="text-align: center;">
                            <input type="checkbox" name="application_ids[]" value="{{ $app->id }}" form="batchInterviewForm" class="custom-checkbox row-check" onchange="handleRowCheck(this)">
                        </td>
                        <td>
                            <strong style="font-family: monospace; color: #475569;">{{ $app->application_no }}</strong>
                            <span style="display: block; font-size: 0.72rem; color: #0284c7; font-weight: 700;">{{ $app->request_type ?? 'تعادل جديد' }}</span>
                        </td>
                        <td>
                            <div class="cand-info-box">
                                <div class="cand-avatar-mini">{{ $firstLetter }}</div>
                                <div class="cand-names">
                                    <strong>{{ $app->candidate->full_name ?? 'غ/م' }}</strong>
                                    <span>{{ $app->candidate->national_id ?? '---' }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <strong style="color: var(--imperial-navy);">{{ $app->workUniversity->name ?? 'غير محددة' }}</strong>
                            <span style="display: block; font-size: 0.76rem; color: var(--text-muted);">{{ $app->work_faculty ?? 'إدارة جامعة' }}</span>
                        </td>
                        <td>
                            <span style="font-weight: 700; color: #0284c7;"><i class="fa-solid fa-graduation-cap"></i> {{ $lastEd->level->name ?? 'إجازة' }}</span>
                            <span style="display: block; font-size: 0.76rem; color: var(--text-muted);">{{ $lastEd->exact_specialization ?? ($lastEd->general_specialization ?? 'تخصص عام') }}</span>
                        </td>
                        <td>
                            @if($app->interview_date)
                                <span class="status-pill-scheduled">
                                    <i class="fa-regular fa-calendar-check"></i> {{ format_sys_date($app->interview_date) }} - {{ $app->interview_time ?? '10:00 ص' }}
                                </span>
                            @else
                                <span class="status-pill-pending">
                                    <i class="fa-solid fa-hourglass-start"></i> لم يحدد الموعد بعد
                                </span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <div class="actions-cluster">
                                <!-- Pass button (PATCH form) -->
                                <form action="{{ route('admin.interviews.decide_outcome', $app->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من اعتماد اجتياز المرشح ({{ $app->candidate->full_name ?? '' }}) للمقابلة وتحويله إلى (بانتظار إصدار القرار)؟');">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="outcome" value="pass">
                                    <button type="submit" class="btn-eval btn-pass" title="اعتماد اجتياز المقابلة وثبوت الأهلية">
                                        <i class="fa-solid fa-circle-check"></i> موافقة (اجتياز)
                                    </button>
                                </form>

                                <!-- Fail button (opens modal) -->
                                <button type="button" class="btn-eval btn-fail" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $app->id }}" title="رصد عدم اجتياز المقابلة">
                                    <i class="fa-solid fa-circle-xmark"></i> رفض (لم يجتز)
                                </button>

                                <!-- Edit schedule button -->
                                <button type="button" class="btn-eval btn-edit-sched" data-bs-toggle="modal" data-bs-target="#singleScheduleModal{{ $app->id }}" title="تعديل الموعد">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-calendar-xmark fs-2 mb-2 d-block" style="color: #94a3b8;"></i>
                            لا يوجد مرشحين بانتظار المقابلة حالياً.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($applications->hasPages())
        <div class="p-3 border-top bg-light">
            {{ $applications->withQueryString()->links() }}
        </div>
        @endif
    </section>

</div>

<!-- INDIVIDUAL SCHEDULE MODALS -->
@foreach($applications as $app)
<div class="modal fade" id="singleScheduleModal{{ $app->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-top: 4px solid var(--heritage-gold) !important; border-radius: 12px;">
            <form action="{{ route('admin.interviews.batch_schedule') }}" method="POST">
                @csrf
                <input type="hidden" name="application_ids[]" value="{{ $app->id }}">
                
                <div class="modal-header bg-light">
                    <h6 class="modal-title fw-bold" style="color: var(--imperial-navy);">
                        <i class="fa-solid fa-user-clock me-1.5" style="color: var(--heritage-gold);"></i>
                        تحديد موعد المقابلة: {{ $app->candidate->full_name ?? '' }}
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body p-4 text-start">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">تاريخ المقابلة :</label>
                        <input type="date" name="interview_date" class="form-control fw-bold" value="{{ $app->interview_date ?? date('Y-m-d', strtotime('+3 days')) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">ساعة / وقت المقابلة :</label>
                        <input type="text" name="interview_time" class="form-control fw-bold" value="{{ $app->interview_time ?? 'الساعة 10:00 صباحاً' }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">المكان / الملاحظات :</label>
                        <input type="text" name="interview_notes" class="form-control" value="{{ $app->interview_notes ?? 'مبنى وزارة التعليم العالي والبحث العلمي - القاعة الرئيسية' }}">
                    </div>
                </div>
                
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary px-3" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn px-4 fw-bold" style="background: var(--imperial-navy); color: white;">حفظ الموعد</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- REJECTION CONFIRMATION MODAL -->
<div class="modal fade" id="rejectModal{{ $app->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-top: 4px solid #dc2626 !important; border-radius: 12px;">
            <form action="{{ route('admin.interviews.decide_outcome', $app->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="outcome" value="fail">
                
                <div class="modal-header bg-light">
                    <h6 class="modal-title fw-bold text-danger">
                        <i class="fa-solid fa-circle-xmark me-1.5"></i>
                        تأكيد عدم اجتياز المقابلة: {{ $app->candidate->full_name ?? '' }}
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body p-4 text-start">
                    <p class="small text-muted mb-3">سيتم تغيير حالة الطلب رسمياً إلى <strong class="text-danger">(مرفوض)</strong> بناءً على نتيجة المقابلة الشفهية والعملية.</p>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">ملاحظات / سبب عدم الاجتياز (اختياري) :</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="أدخل أسباب عدم اجتياز المقابلة..."></textarea>
                    </div>
                </div>
                
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary px-3" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-danger px-4 fw-bold">تأكيد الرفض</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<script>
    function handleRowCheck(box) {
        const row = box.closest('tr');
        if (box.checked) {
            row.classList.add('row-selected');
        } else {
            row.classList.remove('row-selected');
        }
        updateCounter();
    }

    function toggleSelectAll(masterBox) {
        const checks = document.querySelectorAll('.row-check');
        checks.forEach(chk => {
            chk.checked = masterBox.checked;
            handleRowCheck(chk);
        });
    }

    function updateCounter() {
        const count = document.querySelectorAll('.row-check:checked').length;
        const countEl = document.getElementById('countNum');
        if (countEl) countEl.innerText = count;
    }

    function validateBatchSchedule() {
        const count = document.querySelectorAll('.row-check:checked').length;
        if (count === 0) {
            alert('يرجى تحديد مرشح واحد على الأقل من الجدول أولاً.');
            return false;
        }
        return true;
    }

    function filterCandidates() {
        const query = (document.getElementById('candSearch')?.value || '').toLowerCase().trim();
        const uniFilter = (document.getElementById('uniFilter')?.value || '').toLowerCase().trim();
        const rows = document.querySelectorAll('#candidatesTable tbody tr');

        rows.forEach(r => {
            const text = r.innerText.toLowerCase();
            const matchesQuery = !query || text.includes(query);
            const matchesUni = !uniFilter || text.includes(uniFilter);
            r.style.display = (matchesQuery && matchesUni) ? '' : 'none';
        });
    }
</script>
@endsection
