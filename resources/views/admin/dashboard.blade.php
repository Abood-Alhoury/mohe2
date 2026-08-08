@extends('layouts.admin')

@section('title', 'الرئيسية - لوحة التحكم وأرشفة المعاملات')

@section('content')

<style>
    /* Metric Cards Design System */
    .kpi-card-academic {
        background-color: #FFFFFF;
        border-radius: 4px;
        border: none;
        border-top: 3px solid #C5A059;
        box-shadow: 0px 4px 20px rgba(26, 42, 68, 0.05);
        padding: 1.25rem 1.5rem;
        height: 120px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .kpi-card-academic:hover {
        transform: translateY(-2px);
        box-shadow: 0px 8px 24px rgba(26, 42, 68, 0.08);
    }

    .kpi-title {
        font-size: 0.88rem;
        font-weight: 600;
        color: #1A2A44;
        margin: 0;
    }

    .kpi-number {
        font-size: 2.25rem;
        font-weight: 700;
        color: #1A2A44;
        text-align: left;
        line-height: 1;
        margin: 0;
    }

    .kpi-icon {
        color: #1A2A44;
        font-size: 1.5rem;
        opacity: 0.85;
    }

    /* Data Table Design System */
    .card-academic-table {
        background-color: #FFFFFF;
        border-radius: 4px;
        border: 1px solid #C5C6CE;
        box-shadow: 0px 4px 20px rgba(26, 42, 68, 0.05);
        overflow: hidden;
    }

    .table-header-slab {
        padding: 1rem 1.5rem;
        background-color: #FFFFFF;
        border-bottom: 1px solid #C5C6CE;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .table-academic {
        width: 100%;
        margin-bottom: 0;
        border-collapse: collapse;
        text-align: right;
    }

    .table-academic thead {
        background-color: #1A2A44;
        color: #FFFFFF;
    }

    .table-academic th {
        padding: 1rem;
        font-weight: 600;
        font-size: 0.88rem;
        border-bottom: 1px solid #C5C6CE;
        border-top: none;
        border-left: none;
        border-right: none;
    }

    .table-academic td {
        padding: 1rem;
        font-size: 0.92rem;
        border-bottom: 1px solid #C5C6CE;
        border-top: none;
        border-left: none;
        border-right: none;
        vertical-align: middle;
        transition: background-color 0.15s ease;
    }

    .table-academic tbody tr:nth-child(even) {
        background-color: #FBF9FB;
    }

    .table-academic tbody tr:hover {
        background-color: #F5F3F5 !important;
    }

    /* Buttons Design System */
    .btn-outline-navy {
        background-color: transparent;
        color: #1A2A44;
        border: 1px solid #1A2A44;
        border-radius: 4px;
        padding: 0.3rem 0.75rem;
        font-size: 0.82rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .btn-outline-navy:hover {
        background-color: #F5F3F5;
        color: #04152E;
        border-color: #04152E;
    }

    .btn-outline-gold {
        background-color: transparent;
        color: #C5A059;
        border: 1px solid #C5A059;
        border-radius: 4px;
        padding: 0.3rem 0.75rem;
        font-size: 0.82rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .btn-outline-gold:hover {
        background-color: #F5F3F5;
        color: #a3813c;
        border-color: #a3813c;
    }

    .btn-solid-navy {
        background-color: #1A2A44;
        color: #FFFFFF;
        border: none;
        border-radius: 4px;
        padding: 0.5rem 1.25rem;
        font-size: 0.88rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .btn-solid-navy:hover {
        background-color: #04152E;
        color: #ffffff;
    }

    /* Badges */
    .badge-academic-tag {
        background-color: #E4E2E4;
        color: #44474D;
        font-size: 0.78rem;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 4px;
    }
</style>

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
                    <td class="text-muted" style="font-size: 0.88rem;">{{ $app->created_at ? $app->created_at->format('Y-m-d') : 'غ/م' }}</td>
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
