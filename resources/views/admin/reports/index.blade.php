@extends('layouts.admin')

@section('title', 'الإحصائيات والتقارير الشاملة - مجلس التعليم العالي')

@section('content')
<div class="mohe-card">
    <div class="mohe-card-header bg-light">
        <h5 class="mohe-card-title text-success" style="color: #0f392b !important;"><i class="fa-solid fa-chart-pie me-2"></i> التقارير والإحصائيات الشاملة لطلبات التعادل</h5>
    </div>
    <div class="card-body">
        
        <div class="row g-4 mb-4">
            <!-- Status Distribution Table & Progress Bars -->
            <div class="col-md-6">
                <div class="card h-100 border shadow-sm">
                    <div class="card-header bg-primary text-white font-bold">
                        <i class="fa-solid fa-chart-bar me-2"></i> الإحصائيات حسب حالة المعاملة
                    </div>
                    <div class="card-body">
                        @foreach($statusBreakdown as $stName => $stCount)
                        @php
                            $percentage = $totalApps > 0 ? round(($stCount / $totalApps) * 100, 1) : 0;
                        @endphp
                        <div class="mb-3">
                            <div class="d-flex justify-content-between font-bold mb-1 fs-7">
                                <span>{{ $stName }}</span>
                                <span>{{ $stCount }} طلب ({{ $percentage }}%)</span>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-success" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- University Distribution -->
            <div class="col-md-6">
                <div class="card h-100 border shadow-sm">
                    <div class="card-header bg-warning text-dark font-bold">
                        <i class="fa-solid fa-building-columns me-2"></i> إحصائيات الطلبات المقدمة حسب الجامعات السورية
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @foreach($universityBreakdown as $ub)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-dark">{{ $ub->name }}</span>
                                <span class="badge bg-primary rounded-pill px-3 py-2 fs-7">{{ $ub->applications_count }} طلب</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
