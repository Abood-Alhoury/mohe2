@extends('layouts.admin')

@section('title', 'إدارة وتحديد مواعيد المقابلات - بانتظار المقابلة')

@section('content')
<div class="container-fluid px-4 py-3" dir="rtl">

    <!-- PAGE HEADER -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 p-3.5 bg-white shadow-xs rounded border border-secondary-subtle">
        <div>
            <h4 class="fw-bold mb-1 d-flex align-items-center gap-2" style="color: var(--imperial-navy);">
                <i class="fa-solid fa-calendar-check text-cyan" style="color: #0891b2;"></i>
                <span>إدارة وحجز مواعيد المقابلات الشفهية والعملية</span>
            </h4>
            <p class="text-muted mb-0 small">تجميع كافة المرشحين بحالة (بانتظار المقابلة)، تحديد موعد وساعة المقابلة، وتوليد قرار الأهلية لتقديم المقابلة.</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-2 fs-7 rounded-pill">
                <i class="fa-solid fa-users me-1"></i> إجمالي بانتظار المقابلة: <strong>{{ $totalAwaitingCount }}</strong>
            </span>
            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-7 rounded-pill">
                <i class="fa-solid fa-clock-check me-1"></i> تم تحديد الموعد لهم: <strong>{{ $scheduledCount }}</strong>
            </span>
        </div>
    </div>

    <!-- BATCH SCHEDULING FORM CARD -->
    <form action="{{ route('admin.interviews.batch_schedule') }}" method="POST" id="batchInterviewForm">
        @csrf
        <div class="card border-0 shadow-sm mb-4" style="border-top: 3.5px solid #0891b2 !important; border-radius: 6px;">
            <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                <h6 class="fw-bold mb-0" style="color: var(--imperial-navy);">
                    <i class="fa-solid fa-business-time me-1.5" style="color: #0891b2;"></i>
                    <span>تحديد موعد وساعة المقابلة الموحدة للمرشحين المحددين</span>
                </h6>
                <span class="text-muted fs-8">قم بتحديد المرشحين من الجدول أدناه ثم أدخل التاريخ والوقت واضغط حفظ.</span>
            </div>
            <div class="card-body p-4 bg-light-subtle">
                <div class="row g-3 align-items-end">

                    <!-- 1. Interview Date -->
                    <div class="col-md-3">
                        <label class="form-label fw-bold small text-dark mb-1">
                            <i class="fa-regular fa-calendar me-1 text-primary"></i> تاريخ المقابلة :
                        </label>
                        <input type="date" name="interview_date" class="form-control fw-bold" value="{{ date('Y-m-d', strtotime('+3 days')) }}" required>
                    </div>

                    <!-- 2. Interview Time -->
                    <div class="col-md-3">
                        <label class="form-label fw-bold small text-dark mb-1">
                            <i class="fa-regular fa-clock me-1 text-primary"></i> ساعة/وقت المقابلة :
                        </label>
                        <input type="text" name="interview_time" class="form-control fw-bold" placeholder="مثال: الساعة 10:00 صباحاً" value="الساعة 10:00 صباحاً" required>
                    </div>

                    <!-- 3. Additional Notes -->
                    <div class="col-md-4">
                        <label class="form-label fw-bold small text-dark mb-1">
                            <i class="fa-solid fa-location-dot me-1 text-danger"></i> مكان / ملاحظات المقابلة :
                        </label>
                        <input type="text" name="interview_notes" class="form-control" placeholder="مبنى وزارة التعليم العالي - القاعة الرئيسية" value="مبنى وزارة التعليم العالي والبحث العلمي - القاعة الرئيسية">
                    </div>

                    <!-- 4. Submit Button -->
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-solid-navy w-100 fw-bold py-2 shadow-xs d-flex align-items-center justify-content-center gap-1.5">
                            <i class="fa-solid fa-save me-1" style="color: var(--heritage-gold-light);"></i>
                            <span>تثبيت الموعد</span>
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <!-- SEARCH AND FILTER BAR -->
        <div class="bg-white p-3 rounded shadow-xs border mb-4">
            <div class="row g-2 align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" name="search" form="filterForm" class="form-control border-start-0" placeholder="إبحث باسم المرشح، الرقم الوطني، أو رقم الطلب..." value="{{ $searchQuery ?? '' }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="university_id" form="filterForm" class="form-select" onchange="document.getElementById('filterForm').submit()">
                        <option value="">-- كافة الجامعات --</option>
                        @foreach($universities as $u)
                            <option value="{{ $u->id }}" {{ $universityFilter == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" form="filterForm" class="btn btn-outline-navy w-100 fw-bold">تصفية</button>
                </div>
            </div>
        </div>

        <!-- CANDIDATES AWAITING INTERVIEWS TABLE -->
        <div class="card border-0 shadow-sm overflow-hidden mb-4">
            <div class="table-responsive">
                <table class="table mohe-table align-middle text-center mb-0">
                    <thead style="background-color: var(--imperial-navy); color: #ffffff;">
                        <tr>
                            <th style="width: 40px;">
                                <input type="checkbox" id="selectAll" class="form-check-input" onclick="toggleSelectAll(this)">
                            </th>
                            <th>معرف الطلب والكلمة</th>
                            <th>المرشح والمعلومات</th>
                            <th>الجامعة والكلية</th>
                            <th>المؤهل العلمي</th>
                            <th>موعد المقابلة المعتمد</th>
                            <th>الإجراءات وقرار الأهلية</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($applications as $app)
                        @php
                            $lastEd = $app->educations->last();
                        @endphp
                        <tr>
                            <!-- Checkbox -->
                            <td>
                                <input type="checkbox" name="application_ids[]" value="{{ $app->id }}" class="form-check-input app-checkbox">
                            </td>

                            <!-- Application ID & Code -->
                            <td>
                                <div class="fw-bold" style="color: var(--imperial-navy);">{{ $app->application_no }}</div>
                                <span class="badge bg-light text-dark border fs-8">{{ $app->request_type ?? 'تعادل جديد' }}</span>
                            </td>

                            <!-- Candidate Name -->
                            <td>
                                <div class="fw-bold text-dark fs-6">{{ $app->candidate->full_name ?? 'غ/م' }}</div>
                                <small class="text-muted d-block fs-8"><i class="fa-regular fa-id-card me-1"></i> {{ $app->candidate->national_id ?? '---' }}</small>
                            </td>

                            <!-- University & Faculty -->
                            <td>
                                <div class="fw-semibold text-dark">{{ $app->workUniversity->name ?? 'غير محددة' }}</div>
                                <small class="text-muted fs-8">{{ $app->work_faculty ?? 'إدارة جامعة' }}</small>
                            </td>

                            <!-- Degree Level & Specialization -->
                            <td>
                                <div class="fw-bold" style="color: #0369a1;">{{ $lastEd->level->name ?? 'إجازة' }}</div>
                                <small class="text-muted fs-8">{{ $lastEd->exact_specialization ?? ($lastEd->general_specialization ?? '') }}</small>
                            </td>

                            <!-- Scheduled Interview Date & Time -->
                            <td>
                                @if($app->interview_date)
                                    <div class="p-2 rounded bg-cyan-subtle border border-cyan-subtle d-inline-block text-start" style="background-color: #ecfeff; border-color: #a5f3fc !important; color: #0891b2;">
                                        <div class="fw-bold fs-7 mb-0.5">
                                            <i class="fa-regular fa-calendar-check me-1"></i> {{ format_sys_date($app->interview_date) }}
                                        </div>
                                        <div class="small fw-semibold fs-8" style="color: #0e7490;">
                                            <i class="fa-regular fa-clock me-1"></i> {{ $app->interview_time ?? '10:00 AM' }}
                                        </div>
                                    </div>
                                @else
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1.5 fs-8">
                                        <i class="fa-solid fa-hourglass-start me-1"></i> لم يحدد الموعد بعد
                                    </span>
                                @endif
                            </td>

                            <!-- Actions & Eligibility Decision -->
                            <td>
                                <div class="d-flex flex-column gap-1.5 align-items-center justify-content-center">
                                    <!-- Eligibility Decision Generator -->
                                    <a href="{{ route('admin.interviews.eligibility_decision', $app->id) }}" target="_blank" class="btn btn-xs fw-bold shadow-2xs py-1 px-2.5 text-decoration-none w-100 d-inline-flex align-items-center justify-content-center gap-1" style="font-size: 0.78rem; border: 1px solid #93c5fd; color: #1d4ed8; background-color: #eff6ff;" title="توليد وتنزيل قرار الأهلية لتقديم المقابلة للمرشح">
                                        <i class="fa-solid fa-award text-primary fs-8"></i> قرار الأهلية
                                    </a>

                                    <!-- Quick Individual Schedule Button -->
                                    <button type="button" class="btn btn-xs btn-outline-navy py-1 px-2 w-100" data-bs-toggle="modal" data-bs-target="#singleScheduleModal{{ $app->id }}">
                                        <i class="fa-solid fa-pen-to-square me-1"></i> تعديل الموعد
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-calendar-xmark fs-2 mb-2 d-block text-cyan" style="color: #0891b2;"></i>
                                لا يوجد مرشحين بانتظار المقابلة حالياً.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($applications->hasPages())
            <div class="card-footer bg-white py-3 border-top">
                {{ $applications->withQueryString()->links() }}
            </div>
            @endif
        </div>
    </form>

</div>

<!-- HIDDEN FILTER FORM -->
<form action="{{ route('admin.interviews.index') }}" method="GET" id="filterForm" class="d-none"></form>

<!-- INDIVIDUAL SCHEDULE MODALS -->
@foreach($applications as $app)
<div class="modal fade" id="singleScheduleModal{{ $app->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-top: 4px solid #0891b2 !important; border-radius: 6px;">
            <form action="{{ route('admin.interviews.batch_schedule') }}" method="POST">
                @csrf
                <input type="hidden" name="application_ids[]" value="{{ $app->id }}">
                
                <div class="modal-header bg-light">
                    <h6 class="modal-title fw-bold text-navy">
                        <i class="fa-solid fa-user-clock me-1.5" style="color: #0891b2;"></i>
                        تحديد موعد المقابلة للمرشح: {{ $app->candidate->full_name ?? '' }}
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body p-4">
                    <div class="mb-3 text-start">
                        <label class="form-label fw-bold small text-dark">تاريخ المقابلة :</label>
                        <input type="date" name="interview_date" class="form-control fw-bold" value="{{ $app->interview_date ?? date('Y-m-d', strtotime('+3 days')) }}" required>
                    </div>

                    <div class="mb-3 text-start">
                        <label class="form-label fw-bold small text-dark">ساعة / وقت المقابلة :</label>
                        <input type="text" name="interview_time" class="form-control fw-bold" value="{{ $app->interview_time ?? 'الساعة 10:00 صباحاً' }}" required>
                    </div>

                    <div class="mb-3 text-start">
                        <label class="form-label fw-bold small text-dark">المكان / الملاحظات :</label>
                        <input type="text" name="interview_notes" class="form-control" value="{{ $app->interview_notes ?? 'مبنى وزارة التعليم العالي والبحث العلمي - القاعة الرئيسية' }}">
                    </div>
                </div>
                
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary px-3" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-solid-navy px-4 fw-bold">حفظ الموعد</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<script>
function toggleSelectAll(master) {
    const checkboxes = document.querySelectorAll('.app-checkbox');
    checkboxes.forEach(cb => cb.checked = master.checked);
}
</script>
@endsection
