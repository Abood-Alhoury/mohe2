@extends('layouts.admin')

@section('title', 'إرسال قرار التعادل - إصدار وإرسال القرارات')

@section('content')
<div class="row g-4" dir="rtl">

    {{-- ============================================================
         العمود الأيمن: نموذج إصدار القرار (الأول في RTL = الجانب الأيمن)
    ============================================================ --}}
    <div class="col-lg-5 col-md-12">
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden h-100">
            <!-- Header: Title starting from Far Right in RTL -->
            <div class="card-header py-3 px-3 text-white" style="background-color: #047857;">
                <h5 class="mb-0 fs-6 fw-bold text-white d-flex align-items-center gap-2 text-start" dir="rtl">
                    <i class="fa-solid fa-file-import"></i>
                    <span>إصدار وإرسال القرار إلى تحديد الجامعة</span>
                </h5>
            </div>
            <div class="card-body p-4 bg-white" dir="rtl">
                <form action="{{ route('admin.decisions.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    {{-- اختر طلب التعادل --}}
                    <div class="mb-3 text-start">
                        <label class="form-label fw-bold text-dark d-block text-start">
                            اختر طلب التعادل الموافق عليه :
                        </label>
                        <select name="application_id" class="form-select text-start" style="direction: rtl; text-align: right !important; text-align-last: right !important;" required>
                            <option value="">-- اختر الطالب المعني --</option>
                            @foreach($approvedApps as $ap)
                                <option value="{{ $ap->id }}">
                                    طلب {{ $ap->application_no }} - {{ $ap->candidate->full_name ?? '' }} ({{ $ap->workUniversity->name ?? '' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- رقم القرار الوزاري --}}
                    <div class="mb-3 text-start">
                        <label class="form-label fw-bold text-dark d-block text-start">
                            رقم القرار الوزاري :
                        </label>
                        <input
                            type="text"
                            name="decision_no"
                            class="form-control text-start"
                            style="direction: rtl; text-align: right !important;"
                            placeholder="مثال: م.ل.ق/105/2026"
                            value="م.ل.ق/105/2026"
                            required
                        >
                    </div>

                    {{-- تاريخ صدور القرار --}}
                    <div class="mb-3 text-start">
                        <label class="form-label fw-bold text-dark d-block text-start">
                            تاريخ صدور القرار :
                        </label>
                        <input
                            type="date"
                            name="decision_date"
                            class="form-control text-start"
                            style="direction: rtl; text-align: right !important;"
                            value="{{ date('Y-m-d') }}"
                            required
                        >
                    </div>

                    {{-- تحميل نسخة PDF --}}
                    <div class="mb-3 text-start">
                        <label class="form-label fw-bold text-dark d-block text-start">
                            تحميل نسخة قرار التعادل الموقع (PDF) :
                        </label>
                        <input
                            type="file"
                            name="decision_file"
                            class="form-control text-start"
                            style="direction: rtl; text-align: right !important;"
                            accept=".pdf,image/*"
                            required
                        >
                    </div>

                    {{-- ملاحظات القرار --}}
                    <div class="mb-4 text-start">
                        <label class="form-label fw-bold text-dark d-block text-start">
                            ملاحظات القرار :
                        </label>
                        <textarea
                            name="notes"
                            class="form-control text-start"
                            style="direction: rtl; text-align: right !important;"
                            rows="3"
                            placeholder="ملاحظات رئيس مجلس التعليم العالي"
                        ></textarea>
                    </div>

                    {{-- زر الإرسال --}}
                    <button
                        type="submit"
                        class="btn py-3 w-100 fw-bold fs-6 shadow-sm text-white d-flex align-items-center justify-content-center gap-2"
                        style="background-color: #047857; border-color: #047857;"
                    >
                        <span>إرسال القرار ورصد الصدور</span>
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ============================================================
         العمود الأيسر: أرشيف القرارات الصادرة (الثاني في RTL = الجانب الأيسر)
    ============================================================ --}}
    <div class="col-lg-7 col-md-12">
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden h-100">
            <!-- Header: Title starting from Far Right in RTL -->
            <div class="card-header py-3 px-3 text-white" style="background-color: #1E3A5F; border-bottom: 3px solid var(--mohe-gold);">
                <h5 class="mb-0 fs-6 fw-bold text-white d-flex align-items-center gap-2 text-start" dir="rtl">
                    <i class="fa-solid fa-box-archive text-warning"></i>
                    <span>القرارات الصادرة المرسلة للجامعات</span>
                </h5>
            </div>

            {{-- شريط البحث --}}
            <div class="p-3 bg-white border-bottom" dir="rtl">
                <form action="{{ route('admin.decisions.index') }}" method="GET">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn fw-bold px-4 text-white" style="background-color: #047857; border-color: #047857; white-space: nowrap;">
                            <i class="fa-solid fa-magnifying-glass me-1"></i> بحث
                        </button>
                        <input
                            type="text"
                            name="search"
                            class="form-control text-start"
                            style="direction: rtl; text-align: right !important;"
                            placeholder="إبحث باسم الطالب أو اسم الجامعة..."
                            value="{{ $search ?? '' }}"
                            autocomplete="off"
                        >
                        @if($search ?? null)
                        <a href="{{ route('admin.decisions.index') }}" class="btn btn-outline-secondary d-flex align-items-center px-3" title="مسح البحث">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- جدول القرارات --}}
            <div class="card-body p-0" dir="rtl">
                <div class="table-responsive">
                    <table class="table align-middle text-center mb-0">
                        <thead style="background-color: #1E3A5F; color: white;">
                            <tr>
                                <th class="py-3 text-white">رقم القرار</th>
                                <th class="py-3 text-white">اسم الطالب</th>
                                <th class="py-3 text-white">الجامعة</th>
                                <th class="py-3 text-white">تاريخ الصدور</th>
                                <th class="py-3 text-white">الملف</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($issuedDecisions as $dec)
                            <tr>
                                <td class="fw-bold text-primary">{{ $dec->decision_no }}</td>
                                <td class="fw-bold text-dark">{{ $dec->application->candidate->full_name ?? '-' }}</td>
                                <td>{{ $dec->application->workUniversity->name ?? '-' }}</td>
                                <td class="text-muted fs-7">{{ $dec->decision_date }}</td>
                                <td>
                                    <a href="{{ asset('storage/' . $dec->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary fw-bold px-3">
                                        <i class="fa-solid fa-file-pdf me-1 text-danger"></i> عرض PDF
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <div class="opacity-50 mb-2">
                                        <i class="fa-solid fa-stamp fs-1 text-secondary"></i>
                                    </div>
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
