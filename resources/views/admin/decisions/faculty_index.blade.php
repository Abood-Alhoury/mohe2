@extends('layouts.admin')

@section('title', 'إصدار قرارات السماح بالتدريس - الهيئة التدريسية')

@section('content')

<!-- NAV TABS FOR DECISION TYPES -->
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div class="btn-group shadow-2xs rounded" role="group">
        <a href="{{ route('admin.decisions.index') }}" class="btn btn-outline-navy fw-bold px-3.5 py-2">
            <i class="fa-solid fa-graduation-cap me-1.5"></i> 1. قرارات التعادل والأهلية (ماجستير / دكتوراه)
        </a>
        <a href="{{ route('admin.faculty_decisions.index') }}" class="btn btn-solid-navy fw-bold px-3.5 py-2">
            <i class="fa-solid fa-stamp me-1.5" style="color: var(--heritage-gold-light);"></i> 2. قرارات السماح بالتدريس (الهيئة التدريسية)
        </a>
    </div>
    <span class="fs-8 text-muted fw-bold">صفحة مستقلة لإدارة وإصدار قرارات السماح بالتدريس الخاصة بأعضاء الهيئة التدريسية</span>
</div>

<div class="row g-3" dir="rtl">

    {{-- ============================================================
         العمود الأيمن: نموذج إصدار قرارات السماح بالتدريس (حقول فارغة افتراضياً)
    ============================================================ --}}
    <div class="col-xl-4 col-lg-5 col-md-12">
        <div class="card border-0 shadow-sm overflow-hidden h-100" style="border-top: 3.5px solid #0D9488 !important; border-radius: 4px;">
            <!-- Header -->
            <div class="card-header py-2.5 px-3 text-white" style="background-color: var(--imperial-navy) !important;">
                <h5 class="mb-0 fs-6 fw-bold text-white d-flex align-items-center gap-2 text-start" dir="rtl">
                    <i class="fa-solid fa-stamp" style="color: var(--heritage-gold-light);"></i>
                    <span>إصدار ورصد قرارات السماح بالتدريس</span>
                </h5>
            </div>
            <div class="card-body p-3.5 bg-white" dir="rtl">
                <form action="{{ route('admin.faculty_decisions.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    {{-- اختر طلب السماح --}}
                    <div class="mb-3 text-start">
                        <label class="form-label fw-bold small d-block text-start mb-1" style="color: var(--imperial-navy);">
                            اختر طلب السماح بالتدريس الموافق عليه :
                        </label>
                        <select name="application_id" class="form-select form-select-sm text-start fw-bold" style="direction: rtl; text-align: right !important; text-align-last: right !important;" required>
                            <option value="">-- اختر المرشح المعني --</option>
                            @foreach($approvedApps as $ap)
                                <option value="{{ $ap->id }}">
                                    طلب {{ $ap->application_no }} - {{ $ap->candidate->full_name ?? '' }} ({{ $ap->workUniversity->name ?? '' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <hr class="my-2 text-muted opacity-25">

                    {{-- رقم وتاريخ قرار السماح (تفريغ الحقول افتراضياً) --}}
                    <div class="row g-2 mb-2.5">
                        <div class="col-6 text-start">
                            <label class="form-label fw-bold small d-block text-start mb-1" style="color: var(--imperial-navy);">
                                رقم قرار السماح بالتدريس :
                            </label>
                            <input
                                type="text"
                                name="decision_no"
                                class="form-control form-control-sm text-start fw-bold"
                                style="direction: rtl; text-align: right !important;"
                                placeholder="أدخل رقم القرار..."
                                value=""
                                required
                            >
                        </div>
                        <div class="col-6 text-start">
                            <label class="form-label fw-bold small d-block text-start mb-1" style="color: var(--imperial-navy);">
                                تاريخ صدور قرار السماح :
                            </label>
                            <input
                                type="date"
                                name="decision_date"
                                class="form-control form-control-sm text-start fw-bold"
                                style="direction: rtl; text-align: right !important;"
                                value=""
                                required
                            >
                        </div>
                    </div>

                    {{-- تحميل نسخة قرار السماح بالتدريس (PDF) --}}
                    <div class="mb-3 text-start">
                        <label class="form-label fw-bold small d-block text-start mb-1" style="color: var(--imperial-navy);">
                            تحميل نسخة قرار السماح بالتدريس الموقع (PDF) :
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
                            ملاحظات اعتماد القرار :
                        </label>
                        <textarea
                            name="notes"
                            class="form-control form-control-sm text-start"
                            style="direction: rtl; text-align: right !important;"
                            rows="2"
                            placeholder="ملاحظات اعتماد قرار السماح بالتدريس"
                        ></textarea>
                    </div>

                    {{-- زر الإرسال --}}
                    <button
                        type="submit"
                        class="btn btn-gold-cta py-2.5 w-100 fw-bold fs-6 shadow-sm d-flex align-items-center justify-content-center gap-2"
                    >
                        <span>إرسال قرار السماح ورصد الصدور</span>
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ============================================================
         العمود الأيسر: أرشيف قرارات السماح بالتدريس الصادرة
    ============================================================ --}}
    <div class="col-xl-8 col-lg-7 col-md-12">
        <div class="card border-0 shadow-sm overflow-hidden h-100" style="border-top: 3.5px solid #0D9488 !important; border-radius: 4px;">
            <!-- Header -->
            <div class="card-header py-2.5 px-3 text-white d-flex align-items-center justify-content-between flex-wrap gap-2" style="background-color: var(--imperial-navy) !important;">
                <h5 class="mb-0 fs-6 fw-bold text-white d-flex align-items-center gap-2 text-start" dir="rtl">
                    <i class="fa-solid fa-box-archive" style="color: var(--heritage-gold-light);"></i>
                    <span>قرارات السماح بالتدريس الصادرة المرسلة للجامعات</span>
                </h5>
                <span class="badge bg-white bg-opacity-10 text-white border border-white-50 px-2.5 py-1 fs-8 fw-normal">
                    إجمالي القرارات: {{ $issuedDecisions->count() }}
                </span>
            </div>

            {{-- شريط البحث --}}
            <div class="p-3 bg-white border-bottom" dir="rtl">
                <form action="{{ route('admin.faculty_decisions.index') }}" method="GET">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-gold-cta fw-bold px-4 white-space-nowrap">
                            <i class="fa-solid fa-magnifying-glass me-1"></i> بحث
                        </button>
                        <input
                            type="text"
                            name="search"
                            class="form-control text-start"
                            style="direction: rtl; text-align: right !important;"
                            placeholder="ابحث باسم عضو الهيئة التدريسية، رقم القرار، أو اسم الجامعة..."
                            value="{{ $search ?? '' }}"
                            autocomplete="off"
                        >
                        @if($search ?? null)
                        <a href="{{ route('admin.faculty_decisions.index') }}" class="btn btn-outline-navy d-flex align-items-center px-3" title="مسح البحث">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- جدول قرارات السماح بالتدريس الصادرة --}}
            <div class="card-body p-0" dir="rtl">
                <div class="table-responsive">
                    <table class="table mohe-table align-middle text-center mb-0">
                        <thead>
                            <tr>
                                <th>رقم قرار السماح</th>
                                <th>تاريخ الصدور</th>
                                <th>اسم عضو الهيئة التدريسية</th>
                                <th>الجامعة الخاصة المعنية</th>
                                <th style="min-width: 90px;">القرار (PDF)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($issuedDecisions as $dec)
                            <tr>
                                <td class="fw-bold" style="color: var(--imperial-navy);">{{ $dec->decision_no }}</td>
                                <td class="text-muted fs-7">{{ $dec->decision_date ? format_sys_date($dec->decision_date) : '-' }}</td>
                                <td class="fw-bold text-dark">{{ $dec->application->candidate->full_name ?? '-' }}</td>
                                <td class="text-secondary fw-semibold fs-7">{{ $dec->application->workUniversity->name ?? '-' }}</td>
                                <td>
                                    <a href="{{ asset('storage/' . $dec->file_path) }}" target="_blank" 
                                       class="btn btn-sm btn-light border border-success text-success p-1.5 rounded shadow-2xs d-inline-flex align-items-center justify-content-center" 
                                       style="width: 32px; height: 32px;"
                                       title="تحميل واستعراض قرار السماح بالتدريس (PDF)">
                                        <i class="fa-solid fa-file-pdf fs-6"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <div class="opacity-50 mb-2">
                                        <i class="fa-solid fa-stamp fs-1" style="color: var(--imperial-navy);"></i>
                                    </div>
                                    <p class="mb-0">لا توجد قرارات سماح بالتدريس صادرة حالياً.</p>
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
