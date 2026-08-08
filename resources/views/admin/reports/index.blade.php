@extends('layouts.admin')

@section('title', 'الإحصائيات والتقارير الشاملة - مجلس التعليم العالي')

@section('content')
<div class="card-academic-table mb-4">
    <!-- Header Slab -->
    <div class="table-header-slab d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-chart-pie fs-4" style="color: var(--heritage-gold);"></i>
            <h5 class="fw-bold mb-0" style="color: var(--imperial-navy);">
                التقارير والإحصائيات الشاملة لطلبات التعادل
            </h5>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-navy btn-sm px-3 fw-bold">
                <i class="fa-solid fa-arrows-rotate me-1"></i> تحديث البيانات
            </a>
            <a href="{{ route('admin.reports.pdf') }}" target="_blank" class="btn btn-gold-cta btn-sm px-3 fw-bold">
                <i class="fa-solid fa-download me-1"></i> تنزيل كـ PDF
            </a>
        </div>
    </div>

    <div class="card-body p-4 bg-white">
        <div class="row g-4">
            <!-- 1. Status Breakdown Card -->
            <div class="col-lg-6 col-md-12">
                <div class="card border shadow-sm rounded h-100 overflow-hidden" style="border-top: 3px solid var(--heritage-gold) !important;">
                    <div class="card-header text-white font-bold py-3" style="background-color: var(--imperial-navy) !important;">
                        <h6 class="mb-0 fw-bold text-white d-flex align-items-center gap-2">
                            <i class="fa-solid fa-chart-bar" style="color: var(--heritage-gold-light);"></i>
                            <span>الإحصائيات حسب حالة المعاملة</span>
                        </h6>
                    </div>
                    <div class="card-body p-4 bg-white">
                        <div class="d-flex flex-column gap-3">
                            @foreach($statusBreakdown as $stName => $stData)
                            @php
                                $stCount = is_array($stData) ? $stData['count'] : $stData;
                                $stIcon = is_array($stData) ? $stData['icon'] : 'fa-folder-open';
                                $perc = $totalApps > 0 ? round(($stCount / $totalApps) * 100) : 0;
                            @endphp
                            <div class="d-flex align-items-center justify-content-between gap-3">
                                <span class="fw-bold fs-7 d-inline-flex align-items-center gap-2 text-start" style="min-width: 160px; color: var(--imperial-navy);">
                                    <i class="fa-solid {{ $stIcon }} fs-6" style="color: var(--heritage-gold);"></i>
                                    <span>{{ $stName }}</span>
                                </span>

                                <span class="fw-bold fs-7 text-center" style="min-width: 45px; color: var(--imperial-navy);">
                                    %{{ $perc }}
                                </span>

                                <div class="progress flex-grow-1" style="height: 12px; background-color: var(--surface-container-highest); border-radius: 6px;">
                                    <div class="progress-bar" role="progressbar" style="width: {{ $perc }}%; background-color: var(--imperial-navy); border-radius: 6px;" aria-valuenow="{{ $perc }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>

                                <span class="badge bg-light border text-dark px-3 py-2 fs-7 fw-bold rounded" style="min-width: 75px;">
                                    {{ $stCount }} طلب
                                </span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. University Breakdown Card -->
            <div class="col-lg-6 col-md-12">
                <div class="card border shadow-sm rounded h-100 overflow-hidden" style="border-top: 3px solid var(--heritage-gold) !important;">
                    <div class="card-header text-white font-bold py-3" style="background-color: var(--imperial-navy) !important;">
                        <h6 class="mb-0 fw-bold text-white d-flex align-items-center gap-2">
                            <i class="fa-solid fa-building-columns" style="color: var(--heritage-gold-light);"></i>
                            <span>إحصائيات الطلبات حسب الجامعات</span>
                        </h6>
                    </div>
                    <div class="card-body p-4 bg-white">
                        <div class="d-flex flex-column gap-3">
                            @foreach($universityBreakdown as $ub)
                            @php
                                $perc = $totalApps > 0 ? round(($ub->applications_count / $totalApps) * 100) : 0;
                            @endphp
                            <div class="d-flex align-items-center justify-content-between gap-3">
                                <span class="fw-bold fs-7 text-start" style="min-width: 150px; color: var(--imperial-navy);">
                                    <i class="fa-solid fa-university me-2" style="color: var(--heritage-gold);"></i>
                                    {{ $ub->name }}
                                </span>

                                <span class="fw-bold fs-7 text-center" style="min-width: 45px; color: var(--imperial-navy);">
                                    %{{ $perc }}
                                </span>

                                <div class="progress flex-grow-1" style="height: 12px; background-color: var(--surface-container-highest); border-radius: 6px;">
                                    <div class="progress-bar" role="progressbar" style="width: {{ $perc }}%; background-color: var(--heritage-gold); border-radius: 6px;" aria-valuenow="{{ $perc }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>

                                <span class="badge bg-light border text-dark px-3 py-2 fs-7 fw-bold rounded" style="min-width: 75px;">
                                    {{ $ub->applications_count }} طلب
                                </span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
