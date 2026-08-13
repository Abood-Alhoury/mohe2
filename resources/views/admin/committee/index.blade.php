@extends('layouts.admin')

@section('title', 'مواضيع اللجنة العامة - دراسة قرارات اللجنة')

@section('content')
<div class="mohe-card">
    <div class="mohe-card-header bg-light">
        <h5 class="mohe-card-title text-warning"><i class="fa-solid fa-users-rectangle me-2"></i> شؤون وقرارات اللجنة العامة</h5>
    </div>
    <div class="card-body p-0">
        <div class="p-3 bg-warning-subtle text-dark border-bottom fw-bold">
            <i class="fa-solid fa-circle-info me-2"></i> تعرض هذه الصفحة طلبات التعادل المرفوعة إلى (لجنة عامة). عند إقرار الموافقة تتحول المعاملة تلقائياً إلى <strong>(بانتظار إصدار القرار)</strong>، وعند الإقرار بالرفض تتحول المعاملة إلى <strong>(مرفوض)</strong>.
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
                        $lastEducation = $app->educations->last();
                    @endphp
                    <tr>
                        <td class="fw-bold text-secondary">{{ $app->id }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $app->request_type ?? 'تعادل' }}</span></td>
                        <td class="fw-bold">{{ $app->workUniversity->name ?? 'جامعة غير محددة' }}</td>
                        <td class="text-primary fw-bold">{{ $app->candidate->full_name ?? 'غ/م' }}</td>
                        <td>{{ $app->work_faculty ?? 'إدارة جامعة' }}</td>
                        <td>{{ $lastEducation->level->name ?? 'إجازة جامعية' }}</td>
                        <td>
                            @if($app->status === 'لجنة عامة')
                                <span class="badge bg-warning text-dark border border-warning fs-7"><i class="fa-solid fa-users-rectangle me-1"></i> لجنة عامة</span>
                            @else
                                <span class="badge bg-secondary text-white fs-7">{{ $app->status }}</span>
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('admin.committee.decide', $app->id) }}" method="POST" class="d-flex gap-1 justify-content-center">
                                @csrf
                                @method('PATCH')
                                <button type="submit" name="decision" value="موافقة" class="btn btn-sm btn-success fw-bold px-3" onclick="return confirm('إقرار موافقة اللجنة العامة وتحويل الطلب إلى (بانتظار إصدار القرار)؟');">
                                    <i class="fa-solid fa-check me-1"></i> موافقة
                                </button>
                                <button type="submit" name="decision" value="رفض" class="btn btn-sm btn-danger fw-bold px-3" onclick="return confirm('إقرار رفض الطلب من قبل اللجنة العامة وتحويله إلى (مرفوض)؟');">
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
                        <td colspan="9" class="text-center py-5 text-muted">لا توجد طلبات محالة إلى اللجنة العامة حالياً.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
