@extends('layouts.admin')

@section('title', 'الرئيسية - لوحة التحكم وأرشفة المعاملات')

@section('content')

<!-- SECTION 1: KPI CARDS (STATISTICS WITH GOLD TOP BORDER & AMBIENT SHADOW) -->
<div class="row g-4 mb-4">
    <!-- Card 1 -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="kpi-card-academic">
            <div class="d-flex justify-content-between align-items-start">
                <span class="kpi-title">إجمالي معاملات التعادل</span>
                <i class="fa-solid fa-folder-open kpi-icon"></i>
            </div>
            <div class="kpi-number">{{ number_format($totalApps) }}</div>
        </div>
    </div>

    <!-- Card 2 -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="kpi-card-academic">
            <div class="d-flex justify-content-between align-items-start">
                <span class="kpi-title">طلبات قيد الدراسة</span>
                <i class="fa-solid fa-hourglass-half kpi-icon"></i>
            </div>
            <div class="kpi-number">{{ number_format($underStudyCount) }}</div>
        </div>
    </div>

    <!-- Card 3 -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="kpi-card-academic">
            <div class="d-flex justify-content-between align-items-start">
                <span class="kpi-title">مواضيع اللجنة العامة (معلق)</span>
                <i class="fa-solid fa-pause kpi-icon"></i>
            </div>
            <div class="kpi-number">{{ number_format($committeeCount) }}</div>
        </div>
    </div>

    <!-- Card 4 -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="kpi-card-academic">
            <div class="d-flex justify-content-between align-items-start">
                <span class="kpi-title">الطلبات المقبولة / الصادرة</span>
                <i class="fa-solid fa-circle-check kpi-icon"></i>
            </div>
            <div class="kpi-number">{{ number_format($approvedCount) }}</div>
        </div>
    </div>
</div>

<!-- SECTION 2: DATA TABLE (ACADEMIC EXCELLENCE SYSTEM) -->
<div class="card-academic-table">
    <div class="table-header-slab">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-list-alt text-primary fs-5" style="color: #1A2A44 !important;"></i>
            <h2 class="h5 fw-bold mb-0" style="color: #1A2A44;">أحدث طلبات معادلة الشهادات المقدمة للمجلس</h2>
        </div>
        <div>
            <a href="{{ route('admin.applications.index') }}" class="btn-solid-navy">
                <i class="fa-solid fa-eye"></i>
                <span>استعراض كافة الطلبات</span>
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table-academic">
            <thead>
                <tr>
                    <th style="width: 60px;">ID</th>
                    <th>نوع الطلب</th>
                    <th>اسم المرشح (الطالب)</th>
                    <th>الجامعة المقدمة</th>
                    <th>الكلية والفرع</th>
                    <th class="text-center">حالة الطلب</th>
                    <th>تاريخ التقديم</th>
                    <th class="text-center" style="width: 200px;">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentApplications as $app)
                <tr>
                    <td class="fw-bold text-secondary">{{ $app->id }}</td>
                    <td>
                        <span class="badge-academic-tag">{{ $app->request_type ?? 'تعادل' }}</span>
                    </td>
                    <td class="fw-bold" style="color: #1d4ed8;">
                        {{ $app->candidate->full_name ?? 'غ/م' }}
                    </td>
                    <td>{{ $app->workUniversity->name ?? 'جامعة غير محددة' }}</td>
                    <td>{{ $app->work_faculty ?? 'غ/م' }}</td>
                    <td class="text-center">
                        @if($app->status == 'تحت التدقيق الأولي' || $app->status == 'قيد الدراسة')
                            <span class="fw-bold" style="color: #2563eb;">تحت التدقيق الأولي</span>
                        @elseif($app->status == 'بانتظار الوثائق')
                            <span class="fw-bold" style="color: #ea580c;">بانتظار الوثائق</span>
                        @elseif($app->status == 'تم الصدور' || $app->status == 'موافقة')
                            <span class="fw-bold" style="color: #16a34a;">تم الصدور</span>
                        @elseif($app->status == 'معلق')
                            <span class="fw-bold" style="color: #d97706;">معلق</span>
                        @elseif($app->status == 'مرفوض')
                            <span class="fw-bold" style="color: #dc2626;">مرفوض</span>
                        @else
                            <span class="fw-bold" style="color: #2563eb;">{{ $app->status }}</span>
                        @endif
                    </td>
                    <td class="text-muted" style="font-size: 0.88rem;">{{ $app->created_at ? $app->created_at->format('d/m/Y') : 'غ/م' }}</td>
                    <td class="text-center">
                        <div class="d-flex align-items-center justify-content-center gap-2">
                            <a href="{{ route('admin.reports.show', $app->id) }}" class="btn-outline-navy" title="عرض مذكرة العرض">
                                <span>Select</span>
                                <i class="fa-solid fa-file-invoice"></i>
                            </a>
                            <a href="{{ route('admin.applications.edit', $app->id) }}" class="btn-outline-gold" title="تعديل البيانات">
                                <span>Edit</span>
                                <i class="fa-solid fa-pen-to-square"></i>
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
@endsection
