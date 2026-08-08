@extends('layouts.admin')

@section('title', 'الإحصائيات والتقارير الشاملة لطلبات التعادل')

@section('content')

<!-- Header Action Buttons & Title Bar matching Screenshot 2 (Strict RTL) -->
<div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom" dir="rtl">
    <!-- Right side in RTL: Main Page Title -->
    <div>
        <h3 class="fw-bold text-dark mb-0 d-inline-flex align-items-center gap-2">
            <i class="fa-solid fa-scroll text-warning fs-3"></i> التقارير والإحصائيات الكاملة لطلبات التعادل
        </h3>
    </div>

    <!-- Left side in RTL: Action Buttons -->
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary btn-sm px-3 py-2 fw-bold bg-white shadow-sm border">
            <i class="fa-solid fa-arrows-rotate me-1"></i> تحديث البيانات
        </a>
        <a href="{{ route('admin.reports.pdf') }}" target="_blank" class="btn btn-outline-secondary btn-sm px-3 py-2 fw-bold bg-white shadow-sm border">
            <i class="fa-solid fa-download me-1"></i> تنزيل كـ PDF
        </a>
    </div>
</div>

<div class="row g-4" dir="rtl">
    <!-- Right Card in RTL: University Breakdown (First in HTML = Appears on the RIGHT side) -->
    <div class="col-lg-6 col-md-12">
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden h-100">
            <div class="card-header text-white font-bold py-3" style="background-color: #C9B037;">
                <h5 class="mb-0 fs-6 fw-bold text-white d-flex align-items-center justify-content-start gap-2 text-start">
                    <i class="fa-solid fa-building-columns fs-5"></i> إحصائيات الطلبات حسب الجامعات
                </h5>
            </div>
            <div class="card-body p-4 bg-white">
                <div class="d-flex flex-column gap-3">
                    @foreach($universityBreakdown as $ub)
                    @php
                        $perc = $totalApps > 0 ? round(($ub->applications_count / $totalApps) * 100) : 0;
                    @endphp
                    <!-- RTL Row: Name on Far Right -> Percentage -> Progress Bar -> Badge on Far Left -->
                    <div class="d-flex align-items-center justify-content-between gap-3" dir="rtl">
                        <!-- Far Right: University Name -->
                        <span class="fw-bold text-dark fs-6 text-start" style="min-width: 140px;">
                            {{ $ub->name }}
                        </span>

                        <!-- Middle Right: Percentage -->
                        <span class="fw-bold text-dark fs-7 text-center" style="min-width: 45px;">
                            %{{ $perc }}
                        </span>

                        <!-- Middle: Blue Progress Bar -->
                        <div class="progress flex-grow-1" style="height: 14px; background-color: #e2e8f0; border-radius: 10px;">
                            <div class="progress-bar" role="progressbar" style="width: {{ $perc }}%; background-color: #3b82f6; border-radius: 10px;" aria-valuenow="{{ $perc }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>

                        <!-- Far Left: Count Badge -->
                        <span class="badge bg-primary px-3 py-2 fs-7 fw-bold rounded-pill text-center" style="min-width: 75px; background-color: #3b82f6 !important;">
                            {{ $ub->applications_count }} طلب
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="card-footer bg-light border-0 text-muted fs-8 text-center py-2">
                دانمتعل: الجلال الطايئن - جامعة حلب الطلاب - 10 جامعة السوري - جامعة السورية - تحديث البيانات
            </div>
        </div>
    </div>

    <!-- Left Card in RTL: Status Breakdown (Second in HTML = Appears on the LEFT side) -->
    <div class="col-lg-6 col-md-12">
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden h-100">
            <div class="card-header text-white font-bold py-3" style="background-color: #C9B037;">
                <h5 class="mb-0 fs-6 fw-bold text-white d-flex align-items-center justify-content-start gap-2 text-start">
                    <i class="fa-solid fa-chart-pie fs-5"></i> الإحصائيات حسب حالة المعاملة
                </h5>
            </div>
            <div class="card-body p-4 bg-white">
                <div class="d-flex flex-column gap-3">
                    @foreach($statusBreakdown as $stName => $stData)
                    @php
                        $stCount = $stData['count'];
                        $perc = $totalApps > 0 ? round(($stCount / $totalApps) * 100) : 0;
                    @endphp
                    <!-- RTL Row: Status Name & Icon on Far Right -> Percentage -> Progress Bar -> Badge on Far Left -->
                    <div class="d-flex align-items-center justify-content-between gap-3" dir="rtl">
                        <!-- Far Right: Status Icon & Name -->
                        <span class="fw-bold text-dark fs-6 text-start d-inline-flex align-items-center gap-2" style="min-width: 160px;">
                            <i class="fa-solid {{ $stData['icon'] }} fs-6"></i>
                            <span>{{ $stName }}</span>
                        </span>

                        <!-- Middle Right: Percentage -->
                        <span class="fw-bold text-dark fs-7 text-center" style="min-width: 45px;">
                            %{{ $perc }}
                        </span>

                        <!-- Middle: Blue Progress Bar -->
                        <div class="progress flex-grow-1" style="height: 14px; background-color: #e2e8f0; border-radius: 10px;">
                            <div class="progress-bar" role="progressbar" style="width: {{ $perc }}%; background-color: #3b82f6; border-radius: 10px;" aria-valuenow="{{ $perc }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>

                        <!-- Far Left: Count Badge -->
                        <span class="badge bg-primary px-3 py-2 fs-7 fw-bold rounded-pill text-center" style="min-width: 75px; background-color: #3b82f6 !important;">
                            {{ $stCount }} طلب
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="card-footer bg-light border-0 text-muted fs-8 text-center py-2">
                دانمتعل: المتيل - جامعة التدقيق الأولي - 1 تحت التدقيق الأولي - تحديث البيانات
            </div>
        </div>
    </div>
</div>

@endsection
