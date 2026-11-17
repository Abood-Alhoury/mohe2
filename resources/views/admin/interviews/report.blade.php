@extends('layouts.admin')

@section('title', 'تقرير وجدول المقابلات الرسمي - مجلس التعليم العالي')

@push('styles')
<style>
@import url('https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Noto+Naskh+Arabic:wght@400;500;600;700&display=swap');

.report-toolbar-card {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-top: 4px solid var(--heritage-gold, #c59b27);
    border-radius: 12px;
    box-shadow: 0 6px 22px rgba(14, 30, 56, 0.06);
    max-width: 210mm;
    width: 100%;
}

.report-toolbar-header {
    background: linear-gradient(135deg, #0e1e38 0%, #162e54 100%);
    color: #ffffff;
    padding: 14px 20px;
    border-radius: 8px 8px 0 0;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.report-candidate-list {
    max-height: 240px;
    overflow-y: auto;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: #f8fafc;
}

.report-candidate-item {
    display: grid;
    grid-template-columns: 32px 1.4fr 1.4fr 90px 1.2fr 105px 68px;
    gap: 8px;
    align-items: center;
    padding: 8px 12px;
    border-bottom: 1px solid #e2e8f0;
    font-size: 0.82rem;
    background: #ffffff;
}

.report-candidate-item:last-child {
    border-bottom: none;
}

.report-candidate-item:hover {
    background: #f0f7ff;
}

@media (max-width: 900px) {
    .report-candidate-item {
        grid-template-columns: 28px 1fr;
    }
}

/* ============================================================
   الورقة الرسمية للطباعة (مطابقة لتصميم النموذج المرفق A4)
   ============================================================ */
#interview-report-sheet {
    direction: rtl;
    background: #ffffff;
    color: #000000;
    width: 210mm;
    min-height: 297mm;
    padding: 12mm 15mm 14mm 15mm;
    margin: 0 auto 30px auto;
    box-shadow: 0 10px 35px rgba(0, 0, 0, 0.12);
    border: 1px solid #cbd5e1;
    box-sizing: border-box;
    font-family: 'Noto Naskh Arabic', 'Amiri', 'Traditional Arabic', 'Simplified Arabic', serif !important;
}

#interview-report-sheet * {
    font-family: 'Noto Naskh Arabic', 'Amiri', 'Traditional Arabic', 'Simplified Arabic', serif !important;
    color: #000000;
}

#interview-report-sheet [dir="ltr"],
#interview-report-sheet [dir="ltr"] * {
    font-family: 'Times New Roman', Georgia, serif !important;
}

.ir-header-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 14px;
}

.ir-header-table td {
    border: none !important;
    padding: 0;
    vertical-align: top;
}

.ir-hdr-right {
    width: 39%;
    text-align: right;
    font-size: 14.5px;
    font-weight: 700;
    line-height: 1.55;
}

.ir-hdr-logo {
    width: 22%;
    text-align: center;
    vertical-align: middle !important;
}

.ir-hdr-logo img {
    width: 112px;
    max-height: 76px;
    object-fit: contain;
    opacity: 0.95;
    filter: grayscale(100%) brightness(0.86) contrast(1.35);
}

.ir-hdr-left {
    width: 39%;
    text-align: center;
    vertical-align: top;
    font-size: 14px;
    font-weight: 700;
    line-height: 1.5;
    padding-top: 4px !important;
}

.ir-meta-block {
    margin-top: 10px;
    font-size: 14px;
    font-weight: 700;
    line-height: 1.65;
}

.ir-addressee {
    text-align: center;
    font-size: 16px;
    font-weight: 700;
    margin: 12px 0 12px 0;
}

.ir-preamble {
    font-size: 14.5px;
    line-height: 1.72;
    text-align: right;
    margin-bottom: 10px;
}

.ir-preamble-line {
    margin-bottom: 3px;
}

.ir-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
    margin-top: 6px;
}

.ir-table th,
.ir-table td {
    border: 1px solid #000000;
    padding: 4px 6px;
    font-size: 13.5px;
    line-height: 1.35;
    vertical-align: middle;
    word-wrap: break-word;
}

.ir-table thead th {
    font-weight: 700;
    text-align: center;
    font-size: 14px;
    padding: 6px 5px;
}

.ir-table tr.ir-group-row td {
    text-align: center;
    font-weight: 700;
    font-size: 14.5px;
    padding: 5px;
}

.ir-table tbody td {
    height: 31px;
}

.ir-col-no { width: 7%; text-align: center; }
.ir-col-name { width: 23%; text-align: right; }
.ir-col-spec { width: 25%; text-align: right; }
.ir-col-deg { width: 12%; text-align: center; }
.ir-col-uni { width: 21%; text-align: center; }
.ir-col-res { width: 12%; text-align: center; }

#interview-report-sheet [contenteditable="true"] {
    outline: none;
    border-radius: 2px;
    transition: background-color 0.15s ease;
}

#interview-report-sheet [contenteditable="true"]:hover {
    background-color: #f8fafc;
    outline: 1px dashed #94a3b8;
}

#interview-report-sheet [contenteditable="true"]:focus {
    background-color: #fffef5;
    outline: 1.5px solid #0e1e38;
}

.editable-blank {
    display: inline-block;
    min-width: 85px;
    min-height: 18px;
    border-bottom: 1px dashed #cbd5e1;
    padding: 0 4px;
}

@media print {
    @page {
        size: A4 portrait;
        margin: 10mm 13mm 10mm 13mm;
    }

    html, body {
        background: #ffffff !important;
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        height: auto !important;
    }

    main, .container-fluid, .d-flex.flex-column {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        display: block !important;
    }

    .sidebar-container,
    .mohe-header,
    header,
    aside,
    footer,
    nav,
    .no-print,
    .btn,
    button,
    .alert {
        display: none !important;
        visibility: hidden !important;
        height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    #interview-report-sheet {
        width: 100% !important;
        max-width: 100% !important;
        min-height: auto !important;
        height: auto !important;
        margin: 0 !important;
        padding: 0 !important;
        border: none !important;
        box-shadow: none !important;
    }

    #interview-report-sheet [contenteditable="true"],
    #interview-report-sheet [contenteditable="true"]:hover,
    #interview-report-sheet [contenteditable="true"]:focus,
    .editable-blank {
        outline: none !important;
        border-bottom: none !important;
        background: transparent !important;
    }

    .ir-table thead {
        display: table-header-group;
    }

    .ir-table tr {
        page-break-inside: avoid;
        break-inside: avoid;
    }
}
</style>
@endpush

@section('content')
<div class="container-fluid px-3 py-2" dir="rtl">
    <div class="d-flex flex-column align-items-center w-100">

        {{-- ============================================================
             لوحة التحكم وإعداد بيانات التقرير والمرشحين (لا تُطبع)
             ============================================================ --}}
        <div class="report-toolbar-card mb-4 no-print">
            <div class="report-toolbar-header">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <a href="{{ route('admin.interviews.index') }}" class="btn btn-sm btn-outline-light fw-bold px-3">
                        <i class="fa-solid fa-arrow-right me-1"></i> عودة للمقابلات
                    </a>
                    <span class="fw-bold fs-6">
                        <i class="fa-solid fa-clipboard-list me-1" style="color: #f5d77f;"></i>
                        إعداد وطباعة تقرير المقابلات الرسمي (A4)
                    </span>
                </div>

                <div>
                    <button type="button" onclick="window.print()" class="btn btn-warning fw-bold px-4 shadow-sm text-dark">
                        <i class="fa-solid fa-print me-1"></i> طباعة التقرير
                    </button>
                </div>
            </div>

            <div class="p-3">
                {{-- 1. متغيرات الترويسة وموعد ومكان المقابلة --}}
                <div class="row g-2 mb-3">
                    <div class="col-md-2 col-6">
                        <label class="form-label small fw-bold text-dark mb-1">الرقم (أعلى الورقة):</label>
                        <input type="text" id="ctrl-doc-no" class="form-control form-control-sm" value="" placeholder="فارغ افتراضياً">
                    </div>
                    <div class="col-md-2 col-6">
                        <label class="form-label small fw-bold text-dark mb-1">التاريخ (أعلى الورقة):</label>
                        <input type="text" id="ctrl-doc-date" class="form-control form-control-sm" value="" placeholder="فارغ افتراضياً">
                    </div>
                    <div class="col-md-2 col-6">
                        <label class="form-label small fw-bold text-dark mb-1">تاريخ المقابلة:</label>
                        <input type="date" id="ctrl-interview-date" class="form-control form-control-sm fw-bold" value="{{ $initialDate }}">
                    </div>
                    <div class="col-md-2 col-6">
                        <label class="form-label small fw-bold text-dark mb-1">اليوم:</label>
                        <input type="text" id="ctrl-interview-day" class="form-control form-control-sm fw-bold" value="">
                    </div>
                    <div class="col-md-2 col-6">
                        <label class="form-label small fw-bold text-dark mb-1">الساعة:</label>
                        <input type="text" id="ctrl-interview-hour" class="form-control form-control-sm fw-bold" value="{{ $initialHour }}" placeholder="12">
                    </div>
                    <div class="col-md-2 col-6">
                        <label class="form-label small fw-bold text-dark mb-1">الفترة:</label>
                        <select id="ctrl-interview-period" class="form-select form-select-sm fw-bold">
                            <option value="صباحاً" {{ $initialPeriod === 'صباحاً' ? 'selected' : '' }}>صباحاً</option>
                            <option value="ظهراً" {{ $initialPeriod === 'ظهراً' ? 'selected' : '' }}>ظهراً</option>
                            <option value="مساءً" {{ $initialPeriod === 'مساءً' ? 'selected' : '' }}>مساءً</option>
                            <option value="">(بدون)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-dark mb-1">مكان المقابلة:</label>
                        <input type="text" id="ctrl-interview-place" class="form-control form-control-sm fw-bold" value="{{ $initialPlace }}">
                    </div>
                    <div class="col-md-6 d-flex align-items-end gap-3 flex-wrap pb-1">
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" id="ctrl-arabic-digits" checked>
                            <label class="form-check-label small fw-bold text-dark" for="ctrl-arabic-digits">
                                أرقام عربية مشرقية (١، ٢، ٣)
                            </label>
                        </div>
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" id="ctrl-pad-rows" checked>
                            <label class="form-check-label small fw-bold text-dark" for="ctrl-pad-rows">
                                تكميل الصفوف الفارغة حتى ١٦ صفاً
                            </label>
                        </div>
                    </div>
                </div>

                {{-- 2. اختيار وترتيب وإضافة المرشحين داخل التقرير --}}
                <div class="border-top pt-3">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                        <div class="small fw-bold" style="color: #0e1e38;">
                            <i class="fa-solid fa-users text-warning me-1"></i>
                            المرشحون المدرجون في التقرير (<span id="ctrl-active-count">0</span> مرشح)
                        </div>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <select id="ctrl-filter-uni" class="form-select form-select-sm" style="width: auto; min-width: 150px;">
                                <option value="">كل الجامعات</option>
                                @foreach($universities as $uni)
                                    <option value="{{ $uni }}">{{ $uni }}</option>
                                @endforeach
                            </select>
                            <select id="ctrl-filter-degree" class="form-select form-select-sm" style="width: auto;">
                                <option value="">كل الدرجات</option>
                                <option value="doctorate">دكتوراه</option>
                                <option value="masters">ماجستير</option>
                            </select>
                            <select id="ctrl-filter-date" class="form-select form-select-sm" style="width: auto;">
                                <option value="">كل المواعيد</option>
                                @foreach($scheduledDates as $dt)
                                    <option value="{{ $dt }}">{{ $dt }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-sm btn-outline-primary fw-bold" onclick="selectVisibleCandidates(true)">تحديد الظاهر</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary fw-bold" onclick="selectVisibleCandidates(false)">إلغاء الكل</button>
                            <button type="button" class="btn btn-sm btn-success fw-bold" onclick="toggleManualForm()">
                                <i class="fa-solid fa-plus me-1"></i> إضافة مرشح يدوياً
                            </button>
                        </div>
                    </div>

                    {{-- نموذج إضافة صف يدوي --}}
                    <div id="manual-candidate-form" class="p-2 mb-2 bg-light border rounded" style="display: none;">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold mb-0">الاسم</label>
                                <input type="text" id="manual-name" class="form-control form-control-sm" placeholder="اسم المرشح الكامل">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold mb-0">الاختصاص</label>
                                <input type="text" id="manual-spec" class="form-control form-control-sm" placeholder="الاختصاص">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold mb-0">الدرجة</label>
                                <select id="manual-degree" class="form-select form-select-sm">
                                    <option value="دكتوراه">دكتوراه</option>
                                    <option value="ماجستير">ماجستير</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold mb-0">الجامعة الخاصة</label>
                                <input type="text" id="manual-uni" class="form-control form-control-sm" placeholder="اسم الجامعة الخاصة">
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-sm btn-primary w-100 fw-bold" onclick="addManualCandidate()">إضافة</button>
                            </div>
                        </div>
                    </div>

                    <div id="ctrl-candidate-list" class="report-candidate-list"></div>
                    <div class="small text-muted mt-1">
                        <i class="fa-solid fa-circle-info me-1 text-primary"></i>
                        يمكنك النقر مباشرة على أي خانة أو خلية داخل ورقة التقرير أدناه لتعديل النص أو تعديل خانة الرقم والتاريخ قبل الطباعة.
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================
             ورقة التقرير الرسمية القابلة للطباعة (A4)
             ============================================================ --}}
        <div id="interview-report-sheet">
            <table class="ir-header-table">
                <tr>
                    <td class="ir-hdr-right">
                        <div>الجمهورية العربية السورية</div>
                        <div>مجلس التعليم العالي</div>
                        <div>لجنة التأهيل ومعادلة الدرجات العلمية</div>
                        <div class="ir-meta-block">
                            <div>الرقم: <span id="paper-doc-no" class="editable-blank" contenteditable="true"></span></div>
                            <div>التاريخ: <span id="paper-doc-date" class="editable-blank" contenteditable="true"></span></div>
                        </div>
                    </td>
                    <td class="ir-hdr-logo">
                        <img src="{{ asset('assets/report_logo.png') }}" alt="شعار الجمهورية العربية السورية">
                    </td>
                    <td class="ir-hdr-left" dir="ltr">
                        <div>Syrian Arab Republic</div>
                        <div>council of Higher Education</div>
                    </td>
                </tr>
            </table>

            <div class="ir-addressee" contenteditable="true">
                السادة رؤساء الجامعات الخاصة
            </div>

            <div class="ir-preamble">
                <div class="ir-preamble-line">
                    إشارة الى قرار مجلس التعليم العالي رقم /<span id="paper-ref-no" contenteditable="true">١٧٥</span>/ تاريخ <span id="paper-ref-date" contenteditable="true">٢٠٢٢/٦/١٦</span>، المتضمن تكليف المرشحين للتدريس بالجامعات الخاصة بإجراء مقابلة
                </div>
                <div class="ir-preamble-line">
                    يحدد موعد إجراء المقابلة عند الساعة / <span id="paper-hour" contenteditable="true">١٢</span> / <span id="paper-period" contenteditable="true">صباحاً</span> من يوم <span id="paper-day" contenteditable="true">الخميس</span> الواقع في <span id="paper-date" contenteditable="true">٢٠٢٦/١٠/٢٩</span>
                </div>
                <div class="ir-preamble-line">
                    يرجى تبليغ مرشحين جامعاتكم لحضور المقابلة في الموعد المحدد في <span id="paper-place" contenteditable="true">{{ $initialPlace }}</span> وهي:
                </div>
            </div>

            <table class="ir-table" id="paper-candidates-table">
                <thead>
                    <tr>
                        <th class="ir-col-no">الرقم</th>
                        <th class="ir-col-name">الاسم</th>
                        <th class="ir-col-spec">الاختصاص</th>
                        <th class="ir-col-deg">الدرجة</th>
                        <th class="ir-col-uni">الجامعة الخاصة</th>
                        <th class="ir-col-res">النتيجة</th>
                    </tr>
                </thead>
                <tbody id="paper-table-body">
                    {{-- يتم بناؤه تلقائياً بواسطة JS حسب المرشحين المحددين --}}
                </tbody>
            </table>
        </div>

    </div>
</div>

<script>
(function() {
    const dbCandidates = @json($candidatesData);
    const initialSelectedIds = new Set(@json($selectedIds).map(Number));
    const arabicWeekdays = ['الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];
    const arabicDigitsMap = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];

    let refNoState = '175';
    let refDateState = '2022/6/16';

    let reportRows = dbCandidates.map(c => ({
        uid: 'db_' + c.id,
        id: Number(c.id),
        isManual: false,
        checked: initialSelectedIds.has(Number(c.id)),
        name: c.name || '',
        specialization: c.specialization || '',
        degree: c.degree || 'دكتوراه',
        group: c.group || 'doctorate',
        university: c.university || '',
        result: '',
        interview_date: c.interview_date || ''
    }));

    function toDisplayDigits(val) {
        const str = String(val ?? '');
        const useArabic = document.getElementById('ctrl-arabic-digits')?.checked;
        if (!useArabic) {
            return str.replace(/[٠-٩]/g, d => String('٠١٢٣٤٥٦٧٨٩'.indexOf(d)));
        }
        return str.replace(/[0-9]/g, d => arabicDigitsMap[Number(d)]);
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text ?? '';
        return div.innerHTML;
    }

    function computeArabicDayFromDateStr(isoDate) {
        if (!isoDate) return '';
        const parts = isoDate.split('-').map(Number);
        if (parts.length !== 3 || parts.some(isNaN)) return '';
        const dt = new Date(parts[0], parts[1] - 1, parts[2]);
        return arabicWeekdays[dt.getDay()] || '';
    }

    function formatIsoToSlashDate(isoDate) {
        if (!isoDate) return '';
        const parts = isoDate.split('-');
        if (parts.length !== 3) return isoDate;
        return `${parts[0]}/${parts[1]}/${parts[2]}`;
    }

    function syncHeaderAndMetaToPaper() {
        const docNo = document.getElementById('ctrl-doc-no')?.value ?? '';
        const docDate = document.getElementById('ctrl-doc-date')?.value ?? '';
        const hour = document.getElementById('ctrl-interview-hour')?.value ?? '12';
        const period = document.getElementById('ctrl-interview-period')?.value ?? 'صباحاً';
        const day = document.getElementById('ctrl-interview-day')?.value ?? '';
        const isoDate = document.getElementById('ctrl-interview-date')?.value ?? '';
        const place = document.getElementById('ctrl-interview-place')?.value ?? '';

        const paperDocNo = document.getElementById('paper-doc-no');
        const paperDocDate = document.getElementById('paper-doc-date');
        if (paperDocNo && document.activeElement !== paperDocNo) {
            paperDocNo.textContent = docNo ? toDisplayDigits(docNo) : '';
        }
        if (paperDocDate && document.activeElement !== paperDocDate) {
            paperDocDate.textContent = docDate ? toDisplayDigits(docDate) : '';
        }

        const paperRefNo = document.getElementById('paper-ref-no');
        const paperRefDate = document.getElementById('paper-ref-date');
        const paperHour = document.getElementById('paper-hour');
        const paperPeriod = document.getElementById('paper-period');
        const paperDay = document.getElementById('paper-day');
        const paperDate = document.getElementById('paper-date');
        const paperPlace = document.getElementById('paper-place');

        if (paperRefNo && document.activeElement !== paperRefNo) paperRefNo.textContent = toDisplayDigits(refNoState);
        if (paperRefDate && document.activeElement !== paperRefDate) paperRefDate.textContent = toDisplayDigits(refDateState);
        if (paperHour && document.activeElement !== paperHour) paperHour.textContent = toDisplayDigits(hour);
        if (paperPeriod && document.activeElement !== paperPeriod) paperPeriod.textContent = period;
        if (paperDay && document.activeElement !== paperDay) paperDay.textContent = day;
        if (paperDate && document.activeElement !== paperDate) paperDate.textContent = toDisplayDigits(formatIsoToSlashDate(isoDate));
        if (paperPlace && document.activeElement !== paperPlace) paperPlace.textContent = toDisplayDigits(place);
    }

    function isRowVisibleByFilter(row) {
        const uniFilter = document.getElementById('ctrl-filter-uni')?.value || '';
        const degFilter = document.getElementById('ctrl-filter-degree')?.value || '';
        const dateFilter = document.getElementById('ctrl-filter-date')?.value || '';

        if (uniFilter && row.university !== uniFilter) return false;
        if (degFilter && row.group !== degFilter) return false;
        if (dateFilter && !row.isManual && row.interview_date !== dateFilter) return false;
        return true;
    }

    function renderControlList() {
        const listEl = document.getElementById('ctrl-candidate-list');
        if (!listEl) return;

        const visibleRows = reportRows.filter(isRowVisibleByFilter);
        if (visibleRows.length === 0) {
            listEl.innerHTML = '<div class="p-3 text-center text-muted small">لا يوجد مرشحون بحالة (بانتظار المقابلة) ضمن هذه التصفية. يمكنك استخدام زر <strong>«إضافة مرشح يدوياً»</strong> أعلاه أو تعديل الجدول مباشرة.</div>';
        } else {
            listEl.innerHTML = visibleRows.map((row) => {
                const idx = reportRows.findIndex(r => r.uid === row.uid);
                return `
                    <div class="report-candidate-item">
                        <div class="text-center">
                            <input type="checkbox" class="form-check-input" ${row.checked ? 'checked' : ''} onchange="window._toggleReportRow('${row.uid}', this.checked)">
                        </div>
                        <div class="fw-bold text-dark">${escapeHtml(row.name)}</div>
                        <div class="text-muted">${escapeHtml(row.specialization)}</div>
                        <div class="text-center"><span class="badge ${row.group === 'doctorate' ? 'bg-primary-subtle text-primary border border-primary' : 'bg-info-subtle text-dark border border-info'}">${escapeHtml(row.degree)}</span></div>
                        <div>${escapeHtml(row.university)}</div>
                        <div class="text-muted small text-center">${row.interview_date ? escapeHtml(row.interview_date) : 'غير محدد'}</div>
                        <div class="d-flex justify-content-end gap-1">
                            <button type="button" class="btn btn-xs btn-outline-secondary px-1 py-0" onclick="window._moveReportRow(${idx}, -1)" title="تحريك للأعلى"><i class="fa-solid fa-arrow-up"></i></button>
                            <button type="button" class="btn btn-xs btn-outline-secondary px-1 py-0" onclick="window._moveReportRow(${idx}, 1)" title="تحريك للأسفل"><i class="fa-solid fa-arrow-down"></i></button>
                            ${row.isManual ? `<button type="button" class="btn btn-xs btn-outline-danger px-1 py-0" onclick="window._removeReportRow('${row.uid}')" title="حذف"><i class="fa-solid fa-trash"></i></button>` : ''}
                        </div>
                    </div>
                `;
            }).join('');
        }

        const activeCount = reportRows.filter(r => r.checked).length;
        const countEl = document.getElementById('ctrl-active-count');
        if (countEl) countEl.textContent = activeCount;
    }

    function createCandidateTr(serialNo, row) {
        return `
            <tr data-uid="${row ? escapeHtml(row.uid) : ''}">
                <td class="ir-col-no">${toDisplayDigits(serialNo)}</td>
                <td class="ir-col-name" contenteditable="true" data-field="name">${row ? escapeHtml(row.name) : ''}</td>
                <td class="ir-col-spec" contenteditable="true" data-field="specialization">${row ? escapeHtml(toDisplayDigits(row.specialization)) : ''}</td>
                <td class="ir-col-deg" contenteditable="true" data-field="degree">${row ? escapeHtml(row.degree) : ''}</td>
                <td class="ir-col-uni" contenteditable="true" data-field="university">${row ? escapeHtml(row.university) : ''}</td>
                <td class="ir-col-res" contenteditable="true" data-field="result">${row ? escapeHtml(row.result) : ''}</td>
            </tr>
        `;
    }

    function renderPaperTable() {
        const tbody = document.getElementById('paper-table-body');
        if (!tbody) return;

        const active = reportRows.filter(r => r.checked);
        const docRows = active.filter(r => r.group === 'doctorate');
        const maRows = active.filter(r => r.group !== 'doctorate');
        const padTo16 = !!document.getElementById('ctrl-pad-rows')?.checked;

        let html = '';
        let serial = 1;

        if (docRows.length > 0 && maRows.length > 0) {
            html += `<tr class="ir-group-row"><td colspan="6" contenteditable="true">دكتوراه</td></tr>`;
            docRows.forEach(r => { html += createCandidateTr(serial++, r); });

            html += `<tr class="ir-group-row"><td colspan="6" contenteditable="true">ماجستير</td></tr>`;
            maRows.forEach(r => { html += createCandidateTr(serial++, r); });
        } else if (maRows.length > 0) {
            html += `<tr class="ir-group-row"><td colspan="6" contenteditable="true">ماجستير</td></tr>`;
            maRows.forEach(r => { html += createCandidateTr(serial++, r); });
        } else {
            html += `<tr class="ir-group-row"><td colspan="6" contenteditable="true">دكتوراه</td></tr>`;
            docRows.forEach(r => { html += createCandidateTr(serial++, r); });
        }

        if (padTo16) {
            while (serial <= 16) {
                html += createCandidateTr(serial++, null);
            }
        }

        tbody.innerHTML = html;

        // حفظ أي تعديل مباشر يجريه المستخدم على خلايا الجدول بالورقة
        tbody.querySelectorAll('tr[data-uid]').forEach(tr => {
            const uid = tr.dataset.uid;
            if (!uid) return;
            const targetRow = reportRows.find(r => r.uid === uid);
            if (!targetRow) return;
            tr.querySelectorAll('[data-field]').forEach(td => {
                td.addEventListener('blur', function() {
                    const field = this.dataset.field;
                    if (field && Object.prototype.hasOwnProperty.call(targetRow, field)) {
                        targetRow[field] = this.textContent.trim();
                    }
                });
            });
        });
    }

    function renderAll() {
        syncHeaderAndMetaToPaper();
        renderControlList();
        renderPaperTable();
    }

    window._toggleReportRow = function(uid, isChecked) {
        const row = reportRows.find(r => r.uid === uid);
        if (row) {
            row.checked = !!isChecked;
            renderAll();
        }
    };

    window._moveReportRow = function(idx, delta) {
        const newIdx = idx + delta;
        if (idx < 0 || newIdx < 0 || newIdx >= reportRows.length) return;
        const tmp = reportRows[idx];
        reportRows[idx] = reportRows[newIdx];
        reportRows[newIdx] = tmp;
        renderAll();
    };

    window._removeReportRow = function(uid) {
        reportRows = reportRows.filter(r => r.uid !== uid);
        renderAll();
    };

    window.selectVisibleCandidates = function(checked) {
        reportRows.forEach(r => {
            if (isRowVisibleByFilter(r)) {
                r.checked = !!checked;
            }
        });
        renderAll();
    };

    window.toggleManualForm = function() {
        const form = document.getElementById('manual-candidate-form');
        if (form) {
            form.style.display = (form.style.display === 'none' || !form.style.display) ? 'block' : 'none';
        }
    };

    window.addManualCandidate = function() {
        const name = (document.getElementById('manual-name')?.value || '').trim();
        const spec = (document.getElementById('manual-spec')?.value || '').trim();
        const degree = document.getElementById('manual-degree')?.value || 'دكتوراه';
        const uni = (document.getElementById('manual-uni')?.value || '').trim();

        if (!name) {
            alert('يرجى إدخال اسم المرشح.');
            return;
        }

        reportRows.push({
            uid: 'manual_' + Date.now() + '_' + Math.floor(Math.random() * 1000),
            id: null,
            isManual: true,
            checked: true,
            name,
            specialization: spec || '---',
            degree,
            group: degree === 'دكتوراه' ? 'doctorate' : 'masters',
            university: uni || '---',
            result: '',
            interview_date: ''
        });

        document.getElementById('manual-name').value = '';
        document.getElementById('manual-spec').value = '';
        document.getElementById('manual-uni').value = '';
        renderAll();
    };

    document.addEventListener('DOMContentLoaded', () => {
        const dateInput = document.getElementById('ctrl-interview-date');
        const dayInput = document.getElementById('ctrl-interview-day');

        if (dateInput && dayInput && !dayInput.value.trim()) {
            dayInput.value = computeArabicDayFromDateStr(dateInput.value) || 'الخميس';
        }

        if (dateInput) {
            dateInput.addEventListener('change', () => {
                if (dayInput) {
                    const computed = computeArabicDayFromDateStr(dateInput.value);
                    if (computed) dayInput.value = computed;
                }
                syncHeaderAndMetaToPaper();
            });
        }

        ['ctrl-doc-no', 'ctrl-doc-date', 'ctrl-interview-day', 'ctrl-interview-hour', 'ctrl-interview-period', 'ctrl-interview-place'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('input', syncHeaderAndMetaToPaper);
                el.addEventListener('change', syncHeaderAndMetaToPaper);
            }
        });

        ['ctrl-arabic-digits', 'ctrl-pad-rows'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('change', renderAll);
            }
        });

        ['ctrl-filter-uni', 'ctrl-filter-degree', 'ctrl-filter-date'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('change', renderControlList);
            }
        });

        // ربط التعديلات المباشرة من الورقة إلى حقول لوحة التحكم
        [
            ['paper-doc-no', 'ctrl-doc-no'],
            ['paper-doc-date', 'ctrl-doc-date'],
            ['paper-hour', 'ctrl-interview-hour'],
            ['paper-day', 'ctrl-interview-day'],
            ['paper-place', 'ctrl-interview-place']
        ].forEach(([paperId, ctrlId]) => {
            const pEl = document.getElementById(paperId);
            const cEl = document.getElementById(ctrlId);
            if (pEl && cEl) {
                pEl.addEventListener('input', () => { cEl.value = pEl.textContent.trim(); });
            }
        });

        const paperRefNo = document.getElementById('paper-ref-no');
        if (paperRefNo) {
            paperRefNo.addEventListener('blur', () => { refNoState = paperRefNo.textContent.trim() || '175'; });
        }
        const paperRefDate = document.getElementById('paper-ref-date');
        if (paperRefDate) {
            paperRefDate.addEventListener('blur', () => { refDateState = paperRefDate.textContent.trim() || '2022/6/16'; });
        }

        renderAll();
    });
})();
</script>
@endsection
