@extends('layouts.admin')

@section('title', 'إدارة لجنة الإنتاج العلمي - الدكتوراه الخارجية')

@section('content')
<div class="container-fluid py-4">

    <!-- HEADER & BREADCRUMB -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item active" aria-current="page">لجنة الإنتاج العلمي</li>
                </ol>
            </nav>
            <h3 class="fw-bold mb-0 text-navy">
                <i class="fa-solid fa-microscope text-gold me-2"></i> إدارة تقييم الإنتاج العلمي (الدكتوراه الخارجية)
            </h3>
            <p class="text-muted small mb-0">مراجعة وتقييم الأطروحات والأبحاث والإنتاج العلمي لطلبات الدكتوراه الصادرة عن الجامعات غير السورية.</p>
        </div>
        <div>
            <span class="badge bg-purple-subtle text-purple border border-purple px-3 py-2 fs-6 rounded-pill">
                <i class="fa-solid fa-flask me-1"></i> {{ $totalCount }} طلبات بانتظار التقييم
            </span>
        </div>
    </div>

    <!-- SEARCH & FILTER BAR -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="{{ route('admin.scientific_production.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="بحث باسم المرشح، الرقم الوطني، أو رقم المعاملة..." value="{{ $search }}">
                    </div>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary px-4 fw-bold">بحث</button>
                    @if($search)
                        <a href="{{ route('admin.scientific_production.index') }}" class="btn btn-outline-secondary">إلغاء الفلتر</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- MAIN APPLICATIONS TABLE -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center" dir="rtl">
                <thead class="table-light">
                    <tr>
                        <th>رقم الطلب</th>
                        <th>المرشح</th>
                        <th>الجامعة الطالبة</th>
                        <th>الجامعة المانحة للدكتوراه</th>
                        <th>التخصص العام والدقيق</th>
                        <th>عنوان الأطروحة</th>
                        <th>المرفقات</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($applications as $app)
                        @php
                            $phdEd = $app->educations->first(function($e) {
                                return optional($e->level)->name && str_contains(optional($e->level)->name, 'دكتوراه');
                            });
                            $thesisSummary = optional($phdEd)->attachments?->first(function($a) {
                                return str_contains($a->notes ?? '', 'ملخص') || $a->attachment_type_id == 12;
                            });
                            $phdCert = optional($phdEd)->attachments?->first(function($a) {
                                return str_contains($a->notes ?? '', 'شهادة') || $a->attachment_type_id == 8;
                            });
                        @endphp
                        <tr>
                            <td>
                                <span class="fw-bold text-navy">{{ $app->application_no }}</span>
                                <div class="small text-muted">{{ $app->new_uni_request_date ? date('Y/m/d', strtotime($app->new_uni_request_date)) : '' }}</div>
                            </td>
                            <td class="text-start">
                                <div class="fw-bold">{{ optional($app->candidate)->full_name ?? 'غير محدد' }}</div>
                                <div class="small text-muted">الرقم الوطني: {{ optional($app->candidate)->national_id }}</div>
                            </td>
                            <td>
                                <div class="fw-bold text-navy">{{ optional($app->workUniversity)->name ?? '---' }}</div>
                                <div class="small text-muted">{{ $app->work_faculty }} - {{ $app->work_department }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ optional($phdEd)->university_name_manual ?? (optional($phdEd)->university->name ?? 'جامعة خارجية') }}
                                </span>
                                <div class="small text-muted mt-1">{{ optional(optional($phdEd)->country)->name ?? '' }}</div>
                            </td>
                            <td>
                                <div class="fw-bold fs-7">{{ optional($phdEd)->department ?? '---' }}</div>
                                <div class="small text-primary">{{ optional($phdEd)->specialization ?? '---' }}</div>
                            </td>
                            <td class="text-start" style="max-width: 250px;">
                                <div class="text-truncate" title="{{ optional($phdEd)->thesis_title }}">
                                    <i class="fa-solid fa-book text-muted me-1"></i> {{ optional($phdEd)->thesis_title ?? '---' }}
                                </div>
                                @if(optional($phdEd)->supervisor)
                                    <div class="small text-muted">المشرف: {{ $phdEd->supervisor }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1 justify-content-center">
                                    @if($thesisSummary)
                                        <a href="{{ route('admin.attachments.view', $thesisSummary->id) }}" target="_blank" class="btn btn-sm btn-outline-info" title="معاينة ملخص الأطروحة">
                                            <i class="fa-solid fa-file-pdf"></i> الملخص
                                        </a>
                                    @endif
                                    @if($phdCert)
                                        <a href="{{ route('admin.attachments.view', $phdCert->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="معاينة شهادة الدكتوراه">
                                            <i class="fa-solid fa-certificate"></i> الشهادة
                                        </a>
                                    @endif
                                    <a href="{{ route('admin.reports.show', $app->id) }}" target="_blank" class="btn btn-sm btn-outline-dark" title="المذكرة الكاملة">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                </div>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-primary fw-bold px-3 shadow-2xs" data-bs-toggle="modal" data-bs-target="#decisionModal-{{ $app->id }}">
                                    <i class="fa-solid fa-gavel me-1"></i> اتخاذ القرار
                                </button>
                            </td>
                        </tr>

                        <!-- DECISION MODAL -->
                        <div class="modal fade" id="decisionModal-{{ $app->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <form action="{{ route('admin.scientific_production.decide', $app->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <div class="modal-header bg-navy text-white py-2.5">
                                            <h6 class="modal-title fw-bold">
                                                <i class="fa-solid fa-microscope me-1"></i> قرار لجنة الإنتاج العلمي - طلب: #{{ $app->application_no }}
                                            </h6>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>

                                        <div class="modal-body p-4 text-start" dir="rtl">
                                            <div class="p-2.5 bg-light rounded mb-3 border fs-7">
                                                <div><strong>المرشح:</strong> {{ optional($app->candidate)->full_name }}</div>
                                                <div><strong>أطروحة الدكتوراه:</strong> {{ optional($phdEd)->thesis_title }}</div>
                                                <div><strong>الجامعة المانحة:</strong> {{ optional($phdEd)->university_name_manual }} ({{ optional(optional($phdEd)->country)->name }})</div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-bold">قرار لجنة الإنتاج العلمي: <span class="text-danger">*</span></label>
                                                <div class="d-flex gap-3 mt-1">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="decision" id="dec_app_{{ $app->id }}" value="approved" onchange="toggleSpFields('{{ $app->id }}', 'approved')" checked>
                                                        <label class="form-check-label text-success fw-bold" for="dec_app_{{ $app->id }}">
                                                            <i class="fa-solid fa-circle-check me-1"></i> موافقة (إحالة إلى المقابلة والأهلية)
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="decision" id="dec_rej_{{ $app->id }}" value="rejected" onchange="toggleSpFields('{{ $app->id }}', 'rejected')">
                                                        <label class="form-check-label text-danger fw-bold" for="dec_rej_{{ $app->id }}">
                                                            <i class="fa-solid fa-circle-xmark me-1"></i> عدم موافقة / رفض
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row g-2 mb-3">
                                                <div class="col-6">
                                                    <label class="form-label label-sm fw-bold">رقم قرار / محضر الإنتاج العلمي</label>
                                                    <input type="text" name="decision_no" class="form-control form-control-sm" placeholder="مثال: 42/إنتاج">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label label-sm fw-bold">تاريخ قرار الإنتاج العلمي</label>
                                                    <input type="date" name="decision_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                                                </div>
                                            </div>

                                            <div id="sp_rejection_box_{{ $app->id }}" style="display: none;" class="mb-3">
                                                <label class="form-label label-sm fw-bold text-danger">أسباب عدم الموافقة على الإنتاج العلمي: <span class="text-danger">*</span></label>
                                                <textarea name="rejection_reason" id="sp_rejection_input_{{ $app->id }}" class="form-control" rows="3" placeholder="أدخل أسباب عدم كفاية الأبحاث أو عدم موافقة اللجنة..."></textarea>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label label-sm fw-bold">ملاحظات تقييم الإنتاج العلمي</label>
                                                <textarea name="notes" class="form-control" rows="2" placeholder="ملاحظات اللجنة حول النتاج العلمي والمجلات المنشور فيها..."></textarea>
                                            </div>
                                        </div>

                                        <div class="modal-footer bg-light py-2">
                                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                            <button type="submit" class="btn btn-sm btn-primary px-4 fw-bold">تثبيت واعتماد القرار</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-flask fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                لا توجد طلبات دكتوراه خارجية بانتظار تقييم الإنتاج العلمي حالياً.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($applications->hasPages())
            <div class="card-footer bg-white py-3">
                {{ $applications->links() }}
            </div>
        @endif
    </div>

</div>

<script>
function toggleSpFields(appId, val) {
    const rejBox = document.getElementById('sp_rejection_box_' + appId);
    const rejInput = document.getElementById('sp_rejection_input_' + appId);

    if (val === 'rejected') {
        rejBox.style.display = 'block';
        rejInput.setAttribute('required', 'required');
    } else {
        rejBox.style.display = 'none';
        rejInput.removeAttribute('required');
    }
}
</script>
@endsection
