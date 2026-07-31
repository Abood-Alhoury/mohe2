@extends('layouts.admin')

@section('title', 'إرسال قرار التعادل - إصدار وإرسال القرارات')

@section('content')
<div class="row g-4">
    <!-- Left Column: Issue Decision Form -->
    <div class="col-md-5">
        <div class="card border border-success-subtle shadow-sm">
            <div class="card-header bg-success text-white font-bold">
                <i class="fa-solid fa-stamp me-2"></i> إصدار وإرسال قرار تعادل جديد للجامعة
            </div>
            <div class="card-body">
                <form action="{{ route('admin.decisions.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold">اختر طلب التعادل الموافَق عليه :</label>
                        <select name="application_id" class="form-select" required>
                            <option value="">-- اختر الطالب المعني --</option>
                            @foreach($approvedApps as $ap)
                                <option value="{{ $ap->id }}">
                                    طلب {{ $ap->application_no }} - {{ $ap->candidate->full_name ?? '' }} ({{ $ap->workUniversity->name ?? '' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">رقم القرار الوزاري :</label>
                        <input type="text" name="decision_no" class="form-control" placeholder="مثل: ق/2026/105" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">تاريخ صدور القرار :</label>
                        <input type="date" name="decision_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">تحميل نسخة قرار التعادل الموقع (PDF) :</label>
                        <input type="file" name="decision_file" class="form-control" accept=".pdf,image/*" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">ملاحظات القرار :</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="ملاحظات رئيس مجلس التعليم العالي"></textarea>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-success px-5 fw-bold w-100">
                            <i class="fa-solid fa-paper-plane me-1"></i> إرسال القرار ورصد الصدور
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: Issued Decisions Archive Table -->
    <div class="col-md-7">
        <div class="mohe-card">
            <div class="mohe-card-header bg-light">
                <h5 class="mohe-card-title text-success" style="color: #0f392b !important;"><i class="fa-solid fa-box-archive me-2"></i> القرارات الصادرة المرسلة للجامعات</h5>
            </div>

            {{-- Search Bar --}}
            <div class="px-3 pt-3 pb-2">
                <form action="{{ route('admin.decisions.index') }}" method="GET">
                    <div class="input-group shadow-sm">
                        <span class="input-group-text bg-white border-end-0" style="border-color: #0f392b;">
                            <i class="fa-solid fa-magnifying-glass text-success"></i>
                        </span>
                        <input
                            type="text"
                            name="search"
                            class="form-control border-start-0 border-end-0 ps-0"
                            style="border-color: #0f392b;"
                            placeholder="ابحث باسم الطالب أو اسم الجامعة..."
                            value="{{ $search ?? '' }}"
                            autocomplete="off"
                        >
                        @if($search)
                        <a href="{{ route('admin.decisions.index') }}" class="btn btn-outline-secondary border-start-0" style="border-color: #0f392b;" title="مسح البحث">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                        @endif
                        <button type="submit" class="btn fw-bold text-white px-4" style="background-color: #0f392b;">
                            بحث
                        </button>
                    </div>
                    @if($search)
                    <div class="mt-2 text-muted small">
                        <i class="fa-solid fa-circle-info me-1"></i>
                        نتائج البحث عن: <strong class="text-dark">{{ $search }}</strong>
                        &mdash; {{ $issuedDecisions->count() }} نتيجة
                    </div>
                    @endif
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mohe-table align-middle">
                        <thead>
                            <tr>
                                <th>رقم القرار</th>
                                <th>اسم الطالب</th>
                                <th>الجامعة</th>
                                <th>تاريخ الصدور</th>
                                <th>الملف</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($issuedDecisions as $dec)
                            <tr>
                                <td class="fw-bold text-success">{{ $dec->decision_no }}</td>
                                <td class="fw-bold">{{ $dec->application->candidate->full_name ?? '' }}</td>
                                <td>{{ $dec->application->workUniversity->name ?? '' }}</td>
                                <td class="fs-7 text-muted">{{ $dec->decision_date }}</td>
                                <td>
                                    <a href="{{ asset('storage/' . $dec->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary fw-bold">
                                        <i class="fa-solid fa-file-pdf me-1"></i> عرض
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">لا توجد قرارات صادرة مسجلة في الأرشيف حالياً.</td>
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
