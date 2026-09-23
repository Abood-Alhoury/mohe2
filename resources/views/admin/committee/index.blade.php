@extends('layouts.admin')

@section('title', 'مواضيع اللجنة العامة - دراسة قرارات اللجنة')

@section('content')
<div class="mohe-card">
    <div class="mohe-card-header bg-light">
        <h5 class="mohe-card-title text-warning"><i class="fa-solid fa-users-rectangle me-2"></i> شؤون وقرارات اللجنة العامة</h5>
    </div>
    <div class="card-body p-0">
        <div class="p-3 bg-warning-subtle text-dark border-bottom fw-bold fs-7">
            <i class="fa-solid fa-circle-info me-2"></i> قرارات اللجنة العامة: الماجستير الخارجي النظري يتضمن 3 خيارات (مقبول / ماجستير تطبيقي / مرفوض)، والتطبيقي يتضمن خيارين (مقبول / مرفوض).
        </div>

        <div class="table-responsive">
            <table class="table mohe-table align-middle">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>نوع الطلب</th>
                        <th>الجامعة</th>
                        <th>الاسم</th>
                        <th>الكلية</th>
                        <th>المؤهل العلمي</th>
                        <th>وضع الطلب</th>
                        <th style="width: 250px;" class="text-center">قرار اللجنة العامة</th>
                        <th style="width: 70px;" class="text-center">معاينة</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($committeeApps as $app)
                    @php
                        $lastEducation = $app->educations->last();
                        $isForeignMaster = str_contains($app->request_type ?? '', 'خارجي') || str_contains($app->request_type ?? '', 'غير سوري');
                        $isTheoretical = $isForeignMaster && str_contains($app->request_type ?? '', 'نظري');
                    @endphp
                    <tr>
                        <td class="fw-bold text-secondary">{{ $app->id }}</td>
                        <td>
                            <span class="badge {{ $isForeignMaster ? 'bg-primary-subtle text-primary border border-primary' : 'bg-light text-dark border' }}">
                                {{ $app->request_type ?? 'تعادل' }}
                            </span>
                        </td>
                        <td class="fw-bold">{{ $app->workUniversity->name ?? 'جامعة غير محددة' }}</td>
                        <td class="text-primary fw-bold">{{ $app->candidate->full_name ?? 'غ/م' }}</td>
                        <td>{{ $app->work_faculty ?? 'إدارة جامعة' }}</td>
                        <td>{{ $lastEducation->level->name ?? 'إجازة جامعية' }}</td>
                        <td>
                            <span class="badge bg-warning text-dark border border-warning fs-7"><i class="fa-solid fa-users-rectangle me-1"></i> {{ $app->status }}</span>
                        </td>
                        <td class="text-center">
                            @if($isForeignMaster)
                                <button type="button" class="btn btn-sm btn-primary fw-bold px-3 py-1 shadow-xs" data-bs-toggle="modal" data-bs-target="#decisionModal-{{ $app->id }}">
                                    <i class="fa-solid fa-gavel me-1"></i> اتخاذ القرار
                                </button>
                            @else
                                <form action="{{ route('admin.committee.decide', $app->id) }}" method="POST" class="d-flex gap-1 justify-content-center">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" name="decision" value="موافقة" class="btn btn-sm btn-success fw-bold px-3" onclick="return confirm('إقرار موافقة اللجنة العامة على المعاملة؟');">
                                        <i class="fa-solid fa-check me-1"></i> موافقة
                                    </button>
                                    <button type="submit" name="decision" value="رفض" class="btn btn-sm btn-danger fw-bold px-3" onclick="return confirm('إقرار رفض المعاملة؟');">
                                        <i class="fa-solid fa-xmark me-1"></i> رفض
                                    </button>
                                </form>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.reports.show', $app->id) }}" class="btn btn-sm btn-outline-secondary px-2.5 fw-bold" title="عرض تفاصيل المذكرة">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                        </td>
                    </tr>

                    {{-- Modal اتخاذ القرار للماجستير الخارجي --}}
                    @if($isForeignMaster)
                    <div class="modal fade" id="decisionModal-{{ $app->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow">
                                <form action="{{ route('admin.committee.decide', $app->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <div class="modal-header bg-navy text-white py-2.5">
                                        <h6 class="modal-title fw-bold">
                                            <i class="fa-solid fa-gavel me-1"></i> قرار اللجنة - طلب: #{{ $app->application_no }}
                                        </h6>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>

                                    <div class="modal-body p-4 text-start" dir="rtl">
                                        <div class="p-2.5 bg-light rounded mb-3 border fs-7">
                                            <div><strong>المرشح:</strong> {{ $app->candidate->full_name }}</div>
                                            <div><strong>نوع الطلب الحالي:</strong> <span class="badge {{ $isTheoretical ? 'bg-info-subtle text-info-emphasis' : 'bg-secondary-subtle text-secondary' }}">{{ $app->request_type }}</span></div>
                                        </div>

                                        {{-- الـ Drop-down list الذكي --}}
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">قرار اللجنة العامّة: <span class="text-danger">*</span></label>
                                            <select name="decision_action" id="decision_select_{{ $app->id }}" class="form-select fw-bold academic-input" onchange="handleDecisionChange('{{ $app->id }}', this.value)" required>
                                                <option value="" disabled selected>-- اختر القرار من القائمة --</option>
                                                
                                                @if($isTheoretical)
                                                    {{-- الخيارات الثلاثة للماجستير النظري --}}
                                                    <option value="approved_theoretical" class="text-success">
                                                        1. مقبول (مسار نظري - اعتماد خبرة سنتين فأكثر ← بانتظار المقابلة)
                                                    </option>
                                                    <option value="approved_applied" class="text-primary">
                                                        2. ماجستير تطبيقي (الخبرة غير كافية - عضو هيئة فنية ← بانتظار إصدار القرار مباشرة)
                                                    </option>
                                                    <option value="rejected" class="text-danger">
                                                        3. مرفوض (رفض تعادل الشهادة)
                                                    </option>
                                                @else
                                                    {{-- الخياران للماجستير التطبيقي --}}
                                                    <option value="approved_applied" class="text-success">
                                                        1. مقبول (مسار تطبيقي - عضو هيئة فنية ← بانتظار إصدار القرار مباشرة)
                                                    </option>
                                                    <option value="rejected" class="text-danger">
                                                        2. مرفوض (رفض تعادل الشهادة)
                                                    </option>
                                                @endif
                                            </select>
                                        </div>

                                        {{-- حقل سبب الرفض يظهر فقط إذا اختار "مرفوض" --}}
                                        <div id="rejection_box_{{ $app->id }}" style="display: none;" class="mb-3">
                                            <label class="form-label label-sm fw-bold text-danger">سبب الرفض وملاحظات اللجنة: <span class="text-danger">*</span></label>
                                            <textarea name="rejection_reason" id="rejection_input_{{ $app->id }}" class="form-control" rows="3" placeholder="أدخل أسباب عدم الموافقة على التعادل بالتفصيل..."></textarea>
                                        </div>

                                        <div class="row g-2">
                                            <div class="col-6">
                                                <label class="form-label label-sm fw-bold">رقم الجلسة / المحضر</label>
                                                <input type="text" name="committee_meeting_no" class="form-control" placeholder="مثال: 15">
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label label-sm fw-bold">تاريخ الجلسة</label>
                                                <input type="date" name="committee_date" class="form-control" value="{{ date('Y-m-d') }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal-footer bg-light py-2">
                                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                        <button type="submit" class="btn btn-sm btn-primary px-3 fw-bold">تأكيد واعتماد القرار</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endif

                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">لا توجد طلبات محالة إلى اللجنة العامة حالياً.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function handleDecisionChange(appId, val) {
    const rejBox = document.getElementById('rejection_box_' + appId);
    const rejInput = document.getElementById('rejection_input_' + appId);

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