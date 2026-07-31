@extends('layouts.admin')

@section('title', 'مواضيع اللجنة العامة - دراسة الطلبات المعلقة')

@section('content')
<div class="mohe-card">
    <div class="mohe-card-header bg-light">
        <h5 class="mohe-card-title text-warning"><i class="fa-solid fa-users-rectangle me-2"></i> مواضيع وشؤون اللجنة العامة (الطلبات المعلقة المخصصة للدراسة)</h5>
    </div>
    <div class="card-body p-0">
        <div class="p-3 bg-warning-subtle text-dark border-bottom fw-bold">
            <i class="fa-solid fa-circle-info me-2"></i> تعرض هذه الصفحة فقط طلبات التعادل ذات الحالة (معلق) التي تتطلب انقاد جلسة اللجنة العامة لبت القرار فيها.
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
                        <th style="width: 260px;">قرار اللجنة العامة</th>
                        <th style="width: 80px;">Select</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($committeeApps as $app)
                    @php
                        $highestEducation = $app->educations->sortByDesc('education_level_id')->first();
                    @endphp
                    <tr>
                        <td class="fw-bold text-secondary">{{ $app->id }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $app->request_type ?? 'تعادل' }}</span></td>
                        <td class="fw-bold">{{ $app->workUniversity->name ?? 'جامعة غير محددة' }}</td>
                        <td class="text-primary fw-bold">{{ $app->candidate->full_name ?? 'غ/م' }}</td>
                        <td>{{ $app->work_faculty ?? 'إدارة جامعة' }}</td>
                        <td>{{ $highestEducation->level->name ?? 'دكتوراه' }}</td>
                        <td><span class="badge badge-status badge-suspended fs-7">معلق</span></td>
                        <td>
                            <form action="{{ route('admin.committee.decide', $app->id) }}" method="POST" class="d-flex gap-1 justify-content-center">
                                @csrf
                                @method('PATCH')
                                <button type="submit" name="decision" value="تم الصدور" class="btn btn-sm btn-success fw-bold" onclick="return confirm('إقرار موافقة اللجنة والصدور؟');">
                                    <i class="fa-solid fa-check me-1"></i> موافقة
                                </button>
                                <button type="submit" name="decision" value="مرفوض" class="btn btn-sm btn-danger fw-bold" onclick="return confirm('إقرار رفض الطلب من قبل اللجنة؟');">
                                    <i class="fa-solid fa-xmark me-1"></i> رفض
                                </button>
                            </form>
                        </td>
                        <td>
                            <a href="{{ route('admin.reports.show', $app->id) }}" class="btn btn-sm btn-link text-decoration-none fw-bold text-primary">
                                Select
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">لا توجد طلبات معلقة بانتظار اللجنة العامة حالياً.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
