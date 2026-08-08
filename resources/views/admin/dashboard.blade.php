@extends('layouts.admin')

@section('title', 'الرئيسية - لوحة التحكم وأرشفة المعاملات')

@section('content')
<!-- Metric Statistics Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-white" style="background: linear-gradient(135deg, #0f392b, #18523f);">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="card-subtitle text-white-50 mb-1 fw-bold">إجمالي معاملات التعادل</h6>
                    <h2 class="card-title mb-0 font-extrabold">{{ number_format($totalApps) }}</h2>
                </div>
                <div class="fs-1 text-white-50"><i class="fa-solid fa-folder-open"></i></div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-white" style="background: linear-gradient(135deg, #0284c7, #0369a1);">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="card-subtitle text-white-50 mb-1 fw-bold">طلبات قيد الدراسة</h6>
                    <h2 class="card-title mb-0 font-extrabold">{{ number_format($underStudyCount) }}</h2>
                </div>
                <div class="fs-1 text-white-50"><i class="fa-solid fa-hourglass-half"></i></div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-white" style="background: linear-gradient(135deg, #d97706, #b45309);">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="card-subtitle text-white-50 mb-1 fw-bold">مواضيع اللجنة العامة (معلق)</h6>
                    <h2 class="card-title mb-0 font-extrabold">{{ number_format($committeeCount) }}</h2>
                </div>
                <div class="fs-1 text-white-50"><i class="fa-solid fa-pause"></i></div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-white" style="background: linear-gradient(135deg, #059669, #047857);">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="card-subtitle text-white-50 mb-1 fw-bold">الطلبات المقبولة / الصادرة</h6>
                    <h2 class="card-title mb-0 font-extrabold">{{ number_format($approvedCount) }}</h2>
                </div>
                <div class="fs-1 text-white-50"><i class="fa-solid fa-circle-check"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Main Applications Table -->
<div class="mohe-card">
    <div class="mohe-card-header">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-list-check fs-5 text-success" style="color: #0f392b !important;"></i>
            <h5 class="mohe-card-title">أحدث طلبات معادلة الشهادات المقدمة للمجلس</h5>
        </div>
        <div>
            <a href="{{ route('admin.applications.index') }}" class="btn btn-sm btn-mohe-primary">
                <i class="fa-solid fa-eye me-1"></i> استعراض كافة الطلبات
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mohe-table align-middle">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>نوع الطلب</th>
                        <th>اسم المرشح (الطالب)</th>
                        <th>الجامعة المقدمة</th>
                        <th>الكلية والفرع</th>
                        <th>حالة الطلب</th>
                        <th>تاريخ التقديم</th>
                        <th style="width: 250px;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentApplications as $app)
                    <tr>
                        <td class="fw-bold text-secondary">{{ $app->id }}</td>
                        <td>
                            <span class="badge bg-light text-dark border fw-bold">{{ $app->request_type ?? 'تعادل' }}</span>
                        </td>
                        <td class="fw-bold text-primary">
                            {{ $app->candidate->full_name ?? 'غ/م' }}
                        </td>
                        <td>{{ $app->workUniversity->name ?? 'جامعة غير محددة' }}</td>
                        <td>{{ $app->work_faculty ?? 'غ/م' }}</td>
                        <td>
                            @if($app->status == 'تحت التدقيق الأولي' || $app->status == 'قيد الدراسة')
                                <span class="badge-status badge-study">تحت التدقيق الأولي</span>
                            @elseif($app->status == 'بانتظار الوثائق')
                                <span class="badge-status badge-paper">بانتظار الوثائق</span>
                            @elseif($app->status == 'تم الصدور' || $app->status == 'موافقة')
                                <span class="badge-status badge-approved">تم الصدور</span>
                            @elseif($app->status == 'معلق')
                                <span class="badge-status badge-suspended">معلق</span>
                            @elseif($app->status == 'مرفوض')
                                <span class="badge-status badge-rejected">مرفوض</span>
                            @else
                                <span class="badge-status badge-study">{{ $app->status }}</span>
                            @endif
                        </td>
                        <td class="text-muted fs-7">{{ $app->created_at ? $app->created_at->format('Y-m-d') : 'غ/م' }}</td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('admin.reports.show', $app->id) }}" class="btn btn-outline-primary" title="عرض مذكرة العرض">
                                    <i class="fa-solid fa-file-invoice me-1"></i> Select
                                </a>
                                <a href="{{ route('admin.applications.edit', $app->id) }}" class="btn btn-outline-warning" title="تعديل البيانات">
                                    <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">لا توجد طلبات معادلة حالياً.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
