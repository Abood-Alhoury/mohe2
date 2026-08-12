@extends('layouts.admin')

@section('title', 'إرسال قرار التعادل - إصدار وإرسال القرارات')

@section('content')
<div class="row g-3" dir="rtl">

    {{-- ============================================================
         العمود الأيمن: نموذج إصدار القرارات (مدمج ومنظم على سطر واحد لكل من التواريخ والأرقام)
    ============================================================ --}}
    <div class="col-xl-4 col-lg-5 col-md-12">
        <div class="card border-0 shadow-sm overflow-hidden h-100" style="border-top: 3.5px solid var(--heritage-gold) !important; border-radius: 4px;">
            <!-- Header -->
            <div class="card-header py-2.5 px-3 text-white" style="background-color: var(--imperial-navy) !important;">
                <h5 class="mb-0 fs-6 fw-bold text-white d-flex align-items-center gap-2 text-start" dir="rtl">
                    <i class="fa-solid fa-file-import" style="color: var(--heritage-gold-light);"></i>
                    <span>إصدار وإرسال القرارات إلى الجامعة</span>
                </h5>
            </div>
            <div class="card-body p-3.5 bg-white" dir="rtl">
                <form action="{{ route('admin.decisions.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    {{-- اختر طلب التعادل --}}
                    <div class="mb-3 text-start">
                        <label class="form-label fw-bold small d-block text-start mb-1" style="color: var(--imperial-navy);">
                            اختر طلب التعادل الموافق عليه :
                        </label>
                        <select name="application_id" class="form-select form-select-sm text-start fw-bold" style="direction: rtl; text-align: right !important; text-align-last: right !important;" required>
                            <option value="">-- اختر الطالب المعني --</option>
                            @foreach($approvedApps as $ap)
                                <option value="{{ $ap->id }}">
                                    طلب {{ $ap->application_no }} - {{ $ap->candidate->full_name ?? '' }} ({{ $ap->workUniversity->name ?? '' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <hr class="my-2 text-muted opacity-25">

                    {{-- سطر 1: رقم قرار الأهلية وتاريخ الصدور على سطر واحد --}}
                    <div class="row g-2 mb-2.5">
                        <div class="col-6 text-start">
                            <label class="form-label fw-bold small d-block text-start mb-1" style="color: var(--imperial-navy);">
                                رقم قرار الأهلية (اختياري) :
                            </label>
                            <input
                                type="text"
                                name="eligibility_decision_no"
                                class="form-control form-control-sm text-start"
                                style="direction: rtl; text-align: right !important;"
                                placeholder="مثال: أ.هـ/502/2026"
                            >
                        </div>
                        <div class="col-6 text-start">
                            <label class="form-label fw-bold small d-block text-start mb-1" style="color: var(--imperial-navy);">
                                تاريخ صدور قرار الأهلية :
                            </label>
                            <input
                                type="date"
                                name="eligibility_decision_date"
                                class="form-control form-control-sm text-start"
                                style="direction: rtl; text-align: right !important;"
                            >
                        </div>
                    </div>

                    {{-- تحميل نسخة قرار الأهلية (PDF) --}}
                    <div class="mb-3 text-start">
                        <label class="form-label fw-bold small d-block text-start mb-1" style="color: var(--imperial-navy);">
                            تحميل نسخة قرار الأهلية (PDF) :
                        </label>
                        <input
                            type="file"
                            name="eligibility_decision_file"
                            class="form-control form-control-sm text-start"
                            style="direction: rtl; text-align: right !important;"
                            accept=".pdf,image/*"
                        >
                    </div>

                    <hr class="my-2 text-muted opacity-25">

                    {{-- سطر 2: رقم قرار المعادلة وتاريخ الصدور على سطر واحد --}}
                    <div class="row g-2 mb-2.5">
                        <div class="col-6 text-start">
                            <label class="form-label fw-bold small d-block text-start mb-1" style="color: var(--imperial-navy);">
                                رقم قرار المعادلة :
                            </label>
                            <input
                                type="text"
                                name="decision_no"
                                class="form-control form-control-sm text-start fw-bold"
                                style="direction: rtl; text-align: right !important;"
                                placeholder="مثال: م.ل.ق/105/2026"
                                value="م.ل.ق/105/2026"
                                required
                            >
                        </div>
                        <div class="col-6 text-start">
                            <label class="form-label fw-bold small d-block text-start mb-1" style="color: var(--imperial-navy);">
                                تاريخ صدور المعادلة :
                            </label>
                            <input
                                type="date"
                                name="decision_date"
                                class="form-control form-control-sm text-start fw-bold"
                                style="direction: rtl; text-align: right !important;"
                                value="{{ date('Y-m-d') }}"
                                required
                            >
                        </div>
                    </div>

                    {{-- تحميل نسخة قرار التعادل الموقع (PDF) --}}
                    <div class="mb-3 text-start">
                        <label class="form-label fw-bold small d-block text-start mb-1" style="color: var(--imperial-navy);">
                            تحميل نسخة قرار التعادل الموقع (PDF) :
                        </label>
                        <input
                            type="file"
                            name="decision_file"
                            class="form-control form-control-sm text-start"
                            style="direction: rtl; text-align: right !important;"
                            accept=".pdf,image/*"
                            required
                        >
                    </div>

                    {{-- ملاحظات القرار --}}
                    <div class="mb-3.5 text-start">
                        <label class="form-label fw-bold small d-block text-start mb-1" style="color: var(--imperial-navy);">
                            ملاحظات القرار :
                        </label>
                        <textarea
                            name="notes"
                            class="form-control form-control-sm text-start"
                            style="direction: rtl; text-align: right !important;"
                            rows="2"
                            placeholder="ملاحظات رئيس مجلس التعليم العالي"
                        ></textarea>
                    </div>

                    {{-- زر الإرسال --}}
                    <button
                        type="submit"
                        class="btn btn-gold-cta py-2.5 w-100 fw-bold fs-6 shadow-sm d-flex align-items-center justify-content-center gap-2"
                    >
                        <span>إرسال القرار ورصد الصدور</span>
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ============================================================
         العمود الأيسر: أرشيف القرارات الصادرة (أوسع ومريح للعرض)
    ============================================================ --}}
    <div class="col-xl-8 col-lg-7 col-md-12">
        <div class="card border-0 shadow-sm overflow-hidden h-100" style="border-top: 3.5px solid var(--heritage-gold) !important; border-radius: 4px;">
            <!-- Header -->
            <div class="card-header py-2.5 px-3 text-white" style="background-color: var(--imperial-navy) !important;">
                <h5 class="mb-0 fs-6 fw-bold text-white d-flex align-items-center gap-2 text-start" dir="rtl">
                    <i class="fa-solid fa-box-archive" style="color: var(--heritage-gold-light);"></i>
                    <span>القرارات الصادرة المرسلة للجامعات</span>
                </h5>
            </div>

            {{-- شريط البحث --}}
            <div class="p-3 bg-white border-bottom" dir="rtl">
                <form action="{{ route('admin.decisions.index') }}" method="GET">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-gold-cta fw-bold px-4 white-space-nowrap">
                            <i class="fa-solid fa-magnifying-glass me-1"></i> بحث
                        </button>
                        <input
                            type="text"
                            name="search"
                            class="form-control text-start"
                            style="direction: rtl; text-align: right !important;"
                            placeholder="إبحث باسم الطالب أو رقم القرار أو اسم الجامعة..."
                            value="{{ $search ?? '' }}"
                            autocomplete="off"
                        >
                        @if($search ?? null)
                        <a href="{{ route('admin.decisions.index') }}" class="btn btn-outline-navy d-flex align-items-center px-3" title="مسح البحث">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- جدول القرارات --}}
            <div class="card-body p-0" dir="rtl">
                <div class="table-responsive">
                    <table class="table mohe-table align-middle text-center mb-0">
                        <thead>
                            <tr>
                                <th>رقم قرار المعادلة</th>
                                <th>رقم قرار الأهلية</th>
                                <th>اسم الطالب</th>
                                <th>الجامعة</th>
                                <th>تاريخ الصدور</th>
                                <th style="min-width: 170px;">تحميل الملفات (PDF)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($issuedDecisions as $dec)
                            <tr>
                                <td class="fw-bold" style="color: var(--imperial-navy);">{{ $dec->decision_no }}</td>
                                <td class="fw-bold text-secondary">{{ $dec->eligibility_decision_no ?? '-' }}</td>
                                <td class="fw-bold text-dark">{{ $dec->application->candidate->full_name ?? '-' }}</td>
                                <td>{{ $dec->application->workUniversity->name ?? '-' }}</td>
                                <td class="text-muted fs-7">{{ $dec->decision_date }}</td>
                                <td>
                                    <div class="d-flex flex-column gap-1.5 align-items-center justify-content-center">
                                        @if($dec->eligibility_file_path)
                                            <a href="{{ asset('storage/' . $dec->eligibility_file_path) }}" target="_blank" class="btn btn-xs btn-outline-info fw-bold px-2 py-1 w-100 shadow-2xs text-nowrap d-inline-flex align-items-center justify-content-center gap-1" style="font-size: 0.76rem;" title="تحميل نسخة قرار الأهلية">
                                                <i class="fa-solid fa-file-pdf text-info fs-8"></i> تحميل قرار الأهلية
                                            </a>
                                        @endif
                                        <a href="{{ asset('storage/' . $dec->file_path) }}" target="_blank" class="btn btn-xs btn-outline-gold fw-bold px-2 py-1 w-100 shadow-2xs text-nowrap d-inline-flex align-items-center justify-content-center gap-1" style="font-size: 0.76rem;" title="تحميل نسخة قرار المعادلة النهائية">
                                            <i class="fa-solid fa-file-pdf text-danger fs-8"></i> تحميل قرار المعادلة
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <div class="opacity-50 mb-2">
                                        <i class="fa-solid fa-stamp fs-1" style="color: var(--imperial-navy);"></i>
                                    </div>
                                    <p class="mb-0">لا توجد قرارات صادرة حالياً.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
