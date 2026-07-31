@extends('layouts.admin')

@section('title', 'مذكرة العرض - ' . ($candidate->full_name ?? ''))

@push('styles')
<style>
    /* ============ PRINT CONTROLS ============ */
    .no-print { display: flex; }
    @media print {
        .no-print { display: none !important; }
        body { background: #fff !important; }
        .moz-wrapper { box-shadow: none !important; margin: 0 !important; }
    }

    /* ============ WRAPPER ============ */
    .moz-wrapper {
        direction: rtl;
        font-family: 'Tahoma', 'Arial', sans-serif;
        font-size: 13px;
        background: #fff;
        max-width: 900px;
        margin: 0 auto 30px;
        padding: 24px 32px;
        box-shadow: 0 2px 16px rgba(0,0,0,.13);
        border: 1px solid #ccc;
        color: #111;
    }

    /* ============ TOP HEADER ============ */
    .moz-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 6px;
    }
    .moz-header-center {
        text-align: center;
        flex: 1;
    }
    .moz-header-center .country-name {
        font-size: 13px;
        color: #555;
    }
    .moz-header-center .council-name {
        font-size: 15px;
        font-weight: bold;
        color: #0f392b;
    }
    .moz-header-logo {
        width: 80px;
        text-align: left;
    }
    .moz-header-logo img {
        width: 65px;
    }
    .moz-title {
        text-align: center;
        font-size: 17px;
        font-weight: bold;
        color: #222;
        margin: 8px 0 14px;
        border-bottom: 2px solid #0f392b;
        padding-bottom: 8px;
    }

    /* ============ SECTION HEADERS ============ */
    .moz-section-header {
        background: linear-gradient(to left, #0f392b, #1a5c43);
        color: #fff;
        font-weight: bold;
        font-size: 13px;
        padding: 5px 12px;
        margin: 14px 0 0 0;
        border-right: 5px solid #c9a84c;
    }
    .moz-section-header .fa-solid { margin-left: 6px; }

    /* ============ INFO TABLE (key-value rows) ============ */
    .moz-info-table {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
    }
    .moz-info-table td {
        padding: 4px 8px;
        font-size: 12.5px;
        vertical-align: top;
        border: 1px solid #ddd;
    }
    .moz-info-table td.lbl {
        background: #f0f4f2;
        font-weight: bold;
        white-space: nowrap;
        width: 160px;
        color: #0f392b;
    }
    .moz-info-table td.val {
        background: #fff;
        color: #111;
    }
    .moz-info-table td.lbl-wide {
        background: #f0f4f2;
        font-weight: bold;
        color: #0f392b;
        white-space: nowrap;
    }

    /* ============ CANDIDATE NAME ============ */
    .candidate-name-row {
        background: #eaf4ee;
        border: 1px solid #b5d8c5;
        padding: 6px 10px;
        font-size: 15px;
        font-weight: bold;
        color: #0a2e1e;
        text-align: center;
        margin: 4px 0;
    }

    /* ============ WORK INFO ROW ============ */
    .work-info-block {
        background: #f7f7f7;
        border: 1px solid #ccc;
        padding: 7px 12px;
        font-size: 12.5px;
        margin-top: 2px;
    }
    .work-info-block span.key { font-weight: bold; color: #0f392b; }
    .work-info-block span.val { color: #c00; font-weight: bold; }
    .work-info-block span.uni { color: #0055aa; font-weight: bold; }

    /* ============ DATA GRID TABLE ============ */
    .moz-data-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
        margin: 6px 0;
    }
    .moz-data-table th {
        background: #0f392b;
        color: #fff;
        padding: 5px 8px;
        text-align: center;
        font-size: 12px;
    }
    .moz-data-table td {
        border: 1px solid #bbb;
        padding: 4px 8px;
        text-align: center;
        background: #fff;
    }
    .moz-data-table tr:nth-child(even) td { background: #f5f5f5; }

    /* ============ DEGREE BLOCK ============ */
    .degree-block {
        border: 1px solid #c5d8ce;
        margin: 8px 0;
        background: #fff;
    }
    .degree-block-header {
        background: #1a5c43;
        color: #fff;
        font-weight: bold;
        font-size: 12.5px;
        padding: 4px 10px;
    }
    .degree-block-body {
        padding: 0;
    }

    /* ============ COURSES TABLE ============ */
    .courses-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }
    .courses-table th {
        background: #b8d4c8;
        color: #0a2e1e;
        padding: 4px 8px;
        text-align: center;
        border: 1px solid #ccc;
    }
    .courses-table td {
        border: 1px solid #ccc;
        padding: 4px 8px;
        text-align: center;
    }
    .courses-table tr:nth-child(even) td { background: #f5faf7; }

    /* ============ EXTRA INFO ============ */
    .extra-info-block {
        background: #fff8f0;
        border: 1px solid #e8c97a;
        padding: 8px 14px;
        margin-top: 10px;
        font-size: 12.5px;
    }
    .extra-info-block .key { font-weight: bold; color: #7a4f00; }
    .extra-info-block .val-yes { color: #0f6a2e; font-weight: bold; }
    .extra-info-block .val-no  { color: #b00; font-weight: bold; }

    /* ============ SUB-HEADER (مجموع الإقامة) ============ */
    .sub-header {
        background: #e8eeeb;
        color: #0a2e1e;
        font-weight: bold;
        font-size: 12px;
        padding: 3px 8px;
        border-bottom: 1px solid #b5cfbf;
    }

    /* ============ PRINT BUTTON BAR ============ */
    .print-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }
</style>
@endpush

@section('content')

{{-- ===== ACTION BAR (hidden on print) ===== --}}
<div class="print-bar no-print">
    <a href="{{ route('admin.applications.index') }}" class="btn btn-secondary fw-bold">
        <i class="fa-solid fa-arrow-right me-1"></i> العودة لجدول الطلبات
    </a>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-mohe-gold fw-bold">
            <i class="fa-solid fa-print me-1"></i> طباعة مذكرة العرض
        </button>
        <a href="{{ route('admin.reports.download_pdf', $application->id) }}" target="_blank" class="btn btn-danger fw-bold">
            <i class="fa-solid fa-file-pdf me-1"></i> تنزيل PDF
        </a>
        <a href="{{ route('admin.reports.consolidated', $application->id) }}" class="btn btn-success fw-bold">
            <i class="fa-solid fa-layer-group me-1"></i> عرض المرفقات المدمجة
        </a>
    </div>
</div>

{{-- ===== MOZHAKKARA DOCUMENT ===== --}}
<div class="moz-wrapper">

    {{-- ── HEADER ── --}}
    <div class="moz-header">
        <div style="width:80px; text-align:right; color:#888; font-size:11px;">
            {{ \Carbon\Carbon::now()->format('Y-m-d') }}
        </div>
        <div class="moz-header-center">
            <div class="country-name">الجمهورية العربية السورية</div>
            <div class="council-name">مجلس التعليم العالي</div>
        </div>
        <div class="moz-header-logo">
            {{-- Syrian Eagle Logo (SVG inline fallback) --}}
            <svg viewBox="0 0 80 80" width="65" xmlns="http://www.w3.org/2000/svg">
                <circle cx="40" cy="40" r="38" fill="#0f392b" opacity=".08"/>
                <text x="40" y="52" text-anchor="middle" font-size="36" fill="#0f392b">🦅</text>
            </svg>
        </div>
    </div>

    <div class="moz-title">(مذكرة العرض)</div>

    {{-- ══════════════════════════════════════════ --}}
    {{-- 1. البيانات الشخصية للمرشح                --}}
    {{-- ══════════════════════════════════════════ --}}
    <div class="moz-section-header">
        <i class="fa-solid fa-user"></i> البيانات الشخصية للمرشح :
    </div>

    {{-- نوع الطلب + ID --}}
    <table class="moz-info-table">
        <tr>
            <td class="lbl">نوع الطالب :</td>
            <td class="val" style="color:#b00; font-weight:bold;">{{ $application->request_type ?? 'تعادل' }}</td>
            <td class="lbl">ID :</td>
            <td class="val">{{ $candidate->id }}</td>
        </tr>
        <tr>
            <td colspan="4" style="padding:0; border:none;">
                <div class="candidate-name-row">
                    اسم المرشح : {{ $candidate->full_name }}
                </div>
            </td>
        </tr>
        <tr>
            <td class="lbl">رقم الوطني :</td>
            <td class="val">{{ $candidate->national_id }}</td>
            <td class="lbl">الجنسية :</td>
            <td class="val">{{ $candidate->is_syrian ? 'سورية' : 'غير سورية' }}</td>
        </tr>
        <tr>
            <td class="lbl">تاريخ الميلاد :</td>
            <td class="val">{{ $candidate->dob }}</td>
            <td class="lbl">الوظيفة :</td>
            <td class="val">{{ $candidate->job_title }}</td>
        </tr>
        <tr>
            <td class="lbl">رقم الهاتف :</td>
            <td class="val">{{ $candidate->phone }}</td>
            <td class="lbl">رقم الجوال :</td>
            <td class="val">{{ $candidate->mobile }}</td>
        </tr>
        <tr>
            <td class="lbl">البريد الإلكتروني :</td>
            <td class="val" colspan="3" style="color:#0055aa;">{{ $candidate->email }}</td>
        </tr>
        <tr>
            <td class="lbl">العنوان :</td>
            <td class="val" colspan="3">{{ $candidate->address }}</td>
        </tr>
    </table>

    {{-- المرشح للعمل في --}}
    <div class="work-info-block" style="margin-top:6px;">
        <span class="key">المرشح للعمل في قسم :</span>
        <span class="val"> {{ $application->work_department ?? '---' }}</span>
        &nbsp;&nbsp;
        <span class="key">في كلية :</span>
        <span class="val"> {{ $application->work_faculty ?? '---' }}</span>
        &nbsp;&nbsp;
        <span class="key">في جامعة :</span>
        <span class="uni"> {{ $application->workUniversity->name ?? '---' }}</span>
        &nbsp;
        <span style="color:#555; font-size:11.5px;">التي تطلب الجامعة تكليفه بتدريسها إلى قرار معادلة شهادته</span>
    </div>

    {{-- مضبوط للعمل في (نفس بيانات جامعة العمل) --}}
    @if($application->work_department || $application->work_faculty)
    <div class="work-info-block" style="margin-top:3px; background:#f0f4f2;">
        <span class="key">مضبوط للعمل في قسم :</span>
        <span class="val"> {{ $application->work_department ?? '---' }}</span>
        &nbsp;&nbsp;
        <span class="key">في كلية :</span>
        <span class="val"> {{ $application->work_faculty ?? '---' }}</span>
        &nbsp;&nbsp;
        <span class="key">في جامعة :</span>
        <span class="uni"> {{ $application->workUniversity->name ?? '---' }}</span>
        &nbsp;
        <span style="color:#555; font-size:11.5px;">التي تطلب الجامعة تكليفه بتدريسها إلى قرار معادلة شهادته</span>
    </div>
    @endif

    {{-- ══════════════════════════════════════════ --}}
    {{-- 2. المقررات التي يدرسها                   --}}
    {{-- ══════════════════════════════════════════ --}}
    <div class="moz-section-header" style="margin-top:14px;">
        <i class="fa-solid fa-book-bookmark"></i> المقررات التي يدرسها بموجب قرار لجنة التأهيل ومعادلة الدرجات العلمية :
    </div>
    <div style="font-size:11.5px; color:#555; padding:3px 8px; background:#fafafa; border:1px solid #ddd; border-top:none;">
        رقم : {{ $application->application_no ?? '---' }}
        &nbsp;&nbsp;&nbsp;
        تاريخ : {{ \Carbon\Carbon::now()->format('Y-m-d') }}
    </div>

    <table class="courses-table">
        <thead>
            <tr>
                <th>اسم المقرر</th>
                <th>القسم</th>
                <th>الكلية</th>
                <th>حالة المقرر</th>
            </tr>
        </thead>
        <tbody>
            @forelse($application->courses as $crs)
            <tr>
                <td style="font-weight:bold;">{{ $crs->course_name }}</td>
                <td>{{ $crs->department }}</td>
                <td>{{ $crs->faculty }}</td>
                <td><i class="fa-solid fa-square-check text-success"></i> {{ $crs->course_status }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="color:#c00; font-weight:bold; text-align:center; padding:8px;">
                    لا توجد مقررات تطلبها الجامعة
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ══════════════════════════════════════════ --}}
    {{-- 3. الشهادات التي يحملها المرشح            --}}
    {{-- ══════════════════════════════════════════ --}}
    <div class="moz-section-header" style="margin-top:14px;">
        <i class="fa-solid fa-award"></i> الشهادات التي يحملها المرشح :
    </div>

    {{-- 3.1 الشهادة الثانوية --}}
    <div class="degree-block">
        <div class="degree-block-header">الشهادة الثانوية :</div>
        <div class="degree-block-body">
            <table class="moz-info-table">
                <tr>
                    <td class="lbl">الدولة المانحة :</td>
                    <td class="val">{{ optional(optional($highSchoolEd)->country)->name ?? 'سوريا' }}</td>
                    <td class="lbl">القسم :</td>
                    <td class="val">{{ optional($highSchoolEd)->section_name ?? '---' }}</td>
                    <td class="lbl">تاريخ المنح :</td>
                    <td class="val">{{ optional($highSchoolEd)->grant_date ?? '---' }}</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- 3.2 شهادة الإجازة الجامعية --}}
    @if($bachelorEd)
    <div class="degree-block">
        <div class="degree-block-header">شهادة الإجازة الجامعية :</div>
        <div class="degree-block-body">
            <table class="moz-info-table">
                <tr>
                    <td class="lbl">الدولة المانحة :</td>
                    <td class="val">{{ optional($bachelorEd->country)->name ?? '---' }}</td>
                    <td class="lbl">الجهة المانحة :</td>
                    <td class="val" colspan="3">{{ optional($bachelorEd->university)->name ?? '---' }}
                        @if($bachelorEd->university) <span style="color:#888; font-size:11px;">(المركزية)</span> @endif
                    </td>
                </tr>
                <tr>
                    <td class="lbl">التخصص العام :</td>
                    <td class="val">{{ $bachelorEd->general_specialization ?? '---' }}</td>
                    <td class="lbl">التخصص الدقيق :</td>
                    <td class="val">{{ $bachelorEd->exact_specialization ?? '---' }}</td>
                    <td class="lbl">المرتبة :</td>
                    <td class="val">{{ $bachelorEd->rank ?? '---' }}</td>
                </tr>
                <tr>
                    <td class="lbl">تاريخ التسجيل :</td>
                    <td class="val">{{ $bachelorEd->registration_date ?? '---' }}</td>
                    <td class="lbl">تاريخ المنح :</td>
                    <td class="val">{{ $bachelorEd->grant_date ?? '---' }}</td>
                    <td class="lbl">قرار الإيفاد :</td>
                    <td class="val">{{ $bachelorEd->envoy_decision ?? '---' }}</td>
                </tr>
            </table>
        </div>
    </div>
    @endif

    {{-- 3.3 دبلوم دراسات عليا --}}
    @if($diplomaEd)
    <div class="degree-block">
        <div class="degree-block-header">شهادة دبلوم الدراسات العليا :</div>
        <div class="degree-block-body">
            <table class="moz-info-table">
                <tr>
                    <td class="lbl">الدولة المانحة :</td>
                    <td class="val">{{ optional($diplomaEd->country)->name ?? '---' }}</td>
                    <td class="lbl">الجهة المانحة :</td>
                    <td class="val" colspan="3">{{ optional($diplomaEd->university)->name ?? '---' }}
                        @if($diplomaEd->university) <span style="color:#888; font-size:11px;">(المركزية)</span> @endif
                    </td>
                </tr>
                <tr>
                    <td class="lbl">التخصص العام :</td>
                    <td class="val">{{ $diplomaEd->general_specialization ?? '---' }}</td>
                    <td class="lbl">التخصص الدقيق :</td>
                    <td class="val">{{ $diplomaEd->exact_specialization ?? '---' }}</td>
                    <td class="lbl">المرتبة :</td>
                    <td class="val">{{ $diplomaEd->rank ?? '---' }}</td>
                </tr>
                <tr>
                    <td class="lbl">تاريخ التسجيل :</td>
                    <td class="val">{{ $diplomaEd->registration_date ?? '---' }}</td>
                    <td class="lbl">تاريخ المنح :</td>
                    <td class="val">{{ $diplomaEd->grant_date ?? '---' }}</td>
                    <td class="lbl">اسم المشرف :</td>
                    <td class="val">{{ $diplomaEd->supervisor_name ?? '---' }}</td>
                </tr>
                <tr>
                    <td class="lbl">عنوان الأطروحة :</td>
                    <td class="val" colspan="5">{{ $diplomaEd->thesis_title ?? '---' }}</td>
                </tr>
            </table>
        </div>
    </div>
    @endif

    {{-- 3.4 شهادة ماجستير سورية --}}
    @if($masterEd && optional(optional($masterEd)->country)->name == 'سوريا')
    <div class="degree-block">
        <div class="degree-block-header">شهادة ماجستير سوريا :</div>
        <div class="degree-block-body">
            <table class="moz-info-table">
                <tr>
                    <td class="lbl">الدولة المانحة :</td>
                    <td class="val">{{ optional($masterEd->country)->name ?? '---' }}</td>
                    <td class="lbl">الجهة المانحة :</td>
                    <td class="val" colspan="3">{{ optional($masterEd->university)->name ?? '---' }}
                        @if($masterEd->university) <span style="color:#888; font-size:11px;">(المركزية)</span> @endif
                    </td>
                </tr>
                <tr>
                    <td class="lbl">التخصص العام :</td>
                    <td class="val">{{ $masterEd->general_specialization ?? '---' }}</td>
                    <td class="lbl">التخصص الدقيق :</td>
                    <td class="val">{{ $masterEd->exact_specialization ?? '---' }}</td>
                    <td class="lbl">المرتبة :</td>
                    <td class="val">{{ $masterEd->rank ?? '---' }}</td>
                </tr>
                <tr>
                    <td class="lbl">تاريخ التسجيل :</td>
                    <td class="val">{{ $masterEd->registration_date ?? '---' }}</td>
                    <td class="lbl">تاريخ المنح :</td>
                    <td class="val">{{ $masterEd->grant_date ?? '---' }}</td>
                    <td class="lbl">اسم المشرف :</td>
                    <td class="val">{{ $masterEd->supervisor_name ?? '---' }}</td>
                </tr>
                <tr>
                    <td class="lbl">قرار الإيفاد :</td>
                    <td class="val">{{ $masterEd->envoy_decision ?? '---' }}</td>
                    <td class="lbl">تاريخ قرار الإيفاد :</td>
                    <td class="val">{{ $masterEd->envoy_date ?? '---' }}</td>
                    <td class="lbl"></td>
                    <td class="val"></td>
                </tr>
                <tr>
                    <td class="lbl">عنوان الأطروحة :</td>
                    <td class="val" colspan="5">{{ $masterEd->thesis_title ?? '---' }}</td>
                </tr>
            </table>
            {{-- مجموع الإقامة ماجستير --}}
            @if($masterEd->residences && $masterEd->residences->count() > 0)
            <div class="sub-header" style="margin-top:4px;">مجموع الإقامة بمرحلة الماجستير :</div>
            <table class="moz-data-table">
                <thead>
                    <tr>
                        <th>تاريخ الدخول بلد الدراسة</th>
                        <th>مطار الدخول</th>
                        <th>تاريخ الخروج من بلد</th>
                        <th>مطار الخروج</th>
                        <th>رقم الصفحة</th>
                        <th>يوم</th>
                        <th>شهر</th>
                        <th>سنة</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($masterEd->residences as $res)
                    <tr>
                        <td>{{ $res->entry_date }}</td>
                        <td>{{ $res->entry_airport }}</td>
                        <td>{{ $res->exit_date }}</td>
                        <td>{{ $res->exit_airport }}</td>
                        <td>{{ $res->page_number }}</td>
                        <td>0</td><td>0</td>
                        <td>{{ \Carbon\Carbon::parse($res->exit_date)->diffInYears(\Carbon\Carbon::parse($res->entry_date)) ?: 1 }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>
    @endif

    {{-- 3.5 شهادة ماجستير غير سورية --}}
    @if($masterEd && optional(optional($masterEd)->country)->name != 'سوريا')
    <div class="degree-block">
        <div class="degree-block-header">شهادة ماجستير غير سورية :</div>
        <div class="degree-block-body">
            <table class="moz-info-table">
                <tr>
                    <td class="lbl">الدولة المانحة :</td>
                    <td class="val">{{ optional($masterEd->country)->name ?? '---' }}</td>
                    <td class="lbl">الجهة المانحة :</td>
                    <td class="val" colspan="3">{{ optional($masterEd->university)->name ?? '---' }}
                        @if($masterEd->university) <span style="color:#888; font-size:11px;">(المركزية)</span> @endif
                    </td>
                </tr>
                <tr>
                    <td class="lbl">التخصص العام :</td>
                    <td class="val">{{ $masterEd->general_specialization ?? '---' }}</td>
                    <td class="lbl">التخصص الدقيق :</td>
                    <td class="val">{{ $masterEd->exact_specialization ?? '---' }}</td>
                    <td class="lbl">المرتبة :</td>
                    <td class="val">{{ $masterEd->rank ?? '---' }}</td>
                </tr>
                <tr>
                    <td class="lbl">تاريخ التسجيل :</td>
                    <td class="val">{{ $masterEd->registration_date ?? '---' }}</td>
                    <td class="lbl">تاريخ المنح :</td>
                    <td class="val">{{ $masterEd->grant_date ?? '---' }}</td>
                    <td class="lbl">اسم المشرف :</td>
                    <td class="val">{{ $masterEd->supervisor_name ?? '---' }}</td>
                </tr>
                <tr>
                    <td class="lbl">قرار الإيفاد :</td>
                    <td class="val">{{ $masterEd->envoy_decision ?? '---' }}</td>
                    <td class="lbl">تاريخ قرار الإيفاد :</td>
                    <td class="val">{{ $masterEd->envoy_date ?? '---' }}</td>
                    <td class="lbl">تاريخ القيام :</td>
                    <td class="val">{{ $masterEd->defense_date ?? '---' }}</td>
                </tr>
                <tr>
                    <td class="lbl">عنوان الأطروحة :</td>
                    <td class="val" colspan="5">{{ $masterEd->thesis_title ?? '---' }}</td>
                </tr>
            </table>
            {{-- مجموع الإقامة ماجستير غير سوري --}}
            @if($masterEd->residences && $masterEd->residences->count() > 0)
            <div class="sub-header" style="margin-top:4px;">مجموع الإقامة بمرحلة الماجستير :</div>
            <table class="moz-data-table">
                <thead>
                    <tr>
                        <th>تاريخ الدخول بلد الدراسة</th>
                        <th>مطار الدخول</th>
                        <th>تاريخ الخروج من بلد</th>
                        <th>مطار الخروج</th>
                        <th>رقم الصفحة</th>
                        <th>يوم</th><th>شهر</th><th>سنة</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($masterEd->residences as $res)
                    <tr>
                        <td>{{ $res->entry_date }}</td>
                        <td>{{ $res->entry_airport }}</td>
                        <td>{{ $res->exit_date }}</td>
                        <td>{{ $res->exit_airport }}</td>
                        <td>{{ $res->page_number }}</td>
                        <td>0</td><td>0</td>
                        <td>{{ \Carbon\Carbon::parse($res->exit_date)->diffInYears(\Carbon\Carbon::parse($res->entry_date)) ?: 1 }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>
    @endif

    {{-- 3.6 دكتوراه سورية --}}
    @if($phdEd && optional(optional($phdEd)->country)->name == 'سوريا')
    <div class="degree-block">
        <div class="degree-block-header">شهادة دكتوراه سوريا :</div>
        <div class="degree-block-body">
            <table class="moz-info-table">
                <tr>
                    <td class="lbl">الدولة المانحة :</td>
                    <td class="val">{{ optional($phdEd->country)->name ?? '---' }}</td>
                    <td class="lbl">الجهة المانحة :</td>
                    <td class="val" colspan="3">{{ optional($phdEd->university)->name ?? '---' }}
                        @if($phdEd->university) <span style="color:#888; font-size:11px;">(المركزية)</span> @endif
                    </td>
                </tr>
                <tr>
                    <td class="lbl">التخصص العام :</td>
                    <td class="val">{{ $phdEd->general_specialization ?? '---' }}</td>
                    <td class="lbl">التخصص الدقيق :</td>
                    <td class="val">{{ $phdEd->exact_specialization ?? '---' }}</td>
                    <td class="lbl">المرتبة :</td>
                    <td class="val">{{ $phdEd->rank ?? '---' }}</td>
                </tr>
                <tr>
                    <td class="lbl">تاريخ التسجيل :</td>
                    <td class="val">{{ $phdEd->registration_date ?? '---' }}</td>
                    <td class="lbl">تاريخ المنح :</td>
                    <td class="val">{{ $phdEd->grant_date ?? '---' }}</td>
                    <td class="lbl">اسم المشرف :</td>
                    <td class="val">{{ $phdEd->supervisor_name ?? '---' }}</td>
                </tr>
                <tr>
                    <td class="lbl">عنوان الأطروحة :</td>
                    <td class="val" colspan="5">{{ $phdEd->thesis_title ?? '---' }}</td>
                </tr>
            </table>
            @if($phdEd->residences && $phdEd->residences->count() > 0)
            <div class="sub-header" style="margin-top:4px;">تاريخ دخول بلد الدراسة :</div>
            <table class="moz-data-table">
                <thead>
                    <tr>
                        <th>تاريخ الدخول بلد الدراسة</th>
                        <th>مطار الدخول</th>
                        <th>تاريخ الخروج من بلد</th>
                        <th>مطار الخروج</th>
                        <th>رقم الصفحة</th>
                        <th>يوم</th><th>شهر</th><th>سنة</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($phdEd->residences as $res)
                    <tr>
                        <td>{{ $res->entry_date }}</td>
                        <td>{{ $res->entry_airport }}</td>
                        <td>{{ $res->exit_date }}</td>
                        <td>{{ $res->exit_airport }}</td>
                        <td>{{ $res->page_number }}</td>
                        <td>0</td><td>0</td>
                        <td>{{ \Carbon\Carbon::parse($res->exit_date)->diffInYears(\Carbon\Carbon::parse($res->entry_date)) ?: 2 }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>
    @endif

    {{-- 3.7 دكتوراه غير سورية --}}
    @if($phdEd && optional(optional($phdEd)->country)->name != 'سوريا')
    <div class="degree-block">
        <div class="degree-block-header">شهادة دكتوراه غير سورية :</div>
        <div class="degree-block-body">
            <table class="moz-info-table">
                <tr>
                    <td class="lbl">الدولة المانحة :</td>
                    <td class="val">{{ optional($phdEd->country)->name ?? '---' }}</td>
                    <td class="lbl">الجهة المانحة :</td>
                    <td class="val" colspan="3">{{ optional($phdEd->university)->name ?? '---' }}
                        @if($phdEd->university) <span style="color:#888; font-size:11px;">(المركزية)</span> @endif
                    </td>
                </tr>
                <tr>
                    <td class="lbl">التخصص العام :</td>
                    <td class="val">{{ $phdEd->general_specialization ?? '---' }}</td>
                    <td class="lbl">التخصص الدقيق :</td>
                    <td class="val">{{ $phdEd->exact_specialization ?? '---' }}</td>
                    <td class="lbl">المرتبة :</td>
                    <td class="val">{{ $phdEd->rank ?? '---' }}</td>
                </tr>
                <tr>
                    <td class="lbl">تاريخ التسجيل :</td>
                    <td class="val">{{ $phdEd->registration_date ?? '---' }}</td>
                    <td class="lbl">تاريخ المناقشة :</td>
                    <td class="val">{{ $phdEd->defense_date ?? '---' }}</td>
                    <td class="lbl">تاريخ المنح :</td>
                    <td class="val">{{ $phdEd->grant_date ?? '---' }}</td>
                </tr>
                <tr>
                    <td class="lbl">اسم المشرف :</td>
                    <td class="val">{{ $phdEd->supervisor_name ?? '---' }}</td>
                    <td class="lbl">قرار الإيفاد :</td>
                    <td class="val">{{ $phdEd->envoy_decision ?? '---' }}</td>
                    <td class="lbl">تاريخ قرار الإيفاد :</td>
                    <td class="val">{{ $phdEd->envoy_date ?? '---' }}</td>
                </tr>
                <tr>
                    <td class="lbl">عنوان الأطروحة :</td>
                    <td class="val" colspan="5">{{ $phdEd->thesis_title ?? '---' }}</td>
                </tr>
                <tr>
                    <td class="lbl">معلومات أخرى :</td>
                    <td class="val" colspan="5">{{ $phdEd->notes ?? 'لا توجد' }}</td>
                </tr>
            </table>
            @if($phdEd->residences && $phdEd->residences->count() > 0)
            <div class="sub-header" style="margin-top:4px;">تاريخ دخول بلد الدراسة :</div>
            <table class="moz-data-table">
                <thead>
                    <tr>
                        <th>تاريخ الدخول بلد الدراسة</th>
                        <th>مطار الدخول</th>
                        <th>تاريخ الخروج من بلد</th>
                        <th>مطار الخروج</th>
                        <th>رقم الصفحة</th>
                        <th>يوم</th><th>شهر</th><th>سنة</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($phdEd->residences as $res)
                    <tr>
                        <td>{{ $res->entry_date }}</td>
                        <td>{{ $res->entry_airport }}</td>
                        <td>{{ $res->exit_date }}</td>
                        <td>{{ $res->exit_airport }}</td>
                        <td>{{ $res->page_number }}</td>
                        <td>0</td><td>0</td>
                        <td>{{ \Carbon\Carbon::parse($res->exit_date)->diffInYears(\Carbon\Carbon::parse($res->entry_date)) ?: 4 }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════ --}}
    {{-- 4. معلومات إضافية                         --}}
    {{-- ══════════════════════════════════════════ --}}
    <div class="moz-section-header" style="margin-top:14px;">
        <i class="fa-solid fa-circle-info"></i> معلومات إضافية :
    </div>
    <div class="extra-info-block">
        <table style="width:100%; border-collapse:collapse;">
            <tr>
                <td style="padding:4px 8px; width:50%;">
                    <span class="key">هل المرشح جنسيته السورية :</span>
                    @if($candidate->is_syrian)
                        <span class="val-yes">&nbsp;نعم</span>
                    @else
                        <span class="val-no">&nbsp;لا</span>
                    @endif
                </td>
                <td style="padding:4px 8px; width:50%;">
                    <span class="key">هل المرشح حاصل على مؤهل علمي قبل المؤهل الأخير :</span>
                    @if($application->has_previous_degree)
                        <span class="val-yes">&nbsp;نعم</span>
                    @else
                        <span class="val-no">&nbsp;لا</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td style="padding:4px 8px;" colspan="2">
                    <span class="key">نظام دراسة المرشح :</span>
                    <span style="color:#333;">&nbsp;{{ $application->study_system ?? '---' }}</span>
                </td>
            </tr>
        </table>
    </div>

</div>{{-- end .moz-wrapper --}}

@endsection
